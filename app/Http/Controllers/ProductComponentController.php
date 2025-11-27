<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\Product;
use App\Models\ProductComponent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductComponentController extends Controller
{
    public function store(Product $product, Request $request): JsonResponse
    {
        $data = $request->validate([
            'component_id' => ['required', 'exists:components,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_override' => ['nullable', 'string', 'max:20'],
            'unit_cost_override' => ['nullable', 'numeric', 'min:0'],
        ]);

        $component = Component::query()->findOrFail($data['component_id']);

        $productComponent = ProductComponent::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'component_id' => $component->id,
            ],
            [
                'quantity' => $data['quantity'],
                'unit_override' => $data['unit_override'] ?? null,
                'unit_cost_override' => $data['unit_cost_override'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Komponen ditambahkan',
            'data' => $productComponent->load('component'),
        ], 201);
    }

    public function update(Product $product, ProductComponent $productComponent, Request $request): JsonResponse
    {
        if ($productComponent->product_id !== $product->id) {
            abort(404);
        }

        $data = $request->validate([
            'quantity' => ['sometimes', 'numeric', 'min:0.001'],
            'unit_override' => ['nullable', 'string', 'max:20'],
            'unit_cost_override' => ['nullable', 'numeric', 'min:0'],
        ]);

        $productComponent->update($data);

        return response()->json([
            'message' => 'Komponen diperbarui',
            'data' => $productComponent->load('component'),
        ]);
    }

    public function destroy(Product $product, ProductComponent $productComponent): JsonResponse
    {
        if ($productComponent->product_id !== $product->id) {
            abort(404);
        }

        $productComponent->delete();

        return response()->json(['message' => 'Komponen dihapus dari produk']);
    }
}
