<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Search for products
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // Get search parameters
        $query = $request->get('query', '');
        $category = $request->get('category', '');
        $limit = $request->get('limit', 10);

        // Demo products data
        $demoProducts = [
            [
                'id' => 1,
                'name' => 'Cafea boabe 1kg',
                'upc' => '5941234567890',
                'price' => 79.90,
                'quantity' => 50
            ],
            [
                'id' => 2,
                'name' => 'Lapte 1L',
                'upc' => '5940987654321',
                'price' => 5.49,
                'quantity' => 120
            ],
            [
                'id' => 3,
                'name' => 'Pâine integrală',
                'upc' => '5940001112223',
                'price' => 8.25,
                'quantity' => 30
            ],
            [
                'id' => 4,
                'name' => 'Ciocolată neagră 85%',
                'upc' => '5945557770001',
                'price' => 12.35,
                'quantity' => 75
            ],
            [
                'id' => 5,
                'name' => 'Ulei de măsline extra virgin 750ml',
                'upc' => '5942228889999',
                'price' => 42.50,
                'quantity' => 25
            ],
            [
                'id' => 6,
                'name' => 'Apă minerală 2L',
                'upc' => '5940123456789',
                'price' => 3.99,
                'quantity' => 200,
                'sgr' => true
            ],
            [
                'id' => 7,
                'name' => 'Brânză telemea 500g',
                'upc' => '5940987123456',
                'price' => 15.80,
                'quantity' => 40
            ],
            [
                'id' => 8,
                'name' => 'Cereale pentru mic dejun 375g',
                'upc' => '5941111222333',
                'price' => 18.90,
                'quantity' => 60
            ],
            [
                'id' => 9,
                'name' => 'Coca cola 2L',
                'upc' => '5944445556667',
                'price' => 25.00,
                'quantity' => 80,
                'sgr' => true
            ],
            [
                'id' => 10,
                'name' => 'Fanta portocale 2L',
                'upc' => '5947778889990',
                'price' => 10.50,
                'quantity' => 90,
                'sgr' => true
            ]
        ];

        // Filter products based on search parameters
        $filteredProducts = collect($demoProducts);

        // Filter by query (search in name and UPC)
        if (!empty($query)) {
            $filteredProducts = $filteredProducts->filter(function ($product) use ($query) {
                return stripos($product['name'], $query) !== false ||
                       stripos($product['upc'], $query) !== false;
            });
        }

        // Filter by category (not applicable for products, but keeping for API compatibility)
        if (!empty($category)) {
            // You could implement product categories here if needed
            // For now, we'll just ignore this filter for products
        }

        // Limit results
        $filteredProducts = $filteredProducts->take($limit);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'products' => $filteredProducts->values(),
                'total' => $filteredProducts->count(),
                'search_params' => [
                    'query' => $query,
                    'category' => $category,
                    'limit' => $limit
                ]
            ]
        ]);
    }

    /**
     * Get a single product by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Demo products data (same as above for consistency)
        $demoProducts = [
            1 => [
                'id' => 1,
                'name' => 'Cafea boabe 1kg',
                'upc' => '5941234567890',
                'price' => 79.90,
                'quantity' => 50
            ],
            2 => [
                'id' => 2,
                'name' => 'Lapte 1L',
                'upc' => '5940987654321',
                'price' => 5.49,
                'quantity' => 120
            ],
            3 => [
                'id' => 3,
                'name' => 'Pâine integrală',
                'upc' => '5940001112223',
                'price' => 8.25,
                'quantity' => 30
            ],
            4 => [
                'id' => 4,
                'name' => 'Ciocolată neagră 85%',
                'upc' => '5945557770001',
                'price' => 12.35,
                'quantity' => 75
            ],
            5 => [
                'id' => 5,
                'name' => 'Ulei de măsline extra virgin 750ml',
                'upc' => '5942228889999',
                'price' => 42.50,
                'quantity' => 25
            ],
            6 => [
                'id' => 6,
                'name' => 'Apă minerală 2L',
                'upc' => '5940123456789',
                'price' => 3.99,
                'quantity' => 200,
                'sgr' => true
            ],
            7 => [
                'id' => 7,
                'name' => 'Brânză telemea 500g',
                'upc' => '5940987123456',
                'price' => 15.80,
                'quantity' => 40
            ],
            8 => [
                'id' => 8,
                'name' => 'Cereale pentru mic dejun 375g',
                'upc' => '5941111222333',
                'price' => 18.90,
                'quantity' => 60
            ],
            9 => [
                'id' => 9,
                'name' => 'Coca cola 2L',
                'upc' => '5944445556667',
                'price' => 25.00,
                'quantity' => 80,
                'sgr' => true
            ],
            10 => [
                'id' => 10,
                'name' => 'Fanta portocale 2L',
                'upc' => '5947778889990',
                'price' => 10.50,
                'quantity' => 90,
                'sgr' => true
            ]
        ];

        // Find product by ID
        if (!isset($demoProducts[$id])) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => $demoProducts[$id]
        ]);
    }

    /**
     * Get all products
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);

        // Demo products data
        $demoProducts = [
            [
                'id' => 1,
                'name' => 'Cafea boabe 1kg',
                'upc' => '5941234567890',
                'price' => 79.90,
                'quantity' => 50
            ],
            [
                'id' => 2,
                'name' => 'Lapte 1L',
                'upc' => '5940987654321',
                'price' => 5.49,
                'quantity' => 120
            ],
            [
                'id' => 3,
                'name' => 'Pâine integrală',
                'upc' => '5940001112223',
                'price' => 8.25,
                'quantity' => 30
            ],
            [
                'id' => 4,
                'name' => 'Ciocolată neagră 85%',
                'upc' => '5945557770001',
                'price' => 12.35,
                'quantity' => 75
            ],
            [
                'id' => 5,
                'name' => 'Ulei de măsline extra virgin 750ml',
                'upc' => '5942228889999',
                'price' => 42.50,
                'quantity' => 25
            ],
            [
                'id' => 6,
                'name' => 'Apă minerală 2L',
                'upc' => '5940123456789',
                'price' => 3.99,
                'quantity' => 200
            ],
            [
                'id' => 7,
                'name' => 'Brânză telemea 500g',
                'upc' => '5940987123456',
                'price' => 15.80,
                'quantity' => 40
            ],
            [
                'id' => 8,
                'name' => 'Cereale pentru mic dejun 375g',
                'upc' => '5941111222333',
                'price' => 18.90,
                'quantity' => 60
            ]
        ];

        $total = count($demoProducts);
        $offset = ($page - 1) * $perPage;
        $products = array_slice($demoProducts, $offset, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'products' => $products,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ]
        ]);
    }
}
