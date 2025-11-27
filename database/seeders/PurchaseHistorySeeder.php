<?php

namespace Database\Seeders;

use App\Models\Component;
use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\PurchaseHistory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::query()->get()->keyBy('code');
        $components = Component::query()->get()->keyBy('code');
        $rates = ExchangeRate::query()->get()->mapWithKeys(function (ExchangeRate $rate) {
            $dateKey = $rate->rate_date instanceof \Illuminate\Support\Carbon
                ? $rate->rate_date->toDateString()
                : (string) $rate->rate_date;

            return [$dateKey => $rate];
        });

        $rows = [
            ['PRD-TB01', 'CMP-ALU', '2025-10-21', 'USD', 16600, 7.2],
            ['PRD-TB01', 'CMP-ALU', '2025-10-27', 'USD', 16628, 7.1],
            ['PRD-TB01', 'CMP-ALU', '2025-11-03', 'USD', 16660, 7.3],
            ['PRD-TB01', 'CMP-PAINT', '2025-10-22', 'IDR', 1, 50000],
            ['PRD-TB01', 'CMP-PAINT', '2025-10-29', 'IDR', 1, 52000],
            ['PRD-TB01', 'CMP-PAINT', '2025-11-03', 'IDR', 1, 48000],
            ['PRD-TB01', 'CMP-GRIP', '2025-10-23', 'IDR', 1, 35000],
            ['PRD-TB01', 'CMP-GRIP', '2025-10-30', 'IDR', 1, 36500],
            ['PRD-TB01', 'CMP-GRIP', '2025-11-03', 'IDR', 1, 34000],
            ['PRD-RB02', 'CMP-STRING', '2025-10-24', 'USD', 16630, 3.5],
            ['PRD-RB02', 'CMP-STRING', '2025-10-31', 'USD', 16645, 3.6],
            ['PRD-RB02', 'CMP-STRING', '2025-11-03', 'USD', 16660, 3.55],
            ['PRD-RB02', 'CMP-GRIP', '2025-10-25', 'IDR', 1, 28000],
            ['PRD-RB02', 'CMP-GRIP', '2025-11-01', 'IDR', 1, 30000],
            ['PRD-RB02', 'CMP-GRIP', '2025-11-03', 'IDR', 1, 29000],
            ['PRD-HP03', 'CMP-SHELL', '2025-10-26', 'USD', 16620, 12],
            ['PRD-HP03', 'CMP-SHELL', '2025-11-02', 'USD', 16655, 12.1],
            ['PRD-HP03', 'CMP-SHELL', '2025-11-03', 'USD', 16660, 12.05],
            ['PRD-HP03', 'CMP-PAINT', '2025-10-28', 'IDR', 1, 40000],
            ['PRD-HP03', 'CMP-PAINT', '2025-11-02', 'IDR', 1, 42000],
            ['PRD-HP03', 'CMP-PAINT', '2025-11-03', 'IDR', 1, 41500],
        ];

        foreach ($rows as [$productCode, $componentCode, $date, $currency, $rateSnapshot, $priceOriginal]) {
            $product = $products->get($productCode);
            $component = $components->get($componentCode);

            if (! $product || ! $component) {
                continue;
            }

            $rateModel = $rates->get($date);
            $exchangeRateId = $rateModel?->id;

            $unitPriceIdr = $currency === 'USD'
                ? $priceOriginal * $rateSnapshot
                : $priceOriginal;

            PurchaseHistory::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'component_id' => $component->id,
                    'purchase_date' => $date,
                ],
                [
                    'vendor_name' => 'Vendor ' . substr($componentCode, -1),
                    'currency' => $currency,
                    'exchange_rate_id' => $exchangeRateId,
                    'rate_value_snapshot' => $rateSnapshot,
                    'quantity' => 1,
                    'unit_price_original' => $priceOriginal,
                    'unit_price_idr' => $unitPriceIdr,
                    'notes' => 'Sample historical record',
                ]
            );
        }
    }
}
