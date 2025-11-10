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
        $client = Client::where('cardId', $id)->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        // Format response according to specified structure
        $customerData = [
            'id' => $client->idcl,
            'type' => $client->pj ? 'pj' : 'pf',
            'lastName' => $client->den,
            'firstName' => $client->prenume,
            'cardId' => $client->cardid,
            'discountPercent' => $client->discount ?? 0
        ];

        return response()->json([
            'success' => true,
            'message' => 'Customer retrieved successfully',
            'data' => $customerData
        ]);
    }
}