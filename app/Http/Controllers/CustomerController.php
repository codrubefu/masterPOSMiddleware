<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        // Demo customers data with the specified structure
        $demoClients = [
            1 => [
                'id' => 1,
                'type' => 'pf',
                'lastName' => 'Codrut',
                'firstName' => 'Befu',
                'cardId' => 'CI123456',
                'discountPercent' => 5
            ],
            2 => [
                'id' => 2,
                'type' => 'pj',
                'lastName' => 'SRL COMPANY',
                'firstName' => 'Test',
                'cardId' => 'J40/1234/2023',
                'discountPercent' => 10
            ],
            3 => [
                'id' => 3,
                'type' => 'pf',
                'lastName' => 'Ionescu',
                'firstName' => 'Maria',
                'cardId' => 'CI789012',
                'discountPercent' => 0
            ],
            4 => [
                'id' => 4,
                'type' => 'pf',
                'lastName' => 'Persoană fizică',
                'firstName' => '1',
                'cardId' => 'CI789012',
                'discountPercent' => 0
            ]
        ];

        // Find customer by ID
        if (!isset($demoClients[$id])) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer retrieved successfully',
            'data' => $demoClients[$id]
        ]);
    }
}