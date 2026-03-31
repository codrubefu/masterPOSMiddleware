<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\AnafService;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
 /**
     * Get a single customer by ID
     *
     * @param string|int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $client = Client::findByTaxIdentifier($id);

        if (!$client) {
            $anafService = new AnafService();

            if (!$anafService->ping()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ANAF nu este disponibil momentan',
                    'data' => null,
                ], 500);
            }

            try {
                $anafData = $anafService->verifyVatStatus($id);
            } catch (\Throwable $exception) {
                Log::error('ANAF lookup failed', [
                    'id' => $id,
                    'message' => $exception->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Nu s-a putut verifica clientul in ANAF',
                    'data' => null,
                ], 500);
            }

            if (!isset($anafData['found']) || empty($anafData['found'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clientul nu a fost găsit în baza de date și nici în ANAF',
                    'data' => null,
                ], 404);
            }

            Client::saveFromAnafData($anafData['found'][0]);
            $client = Client::findByTaxIdentifier($id);
            }
        
        // Format response according to specified structure
        $customerData = [
            'id' => $client->idcl,
            'type' => $client->pj ? 'pj' : 'pf',
            'lastName' => $client->den,
            'firstName' => $client->prenume,
            'cardId' => $client->cardid,
            'cnpcui' => $client->cnpcui,
            'discountPercent' => $client->discount ?? 0
        ];

        return response()->json([
            'success' => true,
            'message' => 'Customer retrieved successfully',
            'data' => $customerData
        ]);
    }
}
