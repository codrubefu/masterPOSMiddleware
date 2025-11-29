<?php

namespace App\Services;

use App\Models\Company;
use App\Models\TrzCfe;
use App\Models\TrzDetCf;
use App\Models\UpcGenprod;
use Illuminate\Http\Request;
use App\Models\TrzCfePOS;
use App\Models\TrzDetCfPOS;

class BonDatabaseService
{
    protected $company;

    public function __construct()
    {
        $this->company = Company::first();
    }

    public function save(Request $request)
    {
        $data = $request->all();
           TrzCfe::createFromPOS($data);

        $this->saveDetCf($data);
        $this->savePartial($data);
    }

    protected function savePartial($data)
    {
        // Split items by departament
        $grouped = [];
        foreach ($data['items'] as $item) {
            $dept = (int)($item['product']['gest'] ?? 0);
            $grouped[$dept][] = $item;
        }

        foreach ($grouped as $dept => $items) {
            $partial = $data;
            $partial['items'] = $items;
            $partial['subtotal'] = array_sum(array_map(function($i) {
                return $i['unitPrice'] * $i['qty'];
            }, $items));
            $partial['totalDiscount'] = 0; // Or recalculate if needed
            $partial['total'] = $partial['subtotal'] - $partial['totalDiscount'];
            TrzCfePos::createFromPOS($partial);
            $this->saveDetCf($partial, true);

        }
    }

    protected function saveDetCf($data,$usePOSModel = false)
    {

            if($usePOSModel) {
                TrzDetCfPOS::createDetail($item,$data['customer'],$data['type'] ?? null);
            }else{
                TrzDetCf::createDetail($item,$data['customer'],$data['type'] ?? null);
            }

        }
        
    }

    
}
