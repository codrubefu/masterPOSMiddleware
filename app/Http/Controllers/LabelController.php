<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function create()
    {
        return view('labels.create', [
            'products' => $this->demoProducts(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'selected_products' => ['required', 'string'],
        ]);

        $selectedProducts = collect(json_decode($validated['selected_products'], true))
            ->filter(fn (array $item) => isset($item['id'], $item['quantity']) && (int) $item['quantity'] > 0)
            ->values();

        if ($selectedProducts->isEmpty()) {
            return back()
                ->withErrors(['selected_products' => 'Selectează cel puțin un produs.'])
                ->withInput();
        }

        $productsById = collect($this->demoProducts())->keyBy('id');

        $labels = $selectedProducts
            ->map(function (array $item) use ($productsById) {
                $product = $productsById->get((int) $item['id']);
                if (! $product) {
                    return null;
                }

                return array_merge($product, [
                    'quantity' => (int) $item['quantity'],
                ]);
            })
            ->filter()
            ->values();

        if ($labels->isEmpty()) {
            return back()
                ->withErrors(['selected_products' => 'Produsele selectate nu sunt valide.'])
                ->withInput();
        }

        $pdf = Pdf::loadView('labels.pdf', [
            'labels' => $labels,
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download('etichete-produse.pdf');
    }

    private function demoProducts(): array
    {
        return [
            ['id' => 1, 'name' => 'Cafea boabe Premium 1kg', 'price' => 79.90, 'sku' => 'CAF-001'],
            ['id' => 2, 'name' => 'Ceai Verde Bio 50 plicuri', 'price' => 24.50, 'sku' => 'TEA-002'],
            ['id' => 3, 'name' => 'Ciocolată neagră 85%', 'price' => 12.99, 'sku' => 'CHO-003'],
            ['id' => 4, 'name' => 'Biscuiți integrali', 'price' => 8.40, 'sku' => 'BIS-004'],
            ['id' => 5, 'name' => 'Miere polifloră 500g', 'price' => 31.20, 'sku' => 'MIE-005'],
            ['id' => 6, 'name' => 'Fulgi de ovăz 1kg', 'price' => 14.00, 'sku' => 'OVZ-006'],
        ];
    }
}
