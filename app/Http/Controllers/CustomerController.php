<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        // Find customer by cardId
        $client = Client::where('cnpcui', trim($id))->first();
        
        // Load from anaf

       
        if(!$client) {
            $anafService = new \App\Services\AnafService();
            $anafData = $anafService->verifyVatStatus($id);
            if(!isset($anafData['found']) || empty($anafData['found'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found',
                    'data' => null
                ], 404);
            }

            $client = Client::saveFromAnafData($anafData['found'][0]);
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