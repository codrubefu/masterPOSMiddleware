<?php

namespace App\Http\Controllers;

use App\Models\UpcGenprod;
use Illuminate\Http\Request;
use App\Models\TrzBoncurdel;

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
        $query = $request->get('query', '');
        $product = UpcGenprod::findByUpc($query)->first();
    
        return $this->jsonResponse($product, 'Product retrieved successfully');
    }

    /**
     * Get a single product by ID
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $product = UpcGenprod::findByUpc($id)->first();
        
        if (!$product) {
            return $this->jsonResponse(null, 'Product not found', false, 404);
        }
        
        return $this->jsonResponse(
            $this->formatProduct($product, 1),
            'Product retrieved successfully'
        );
    }

    /**
     * Get all products
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = $request->get('query', '');
        $product = UpcGenprod::findByUpc($query)->first();
    
        return $this->jsonResponse($product, 'Product retrieved successfully');
    }

    /**
     * Update a product (returns product with quantity-based pricing)
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $product = UpcGenprod::findByUpc($id)->first();
        
        if (!$product) {
            return $this->jsonResponse(null, 'Product not found', false, 404);
        }
        
        $quantity = $request->get('qty', 1);
        
        return $this->jsonResponse(
            $this->formatProduct($product, $quantity),
            'Product updated successfully'
        );
    }

    /**
     * Delete a product
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id, Request $request)
    {
        $product = UpcGenprod::findByUpc($id)->first();
        
        if (!$product) {
            return $this->jsonResponse(null, 'Product not found', false, 404);
        }

        $trzBoncurdel = new TrzBoncurdel();
        $trzBoncurdel->idfirma = $product->idfirma;
        $trzBoncurdel->idcl = 0;
        $trzBoncurdel->art = $product->art;
        $trzBoncurdel->cant = $request->get('qty', 1);
        $trzBoncurdel->pretu = $request->get('price', 0.00);
        $trzBoncurdel->redabs = 0.00;
        $trzBoncurdel->redproc = 0.00;
        $trzBoncurdel->tipv = 'RON';
        $trzBoncurdel->data = now();
        $trzBoncurdel->utilizator = 'CASA';
        $trzBoncurdel->clasa = str_pad('', 30, ' ');
        $trzBoncurdel->grupa = str_pad('', 20, ' ');
        $trzBoncurdel->puncte = 0.00;
        $trzBoncurdel->casa = 1;
        $trzBoncurdel->datac = now();
        $trzBoncurdel->tip = TrzBoncurdel::TYPE_STERGERE;
        $trzBoncurdel->save();
        
        return $this->jsonResponse(
            $this->formatProduct($product, 1),
            'Product deleted successfully'
        );
    }

    /**
     * Format product data with pricing
     *
     * @param UpcGenprod $product
     * @param int $quantity
     * @return array
     */
    protected function formatProduct($product, $quantity = 1)
    {
        $prices = $product->prices->toArray();
        return [
            'id' => $product->upc,
            'name' => $product->art,
            'upc' => $product->upc,
            'price' => $this->getPrice($quantity, $prices),
            'quantity' => $quantity,
            'sgr' => $product->ambsgr
        ];
    }

    /**
     * Get the appropriate price based on quantity
     * If quantity < 6, return higher price
     * If quantity >= 6, return lower price
     *
     * @param int $quantity
     * @param array $prices
     * @return float|null
     */
    protected function getPrice($quantity, $prices)
    {
        if (empty($prices)) {
            return null;
        }

        $priceValues = array_column($prices, 'pret');
        $maxPrice = max($priceValues);
        $minPrice = min($priceValues);

        return $quantity < 6 ? $maxPrice : $minPrice;
    }

    /**
     * Standard JSON response format
     *
     * @param mixed $data
     * @param string $message
     * @param bool $success
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse($data, $message, $success = true, $statusCode = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}
