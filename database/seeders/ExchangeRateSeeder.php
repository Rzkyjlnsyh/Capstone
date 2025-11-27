<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = [
            [
                'rate_date' => '2025-11-03',
                'rate_value' => 16660,
            ],
            [
                'rate_date' => '2025-11-04',
                'rate_value' => 16664,
            ],
        ];

        foreach ($rates as $rate) {
            ExchangeRate::query()->updateOrCreate(
                [
                    'rate_date' => $rate['rate_date'],
                    'base_currency' => 'USD',
                    'quote_currency' => 'IDR',
                ],
                [
                    'rate_value' => $rate['rate_value'],
                    'source' => 'JISDOR',
                    'fetched_at' => now(),
                ]
            );
        }
    }
}
