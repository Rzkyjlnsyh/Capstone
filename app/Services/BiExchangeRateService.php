<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiExchangeRateService
{
    private const BI_API_URL = 'https://www.bi.go.id/biwebservice/wskursbi.asmx';

    /**
     * Fetch JISDOR USD->IDR rate from BI API for a specific date
     */
    public function fetchRateForDate(Carbon $date): ?array
    {
        try {
            $response = Http::timeout(10)->get(self::BI_API_URL . '/GetKurs', [
                'tanggal' => $date->format('Y-m-d'),
                'matauang' => 'USD',
            ]);

            if (! $response->successful()) {
                Log::warning('BI API request failed', [
                    'date' => $date->toDateString(),
                    'status' => $response->status(),
                ]);

                return null;
            }

            $xml = $response->body();
            $parsed = $this->parseXmlResponse($xml, $date);

            return $parsed;
        } catch (\Exception $e) {
            Log::error('BI API exception', [
                'date' => $date->toDateString(),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Fetch rates for a date range
     */
    public function fetchRatesForRange(Carbon $startDate, Carbon $endDate): array
    {
        $results = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            if ($current->isWeekend()) {
                $current->addDay();
                continue;
            }

            $rate = $this->fetchRateForDate($current);
            if ($rate) {
                $results[] = $rate;
            }

            $current->addDay();
        }

        return $results;
    }

    /**
     * Parse XML response from BI API
     * Expected format: <Kurs>...</Kurs> with <Nilai> containing rate value
     */
    private function parseXmlResponse(string $xml, Carbon $date): ?array
    {
        try {
            $xmlObj = simplexml_load_string($xml);
            if (! $xmlObj) {
                return null;
            }

            // BI API returns structure like:
            // <Kurs>
            //   <Tanggal>2025-11-04</Tanggal>
            //   <Nilai>16664</Nilai>
            //   ...
            // </Kurs>
            $nilai = (string) ($xmlObj->Nilai ?? $xmlObj->kurs->Nilai ?? null);

            if (empty($nilai) || ! is_numeric($nilai)) {
                Log::warning('BI API: Invalid rate value', ['xml' => $xml]);
                return null;
            }

            $rateValue = (float) $nilai;

            return [
                'rate_date' => $date->toDateString(),
                'base_currency' => 'USD',
                'quote_currency' => 'IDR',
                'rate_value' => $rateValue,
                'source' => 'JISDOR',
                'raw_payload' => $xml,
            ];
        } catch (\Exception $e) {
            Log::error('BI API XML parse error', [
                'error' => $e->getMessage(),
                'xml_preview' => substr($xml, 0, 200),
            ]);

            return null;
        }
    }

    /**
     * Save fetched rate to database
     */
    public function saveRate(array $rateData): ExchangeRate
    {
        return ExchangeRate::updateOrCreate(
            [
                'rate_date' => $rateData['rate_date'],
                'base_currency' => $rateData['base_currency'],
                'quote_currency' => $rateData['quote_currency'],
            ],
            [
                'rate_value' => $rateData['rate_value'],
                'source' => $rateData['source'],
                'raw_payload' => $rateData['raw_payload'] ?? null,
                'fetched_at' => now(),
            ]
        );
    }

    /**
     * Mock data for development/testing when BI API is unavailable
     */
    public function getMockRateForDate(Carbon $date): array
    {
        // Generate a realistic mock rate around 16,600-16,700 range
        $baseRate = 16600;
        $variation = rand(-100, 100);
        $mockRate = $baseRate + $variation;

        return [
            'rate_date' => $date->toDateString(),
            'base_currency' => 'USD',
            'quote_currency' => 'IDR',
            'rate_value' => $mockRate,
            'source' => 'JISDOR_MOCK',
            'raw_payload' => '<Mock>Rate for testing</Mock>',
        ];
    }
}

