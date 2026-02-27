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
                'grupa' => 'ZZ-AMBALAJE',
                'clasa' => 'ZZ-AMBALAJE',
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
                'grupa' => 'ZZ-AMBALAJE',
                'clasa' => 'ZZ-AMBALAJE',
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
                'grupa' => 'ZZ-AMBALAJE',
                'clasa' => 'ZZ-AMBALAJE',
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
        TrzCfePOS::createFromPOS($data);
        $trzCfe = TrzCfePOS::lastSaved();
        $nrBon = $trzCfe->nrbonfint ?? null;
        $this->saveDetCf($data, $nrBon);
        $this->savePartial($data, $trzCfe->nrbonfint ?? null);
    }

    protected function saveFacturaDet($data, $fact)
    {
        $nrFact = $fact->nrfact ?? null;
        foreach ($data['items'] as $item) {
            TrzDetFactBf::createDetail($item, $data['customer'], $nrFact);
        }
    }

    protected function savePartial($data, $nrBonfintPOS = null)
    {
        // Split items by departament
        $grouped = [];
        foreach ($data['items'] as $item) {
            $gest = (int)($item['product']['gest'] ?? 0);
            $grouped[$gest][] = $item;
        }
        $gestiuni = count($grouped);

        
        foreach ($grouped as $gest => $dept) {

            $items = $this->calculate($dept);
            $partial['subtotal'] = array_sum(array_map(function ($i) {
                return $i['unitPrice'] * $i['qty'];
            }, $dept));
            $partial['items'] = $items;
            $partial['customer'] = $data['customer'];
            $partial['type'] = $data['type'];
            $partial['totalDiscount'] = 0; // Or recalculate if needed
            $partial['total'] = $partial['subtotal'] - $partial['totalDiscount'];
            $partial['casa'] = $data['casa'];
            $partial['cashGiven'] = $data['cashGiven'];
            $partial['change'] = $data['change'];
            $partial['pendingPayment'] = $data['pendingPayment'] ?? null;
            // Distribute $partial['total'] between card and numerar in the same proportion as provided
            $cardAmount = $data['cardAmount'] ?? 0;
            $numerarAmount = $data['numerarAmount'] ?? 0;
            $total = $partial['total'] ?? 0;
            $sum = $cardAmount + $numerarAmount;
            if ($sum > 0 && $total > 0) {
                $partial['cardAmount'] = round($total * ($cardAmount / $sum), 2);
                $partial['numerarAmount'] = $total - $partial['cardAmount'];
            } else {
                $partial['cardAmount'] = $cardAmount;
                $partial['numerarAmount'] = $numerarAmount;
            }


            $trzCfePOS = TrzCfe::createFromPOS($partial, $gest, $nrBonfintPOS,$gestiuni);
            $nrBon = $trzCfePOS->nrbonfint ?? null;
            $this->saveDetCf($partial, $nrBon, true);
            $totalWithoutVat = $this->calculateTotalWithoutVat($partial['items']);
            
            if ($data['customer']['type'] == 'pj') {
                $fact = TrzFactBf::createFromPOS($partial, $totalWithoutVat);
                $this->saveFacturaDet($partial, $fact);
            }
        }
    }

    public function calculate(array $items): array
    {
        foreach ($items as &$item) {
        // Calculate VAT based on department
            $tva = 0.21; // Default VAT rate
            if (isset($item['product']['departament'])) {
                if ($item['product']['departament'] == 1) {
                    $tva = $item['product']['tax1'] ?? 0.21;
                } elseif ($item['product']['departament'] == 2) {
                    $tva = $item['product']['tax2'] ?? 0.21;
                } elseif ($item['product']['departament'] == 3) {
                    $tva = $item['product']['tax3'] ?? 0.21;
                }
            }
            $price = $item['product']['price'] ?? 0;
            $qty = $item['qty'] ?? 1;
            $valoare = $price * $qty;
            $priceWithoutVat = self::getPriceWithoutVat($price, $tva);

            $item['qty'] = $qty;
            $item['valoare'] = $priceWithoutVat * $qty;
            $item['tva'] =  $valoare - ($priceWithoutVat * $qty);
            $item['preturondisc'] = $priceWithoutVat;
            $item['cotatva'] = $tva;

            $item['valoare2'] = round($item['valoare'], 2);
            $item['tva2'] = round($item['tva'], 2);
        }
        return $items;
    }

    protected function calculateTotalWithoutVat($dataItems)
        {
            // Use array_column to extract 'valoare2' and sum for performance and clarity
            return round(array_sum(array_column($dataItems, 'valoare2')), 2);
        }

    protected function saveDetCf($data, $nrBon, $usePOSModel = false)
    {
        // Use the model's helper method for cleaner code
        foreach ($data['items'] as $item) {
            if ($usePOSModel) {
                TrzDetCf::createDetail($item, $data['customer'], $nrBon);
            } else {
                TrzDetCfPOS::createDetail($item, $data['customer'], $nrBon);
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

    protected static function getPriceWithoutVat($priceWithVat, $vatRate = 0.21)
    {
        return round($priceWithVat / (1 + $vatRate), 10);
    }
}
