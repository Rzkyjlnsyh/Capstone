<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\ExchangeRate;
use App\Models\HpeResult;
use App\Models\Product;
use App\Models\PurchaseHistory;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index()
    {
        if (request()->wantsJson() || request()->expectsJson()) {
            return $this->getDashboardData();
        }

        $data = $this->getDashboardDataArray();
        return view('pages.dashboard', $data);
    }

    private function getDashboardDataArray(): array
    {
        $totalProducts = Product::count();
        $totalComponents = Component::count();
        $totalPurchaseHistories = PurchaseHistory::count();
        $totalHpeResults = HpeResult::count();

        $latestRate = ExchangeRate::where('base_currency', 'USD')
            ->where('quote_currency', 'IDR')
            ->orderBy('rate_date', 'desc')
            ->first();

        $recentHpeResults = HpeResult::with('product')
            ->orderBy('calculated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($result) {
                return [
                    'id' => $result->id,
                    'product_name' => $result->product->name ?? '-',
                    'product_code' => $result->product->code ?? '-',
                    'total_with_margin' => $result->total_with_margin,
                    'calculated_at' => $result->calculated_at->format('d/m/Y H:i'),
                ];
            });

        $averageHpeTotal = HpeResult::avg('total_with_margin') ?? 0;
        $productsWithComponents = Product::has('productComponents')->count();

        return [
            'summary' => [
                'total_products' => $totalProducts,
                'total_components' => $totalComponents,
                'total_purchase_histories' => $totalPurchaseHistories,
                'total_hpe_results' => $totalHpeResults,
                'products_with_components' => $productsWithComponents,
                'average_hpe_total' => round($averageHpeTotal, 2),
            ],
            'exchange_rate' => $latestRate ? [
                'rate_date' => $latestRate->rate_date,
                'rate_value' => $latestRate->rate_value,
                'source' => $latestRate->source,
            ] : null,
            'recent_hpe_results' => $recentHpeResults,
        ];
    }

    private function getDashboardData(): JsonResponse
    {
        $totalProducts = Product::count();
        $totalComponents = Component::count();
        $totalPurchaseHistories = PurchaseHistory::count();
        $totalHpeResults = HpeResult::count();

        $latestRate = ExchangeRate::where('base_currency', 'USD')
            ->where('quote_currency', 'IDR')
            ->orderBy('rate_date', 'desc')
            ->first();

        $recentHpeResults = HpeResult::with('product')
            ->orderBy('calculated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($result) {
                return [
                    'id' => $result->id,
                    'product_name' => $result->product->name ?? '-',
                    'product_code' => $result->product->code ?? '-',
                    'total_with_margin' => $result->total_with_margin,
                    'calculated_at' => $result->calculated_at->format('d/m/Y H:i'),
                ];
            });

        $averageHpeTotal = HpeResult::avg('total_with_margin') ?? 0;

        $productsWithComponents = Product::has('productComponents')->count();

        return response()->json([
            'summary' => [
                'total_products' => $totalProducts,
                'total_components' => $totalComponents,
                'total_purchase_histories' => $totalPurchaseHistories,
                'total_hpe_results' => $totalHpeResults,
                'products_with_components' => $productsWithComponents,
                'average_hpe_total' => round($averageHpeTotal, 2),
            ],
            'exchange_rate' => $latestRate ? [
                'rate_date' => $latestRate->rate_date,
                'rate_value' => $latestRate->rate_value,
                'source' => $latestRate->source,
            ] : null,
            'recent_hpe_results' => $recentHpeResults,
        ]);
    }
}
