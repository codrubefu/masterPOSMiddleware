<?php

namespace App\Http\Controllers;

use App\Services\BonDatabaseService;
use App\Services\BonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class PaymentsController extends Controller
{

    protected $bonService;
    public function __construct(BonService $bonService)
    {
        $this->bonService = $bonService;
    }


    /**
     * Calculate subtotal
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subTotal(Request $request)
    {
        try {
            // TODO: Implement subTotal logic
            
            Log::info('SubTotal calculation requested', $request->all());
            $this->bonService->writeSubtotal($request->all());
            return response()->json([
                'success' => true,
                'message' => 'SubTotal calculated successfully',
                'data' => [
                    'subtotal' => 0.00,
                    'calculated_at' => now()->toDateTimeString(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error calculating subtotal: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate subtotal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payment(Request $request, BonDatabaseService $bonDatabaseService)
    {
            $this->saveSentBackup($request->all());
            $this->bonService->writeBonFinal($request->all());
            $company = Company::first();
            
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'bon_no' => $company->nrbfdude,
                    'processed_at' => now()->toDateTimeString(),
                ]
            ], 200);
       
    }

    protected function saveSentBackup(array $data): void
    {
        $paymentType = 'numRON';
        if (isset($data['type'])) {
            if ($data['type'] == 'cash') {
                $paymentType = 'numRON';
            } elseif ($data['type'] == 'card') {
                $paymentType = 'ccRON';
            } else {
                $paymentType = 'ppRON';
            }
        }

        $nrBonSent = DB::table('dbo.trzcfePOSSent')->insertGetId([
            'idfirma' => 1,
            'idcl' => $data['customer']['id'] ?? $data['idcl'] ?? 1,
            'stotalron' => $data['subtotal'] ?? 0,
            'redabs' => $data['redabs'] ?? 0,
            'redproc' => $data['discount_percentage'] ?? 0,
            'itotalron' => $data['subtotal'] ?? 0,
            'itotaleur' => null,
            'itotalusd' => null,
            'modp' => $paymentType,
            'nrtrzcc' => null,
            'tipcc' => null,
            'tipv' => 'RON',
            'data' => now(),
            'compid' => 'AriPos' . $data['casa'],
            'nrbonspec' => null,
            'costtot' => null,
            'chit' => false,
            'idtrzcf' => null,
            'casa' => $data['casa'] ?? 1,
            'nrdispliv' => 0,
            'nrbontrzcfeaux' => null,
            'idlogin' => 0,
            'userlogin' => ' ',
            'numerar' => $data['numerarAmount'] ?? 0.00,
            'card' => $data['cardAmount'] ?? 0.00,
            'nrnp' => null,
            'datac' => now(),
            'tichete' => 0.00,
            'cuibf' => $data['customer']['cui'] ?? 0,
            'idrapz' => 0,
            'anulat' => false,
        ], 'nrbonfint');

        foreach ($data['items'] as $item) {
            [$casa, $compId] = $this->getSentDetailCasaAndCompId($item, $data['casa']);

            DB::table('dbo.trzdetcfPOSSent')->insert([
                'idfirma' => 1,
                'casa' => $casa,
                'nrbonf' => $nrBonSent,
                'idcl' => $data['customer']['id'] ?? 1,
                'art' => $item['product']['name'],
                'cant' => $item['qty'] . '.000',
                'pretueur' => $item['pretueur'] ?? 0.00,
                'preturon' => $item['product']['price'],
                'redabs' => $item['redabs'] ?? 0.00,
                'redproc' => $item['redproc'] ?? 0.00,
                'valoare' => $item['product']['price'] * $item['qty'],
                'data' => now(),
                'compid' => $compId,
                'inchidzi' => false,
                'genconsum' => false,
                'pretfaradisc' => $item['product']['price'],
                'upc' => $item['product']['upc'] ?? null,
                'cotatva' => $this->getSentDetailTva($item),
                'art2' => $item['art2'] ?? null,
            ]);
        }
    }

    protected function getSentDetailTva(array $item)
    {
        if ($item['product']['departament'] == 1) {
            return $item['product']['tax1'];
        } elseif ($item['product']['departament'] == 2) {
            return $item['product']['tax2'];
        } elseif ($item['product']['departament'] == 3) {
            return $item['product']['tax3'];
        }

        return 0;
    }

    protected function getSentDetailCasaAndCompId(array $item, $requestCasa): array
    {
        if ($requestCasa == 1) {
            if ($item['product']['gest'] == 3) {
                return [8, 'AriPos' . $requestCasa];
            }

            return [9, 'AriPos' . $requestCasa];
        } elseif ($requestCasa == 2) {
            if ($item['product']['gest'] == 3) {
                return [10, 'AriPos' . $requestCasa];
            }

            return [11, 'AriPos' . $requestCasa];
        } elseif ($requestCasa == 3) {
            if ($item['product']['gest'] == 3) {
                return [12, 'AriPos' . $requestCasa];
            }

            return [13, 'AriPos' . $requestCasa];
        }

        return [$requestCasa, 'AriPos' . $requestCasa];
    }

    public function saveBonInDatabase(Request $request, BonDatabaseService $bonDatabaseService)
    {
        $bonDatabaseService->save($request);
    }

    public function isPaymentDone(Request $request, BonService $bonService)
    {
        $success = $bonService->isPaymentDone($request->casa, $request->bon_no);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Payment is done' : 'Payment is not done',
            'data' => []
        ], 200);
    }

    /**
     * Reset casa bon file
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        try {
            $casa = $request->query('casa', 1);
            
            Log::info('Reset requested for casa: ' . $casa);
            
            // Get casa path from config
            $casaFiles = config('casa.file');
            
            if (isset($casaFiles[$casa]['path']) && file_exists($casaFiles[$casa]['path'])) {
                $casaPath = $casaFiles[$casa]['path'];
            } else {
                $casaPath = storage_path('bon');
                if (!file_exists($casaPath)) {
                    mkdir($casaPath, 0755, true);
                }
            }
            
            $bonFilePath = $casaPath . '/bon.txt';
            
            // Clear bon.txt file
            if (file_put_contents($bonFilePath, '60') === false) {
                throw new \Exception('Failed to reset bon file.');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Reset completed successfully',
                'data' => [
                    'casa' => $casa,
                    'path' => $bonFilePath,
                    'reset_at' => now()->toDateTimeString(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error resetting bon file: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset bon file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
