<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\HpeResult;
use App\Models\Product;
use App\Models\PurchaseHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HpeCalculator
{
    /**
     * Hitung HPE untuk satu produk berdasarkan riwayat pengadaan dan kurs terbaru.
     *
     * @param  Product  $product        Produk yang akan dihitung HPE-nya.
     * @param  float    $marginPercent  Margin tambahan dalam persen (misal 10 untuk 10%).
     * @param  int|null $userId         ID user yang melakukan perhitungan (opsional).
     * @param  ExchangeRate|null $currentRate  Kurs yang akan dipakai (opsional, jika null ambil terbaru).
     *
     * @return HpeResult
     */
    public function calculateForProduct(
        Product $product,
        float $marginPercent = 0,
        ?int $userId = null,
        ?ExchangeRate $currentRate = null
    ): HpeResult {
        $currentRate ??= $this->getLatestUsdIdrRate();

        $warnings = [];

        if (! $currentRate) {
            $warnings[] = 'NO_EXCHANGE_RATE_FOUND';
        }

        $componentBreakdown = [];
        $totalBase = 0.0;

        $product->loadMissing(['productComponents.component', 'purchaseHistories']);

        foreach ($product->productComponents as $productComponent) {
            $component = $productComponent->component;

            if (! $component) {
                $warnings[] = "MISSING_COMPONENT_FOR_PRODUCT_COMPONENT_{$productComponent->id}";
                continue;
            }

            $histories = $this->getLatestHistoriesForComponent($product, $component);

            if ($histories->isEmpty()) {
                $componentWarnings = ['NO_HISTORY'];
                $componentBreakdown[] = $this->buildComponentBreakdownItem(
                    $productComponent,
                    $histories,
                    0.0,
                    0.0,
                    $componentWarnings
                );

                $warnings[] = "NO_HISTORY_FOR_COMPONENT_{$component->id}";

                continue;
            }

            if ($histories->count() < 3) {
                $warnings[] = "INSUFFICIENT_HISTORY_FOR_COMPONENT_{$component->id}";
            }

            $normalizedPrices = $histories->map(function (PurchaseHistory $history) use ($currentRate) {
                return $this->normalizeHistoryUnitPriceIdr($history, $currentRate);
            })->filter(static fn (?float $v) => $v !== null);

            if ($normalizedPrices->isEmpty()) {
                $componentWarnings = ['UNABLE_TO_NORMALIZE_PRICES'];

                $componentBreakdown[] = $this->buildComponentBreakdownItem(
                    $productComponent,
                    $histories,
                    0.0,
                    0.0,
                    $componentWarnings
                );

                $warnings[] = "UNABLE_TO_NORMALIZE_PRICES_FOR_COMPONENT_{$component->id}";

                continue;
            }

            $averageUnitPrice = $normalizedPrices->avg();
            $subtotal = $averageUnitPrice * (float) $productComponent->quantity;

            $totalBase += $subtotal;

            $componentBreakdown[] = $this->buildComponentBreakdownItem(
                $productComponent,
                $histories,
                $averageUnitPrice,
                $subtotal,
                []
            );
        }

        $marginPercent = max(0.0, $marginPercent);
        $marginValue = $totalBase * ($marginPercent / 100);
        $totalWithMargin = $totalBase + $marginValue;

        if ($currentRate && $this->isRateStale($currentRate)) {
            $warnings[] = 'EXCHANGE_RATE_STALE';
        }

        $warnings = array_values(array_unique($warnings));

        return DB::transaction(function () use (
            $product,
            $currentRate,
            $userId,
            $marginPercent,
            $totalBase,
            $totalWithMargin,
            $componentBreakdown,
            $warnings
        ): HpeResult {
            return HpeResult::query()->create([
                'product_id' => $product->id,
                'exchange_rate_id' => $currentRate?->id,
                'calculated_by' => $userId,
                'margin_percent' => $marginPercent,
                'total_cost_idr' => $totalBase,
                'total_with_margin' => $totalWithMargin,
                'status' => 'draft',
                'component_breakdown' => $componentBreakdown,
                'warnings' => $warnings,
                'calculated_at' => Carbon::now(),
            ]);
        });
    }

    /**
     * Ambil minimal tiga transaksi terakhir per komponen (kalau ada).
     *
     * @return Collection<int, PurchaseHistory>
     */
    protected function getLatestHistoriesForComponent(Product $product, $component): Collection
    {
        return PurchaseHistory::query()
            ->where('product_id', $product->id)
            ->where('component_id', $component->id)
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->limit(3)
            ->get();
    }

    /**
     * Normalisasi harga unit IDR sebuah transaksi ke kurs terbaru.
     */
    protected function normalizeHistoryUnitPriceIdr(PurchaseHistory $history, ?ExchangeRate $currentRate): ?float
    {
        if ($history->unit_price_idr === null) {
            return null;
        }

        $currency = strtoupper($history->currency ?? 'IDR');

        // Jika transaksi sudah dalam IDR, tidak perlu penyesuaian kurs.
        if ($currency === 'IDR') {
            return (float) $history->unit_price_idr;
        }

        if (! $currentRate) {
            return null;
        }

        $snapshotRate = $history->rate_value_snapshot ?: $currentRate->rate_value;

        if (! $snapshotRate || $snapshotRate <= 0) {
            return null;
        }

        return (float) $history->unit_price_idr * ($currentRate->rate_value / $snapshotRate);
    }

    /**
     * Bentuk struktur breakdown komponen untuk disimpan di JSON.
     *
     * @param  Collection<int, PurchaseHistory>  $histories
     * @param  array<int, string>                $warnings
     * @return array<string, mixed>
     */
    protected function buildComponentBreakdownItem(
        $productComponent,
        Collection $histories,
        float $averageUnitPrice,
        float $subtotal,
        array $warnings
    ): array {
        $component = $productComponent->component;

        return [
            'component_id' => $component?->id,
            'component_code' => $component?->code,
            'component_name' => $component?->name,
            'bom_quantity' => (float) $productComponent->quantity,
            'unit' => $productComponent->unit_override ?: $component?->unit,
            'history_count' => $histories->count(),
            'history_ids' => $histories->pluck('id')->all(),
            'average_unit_price_idr' => $averageUnitPrice,
            'subtotal_idr' => $subtotal,
            'warnings' => $warnings,
        ];
    }

    /**
     * Ambil kurs USD -> IDR terbaru dari tabel exchange_rates.
     */
    protected function getLatestUsdIdrRate(): ?ExchangeRate
    {
        return ExchangeRate::query()
            ->where('base_currency', 'USD')
            ->where('quote_currency', 'IDR')
            ->orderByDesc('rate_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Anggap kurs "basi" jika lebih tua dari beberapa hari (default 7 hari).
     */
    protected function isRateStale(ExchangeRate $rate, int $thresholdDays = 7): bool
    {
        $date = $rate->rate_date ?? $rate->created_at;

        if (! $date) {
            return true;
        }

        return Carbon::parse($date)->diffInDays(Carbon::now()) > $thresholdDays;
    }
}


