<?php

namespace App\Services;

use App\Models\Company;
use App\Models\TrzCfe;
use App\Models\TrzDetCf;
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
        $trzCfe = TrzCfePOS::createFromPOS($data);
        $nrBon = $trzCfe->nrbonfint ?? null;
        $this->saveDetCf($data, $nrBon);
        $this->savePartial($data);
    }

    protected function savePartial($data)
    {
        // Split items by departament
        $grouped = [];
        foreach ($data['items'] as $item) {
            $gest = (int)($item['product']['gest'] ?? 0);
            $grouped[$gest][] = $item;
        }
        foreach ($grouped as $dept) {

            $partial['subtotal'] = array_sum(array_map(function($i) {
                return $i['unitPrice'] * $i['qty'];
            }, $dept));
            $partial['items'] = $dept;
            $partial['customer'] = $data['customer'];
            $partial['type'] = $data['type'];
            $partial['totalDiscount'] = 0; // Or recalculate if needed
            $partial['total'] = $partial['subtotal'] - $partial['totalDiscount'];
            $partial['casa'] = $data['casa'];
            $partial['cashGiven'] = $data['cashGiven'];
            $partial['change'] = $data['change'];
            $partial['pendingPayment'] = $data['pendingPayment'];

            $trzCfePOS = TrzCfe::createFromPOS($partial);
            $nrBon = $trzCfePOS->nrbonfint ?? null;
            $this->saveDetCf($partial, $nrBon, true);
        }
    }

    protected function saveDetCf($data,$nrBon, $usePOSModel = false)
    {

        // Use the model's helper method for cleaner code
        foreach ($data['items'] as $item) {
            if($usePOSModel) {
                TrzDetCfPOS::createDetail($item,$data['customer'], $nrBon);
            }else{
                TrzDetCfPOS::createDetail($item,$data['customer'], $nrBon);
            } 

        }
        
    }

    
}
