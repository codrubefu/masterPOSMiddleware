<?php

namespace App\Http\Controllers;

use App\Models\TrzCfe;
use App\Models\TrzDetCf;
use App\Services\BonDatabaseService;
use App\Services\BonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            //$this->bonService->writeBonFinal($request->all());
            //$bonDatabaseService->save($request);
            $currentBon = 12;
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'bon_no' => $currentBon,
                    'processed_at' => now()->toDateTimeString(),
                ]
            ], 200);
       
    }

    public function isPaymentDone(Request $request, BonService $bonService)
    {
        $success = $bonService->isPaymentDone($request->casa);
        
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
