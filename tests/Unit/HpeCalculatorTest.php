<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\HpeCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HpeCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_hpe_for_product_with_seed_data(): void
    {
        Carbon::setTestNow('2025-11-05 00:00:00');

        $this->seed();

        $product = Product::query()->where('code', 'PRD-TB01')->firstOrFail();

        /** @var HpeCalculator $calculator */
        $calculator = app(HpeCalculator::class);

        $result = $calculator->calculateForProduct($product, 10.0);

        $this->assertSame($product->id, $result->product_id);
        $this->assertSame(10.0, (float) $result->margin_percent);
        $this->assertCount(3, $result->component_breakdown);
        $this->assertEmpty($result->warnings ?? []);

        foreach ($result->component_breakdown as $component) {
            $this->assertSame(3, $component['history_count']);
            $this->assertEmpty($component['warnings']);
        }

        $expectedBase = $this->expectedTotalCostForTongkatBaseball(16664.0);
        $this->assertEqualsWithDelta($expectedBase, (float) $result->total_cost_idr, 0.01);
        $this->assertEqualsWithDelta(
            $expectedBase * 1.10,
            (float) $result->total_with_margin,
            0.05
        );

        Carbon::setTestNow();
    }

    private function expectedTotalCostForTongkatBaseball(float $currentRate): float
    {
        $components = [
            'CMP-ALU' => [
                'quantity' => 2,
                'histories' => [
                    ['currency' => 'USD', 'rate' => 16600, 'price' => 7.2],
                    ['currency' => 'USD', 'rate' => 16628, 'price' => 7.1],
                    ['currency' => 'USD', 'rate' => 16660, 'price' => 7.3],
                ],
            ],
            'CMP-PAINT' => [
                'quantity' => 1,
                'histories' => [
                    ['currency' => 'IDR', 'rate' => 1, 'price' => 50000],
                    ['currency' => 'IDR', 'rate' => 1, 'price' => 52000],
                    ['currency' => 'IDR', 'rate' => 1, 'price' => 48000],
                ],
            ],
            'CMP-GRIP' => [
                'quantity' => 1,
                'histories' => [
                    ['currency' => 'IDR', 'rate' => 1, 'price' => 35000],
                    ['currency' => 'IDR', 'rate' => 1, 'price' => 36500],
                    ['currency' => 'IDR', 'rate' => 1, 'price' => 34000],
                ],
            ],
        ];

        $total = 0.0;

        foreach ($components as $component) {
            $normalizedPrices = [];

            foreach ($component['histories'] as $history) {
                $unitPriceIdr = $history['currency'] === 'USD'
                    ? $history['price'] * $history['rate']
                    : $history['price'];

                if ($history['currency'] === 'USD') {
                    $normalizedPrices[] = $unitPriceIdr * ($currentRate / $history['rate']);
                } else {
                    $normalizedPrices[] = $unitPriceIdr;
                }
            }

            $average = array_sum($normalizedPrices) / count($normalizedPrices);

            $total += $average * $component['quantity'];
        }

        return $total;
    }
}
