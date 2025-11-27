<?php

namespace App\Http\Controllers;

use App\Models\HpeResult;
use App\Models\Product;
use App\Services\HpeCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HpeController extends Controller
{
    public function __construct(
        private HpeCalculator $calculator
    ) {
    }

    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'margin_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $product = Product::with('productComponents.component')->findOrFail($data['product_id']);
        $margin = $data['margin_percent'] ?? 0;

        try {
            $result = $this->calculator->calculateForProduct(
                $product,
                $margin,
                auth()->id()
            );

            return response()->json([
                'message' => 'HPE berhasil dihitung',
                'data' => [
                    'id' => $result->id,
                    'product_id' => $result->product_id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'margin_percent' => $result->margin_percent,
                    'total_cost_idr' => $result->total_cost_idr,
                    'total_with_margin' => $result->total_with_margin,
                    'component_breakdown' => $result->component_breakdown,
                    'warnings' => $result->warnings,
                    'calculated_at' => $result->calculated_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghitung HPE',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $query = HpeResult::with('product', 'exchangeRate', 'calculatedBy');

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->where('calculated_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('calculated_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 15);
        $results = $query->orderBy('calculated_at', 'desc')->paginate($perPage);

        return response()->json($results);
    }

    public function show(HpeResult $hpeResult): JsonResponse
    {
        $hpeResult->load('product', 'exchangeRate', 'calculatedBy');

        return response()->json([
            'id' => $hpeResult->id,
            'product' => [
                'id' => $hpeResult->product->id,
                'code' => $hpeResult->product->code,
                'name' => $hpeResult->product->name,
            ],
            'margin_percent' => $hpeResult->margin_percent,
            'total_cost_idr' => $hpeResult->total_cost_idr,
            'total_with_margin' => $hpeResult->total_with_margin,
            'component_breakdown' => $hpeResult->component_breakdown,
            'warnings' => $hpeResult->warnings,
            'status' => $hpeResult->status,
            'exchange_rate' => $hpeResult->exchangeRate ? [
                'id' => $hpeResult->exchangeRate->id,
                'rate_date' => $hpeResult->exchangeRate->rate_date,
                'rate_value' => $hpeResult->exchangeRate->rate_value,
            ] : null,
            'calculated_by' => $hpeResult->calculatedBy ? [
                'id' => $hpeResult->calculatedBy->id,
                'name' => $hpeResult->calculatedBy->name,
            ] : null,
            'calculated_at' => $hpeResult->calculated_at,
            'created_at' => $hpeResult->created_at,
        ]);
    }
}
