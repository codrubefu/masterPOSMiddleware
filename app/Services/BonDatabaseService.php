<?php

namespace App\Services;

use App\Models\Company;
use App\Models\TrzCfe;
use Illuminate\Http\Request;
use App\Models\TrzCfePOS;
use App\Models\TrzDetCfPOS;
use App\Models\TrzDetCf;
use App\Models\TrzFactBf;
use App\Models\TrzDetFactBf;

class BonDatabaseService
{
    protected $company;
    protected array $sgrUpcs = ['1112', '1113', '1114'];

    protected array $sgrItems = [
        'pet' => [
            'product' => [
                'upc' => '1112',
                'name' => 'Garantie SGR Pet',
                'price' => 0.50,
                'gest' => 1,
                'departament' => 3,
                'tax1' => 0,
                'tax2' => 0,
                'tax3' => 0,
            ],
            'qty' => 1,
            'unitPrice' => 0.50,
            'storno' => false,
        ],
        'doza' => [
            'product' => [
                'upc' => '1113',
                'name' => 'Garantie SGR Doza',
                'price' => 0.50,
                'gest' => 1,
                'departament' => 3,
                'tax1' => 0,
                'tax2' => 0,
                'tax3' => 0,
            ],
            'qty' => 1,
            'unitPrice' => 0.30,
            'storno' => false,
        ],
        'sticla' => [
            'product' => [
                'upc' => '1114',
                'name' => 'Garantie SGR Sticla',
                'price' => 0.50,
                'gest' => 1,
                'departament' => 3,
                'tax1' => 0,
                'tax2' => 0,
                'tax3' => 0,
            ],
            'qty' => 1,
            'unitPrice' => 0.20,
            'storno' => false,
        ],
    ];
    public function __construct()
    {
        $this->company = Company::first();
    }

    public function save(Request $request)
    {
        $data = $request->all();
        $data['items'] = $this->deleteSGR($data['items'] ?? []);
        $data['items'] = $this->addSgr($data['items'] ?? []);
        $trzCfe = TrzCfePOS::createFromPOS($data);
        $nrBon = $trzCfe->nrbonfint ?? null;
        $this->saveDetCf($data, $nrBon);
        $this->savePartial($data);

        //Save facura

        $fact = TrzFactBf::createFromPOS($request->all());
        $this->saveFacturaDet($request->all(), $fact);
    }

    protected function saveFacturaDet($data, $fact)
    {
             $nrFact = $fact->nrfact ?? null;
             foreach ($data['items'] as $item) {
                TrzDetFactBf::createDetail($item,$data['customer'], $nrFact);
            }
    }

    protected function savePartial($data)
    {
        // Split items by departament
        $grouped = [];
        foreach ($data['items'] as $item) {
            $gest = (int)($item['product']['gest'] ?? 0);
            $grouped[$gest][] = $item;
        }

        foreach ($grouped as $gest=>$dept) {

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
            $partial['pendingPayment'] = $data['pendingPayment'] ?? null;

            $trzCfePOS = TrzCfe::createFromPOS($partial,$gest);
            $nrBon = $trzCfePOS->nrbonfint ?? null;
            $this->saveDetCf($partial, $nrBon, true);
        }
    }

    protected function saveDetCf($data,$nrBon, $usePOSModel = false)
    {
        // Use the model's helper method for cleaner code
        foreach ($data['items'] as $item) {
            if($usePOSModel) {
                TrzDetCf::createDetail($item,$data['customer'], $nrBon);
            }else{ 
                TrzDetCfPOS::createDetail($item,$data['customer'], $nrBon);
            } 
        }  
    }

    protected function deleteSGR(array $items): array
    {
        return array_values(array_filter($items, function ($item) {
            $upc = trim($item['product']['upc'] ?? '');
            return !in_array($upc, $this->sgrUpcs, true);
        }));
    } 

    protected function addSgr(array $items): array
    {
        $itemsWithSgr = [];

        foreach ($items as $item) {
            $itemsWithSgr[] = $item;

            $sgrKey = strtolower(trim($item['product']['sgr'] ?? ''));
            if ($sgrKey === '' || !isset($this->sgrItems[$sgrKey])) {
                continue;
            }

            $sgrTemplate = $this->sgrItems[$sgrKey];
            $qty = $item['qty'] ?? $sgrTemplate['qty'];

            $sgrItem = $sgrTemplate;
            $sgrItem['qty'] = $qty;
            $sgrItem['unitPrice'] = $sgrTemplate['product']['price'];
            $sgrItem['casa'] = $item['casa'] ?? ($sgrTemplate['casa'] ?? null);
            $sgrItem['product']['departament'] = $item['product']['departament'] ?? ($sgrTemplate['product']['departament'] ?? null);
            $sgrItem['product']['gest'] = $item['product']['gest'] ?? ($sgrTemplate['product']['gest'] ?? null);
            $sgrItem['product']['sgr'] = null; // Prevent re-processing

            $itemsWithSgr[] = $sgrItem;
        }

        return $itemsWithSgr;
    }

    
}
