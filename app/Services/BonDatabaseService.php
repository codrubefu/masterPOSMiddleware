<?php

namespace App\Services;

use App\Models\Company;
use App\Models\TrzCfe;
use App\Models\TrzDetCf;
use App\Models\UpcGenprod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


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

        $this->saveTrzCfeAux($data);
    }

    protected function saveTrzCfeAux($data)
    {

        $product = UpcGenprod::getByUpc($data['items'][0]['product']['upc']);
        if($product['depart'] == 1){
            $tva = $product['tax1'];
        }elseif($product['depart'] == 2){
            $tva = $product['tax2'];
        }elseif($product['depart'] == 3){
            $tva = $product['tax3'];
        }

        // Use the model's helper method for cleaner code
        foreach ($data['items'] as $item) {
           TrzDetCf::createDetail($item,$data['customer'],$data['type'] ?? null,$tva);
        }
        
    }
}
