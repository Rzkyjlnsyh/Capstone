<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\PurchaseHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseHistoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseHistory::query()->with(['product', 'component', 'exchangeRate'])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id');

        if ($productId = $request->integer('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($componentId = $request->integer('component_id')) {
            $query->where('component_id', $componentId);
        }

        if ($dateFrom = $request->date('date_from')) {
            $query->whereDate('purchase_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->date('date_to')) {
            $query->whereDate('purchase_date', '<=', $dateTo);
        }

        $perPage = (int) $request->integer('per_page', 15);

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request, true);

        $rateSnapshot = $this->resolveRateValue($data, null, $data['currency']);
        $unitPriceIdr = $this->calculateUnitPriceIdr($data['currency'], $data['unit_price_original'], $rateSnapshot);

        $history = PurchaseHistory::query()->create([
            ...$data,
            'rate_value_snapshot' => $rateSnapshot,
            'quantity' => $data['quantity'] ?? 1,
            'unit_price_idr' => $unitPriceIdr,
        ]);

        return response()->json($history->load(['product', 'component', 'exchangeRate']), 201);
    }

    public function show(PurchaseHistory $purchaseHistory): JsonResponse
    {
        return response()->json($purchaseHistory->load(['product', 'component', 'exchangeRate']));
    }

    public function update(Request $request, PurchaseHistory $purchaseHistory): JsonResponse
    {
        $data = $this->validateData($request);

        $currency = $data['currency'] ?? $purchaseHistory->currency;
        $unitPriceOriginal = $data['unit_price_original'] ?? $purchaseHistory->unit_price_original;

        $rateSnapshot = $this->resolveRateValue(
            $data,
            $purchaseHistory->exchange_rate_id,
            $currency
        ) ?? $purchaseHistory->rate_value_snapshot;

        $unitPriceIdr = $this->calculateUnitPriceIdr(
            $currency,
            $unitPriceOriginal,
            $rateSnapshot
        );

        $purchaseHistory->update([
            ...$data,
            'rate_value_snapshot' => $rateSnapshot,
            'unit_price_idr' => $unitPriceIdr,
        ]);

        return response()->json($purchaseHistory->fresh()->load(['product', 'component', 'exchangeRate']));
    }

    public function destroy(PurchaseHistory $purchaseHistory): JsonResponse
    {
        $purchaseHistory->delete();

        return response()->json(['message' => 'Riwayat pengadaan dihapus']);
    }

    protected function validateData(Request $request, bool $isCreate = false): array
    {
        $required = $isCreate ? 'required' : 'sometimes';

        return $request->validate([
            'product_id' => [$required, 'exists:products,id'],
            'component_id' => [$required, 'exists:components,id'],
            'purchase_date' => [$required, 'date'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'currency' => [$required, 'in:USD,IDR'],
            'exchange_rate_id' => ['nullable', 'exists:exchange_rates,id'],
            'rate_value_snapshot' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'numeric', 'min:0.001'],
            'unit_price_original' => [$required, 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'document_reference' => ['nullable', 'string', 'max:255'],
        ]);
    }

    protected function resolveRateValue(array $data, ?int $existingRateId = null, ?string $defaultCurrency = null): ?float
    {
        $rateValue = $data['rate_value_snapshot'] ?? null;
        $exchangeRateId = $data['exchange_rate_id'] ?? $existingRateId;
        $currency = $data['currency'] ?? $defaultCurrency;

        if (! $rateValue && $exchangeRateId) {
            $rate = ExchangeRate::query()->find($exchangeRateId);
            $rateValue = $rate?->rate_value;
        }

        if ($currency === 'USD' && ! $rateValue) {
            abort(422, 'Kurs wajib diisi untuk transaksi USD');
        }

        if ($currency === 'IDR') {
            $rateValue = $rateValue ?: 1;
        }

        return $rateValue;
    }

    protected function calculateUnitPriceIdr(string $currency, float $unitPriceOriginal, ?float $rateSnapshot): float
    {
        if ($currency === 'USD') {
            if (! $rateSnapshot) {
                abort(422, 'Tidak bisa menghitung harga IDR tanpa kurs');
            }

            return $unitPriceOriginal * $rateSnapshot;
        }

        return $unitPriceOriginal;
    }
}
