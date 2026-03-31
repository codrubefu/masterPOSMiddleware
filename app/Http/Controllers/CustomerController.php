<?php

namespace App\Http\Controllers;

use App\Models\Client;

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
            $anafService = new \App\Services\AnafService();
            $ping = $anafService->ping();
            $anafData = $anafService->verifyVatStatus($id);
            if (!isset($anafData['found']) || empty($anafData['found'])) {
                return response()->json([
                    'success' => false,
                    'message' => $ping ? 'Clientul nu a fost găsit în baza de date și nici în ANAF' : 'Clientul nu a fost găsit în baza de date și ANAF nu este disponibil',
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
