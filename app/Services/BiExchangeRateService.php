<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiExchangeRateService
{
    // API alternatif yang lebih reliable
    private const EXCHANGERATE_API_URL = 'https://api.exchangerate-api.com/v4';
    private const FIXER_API_URL = 'https://api.fixer.io';
    private const CURRENCYAPI_URL = 'https://api.currencyapi.com/v3';
    private const EXCHANGERATE_HOST_URL = 'https://api.exchangerate.host'; // Alternative API (no SSL issues)
    
    // BI API (backup jika API lain gagal)
    private const BI_API_URL = 'https://www.bi.go.id/biwebservice/wskursbi.asmx';

    /**
     * Fetch USD->IDR rate from reliable APIs (prioritized order)
     * 1. ExchangeRate-API (free, no API key, reliable)
     * 2. Fixer.io (if API key available)
     * 3. CurrencyAPI.net (if API key available)
     * 4. BI API (backup)
     */
    public function fetchRateForDate(Carbon $date): ?array
    {
        Log::info('Starting exchange rate fetch', [
            'date' => $date->toDateString(),
        ]);

        // Coba API 1: ExchangeRate-API (Primary - Free, No API Key)
        Log::info('Trying ExchangeRate-API...', ['date' => $date->toDateString()]);
        $rateData = $this->fetchFromExchangeRateAPI($date);
        if ($rateData) {
            Log::info('ExchangeRate-API SUCCESS', [
                'date' => $date->toDateString(),
                'rate' => $rateData['rate_value'],
                'source' => $rateData['source'],
            ]);
            return $rateData;
        }
        Log::warning('ExchangeRate-API failed, trying ExchangeRate-Host...', ['date' => $date->toDateString()]);
        
        // Coba API 1b: ExchangeRate-Host (Alternative - Free, No API Key, No SSL issues)
        $rateData = $this->fetchFromExchangeRateHost($date);
        if ($rateData) {
            Log::info('ExchangeRate-Host SUCCESS', [
                'date' => $date->toDateString(),
                'rate' => $rateData['rate_value'],
                'source' => $rateData['source'],
            ]);
            return $rateData;
        }
        Log::warning('ExchangeRate-Host failed, trying next API...', ['date' => $date->toDateString()]);

        // Coba API 2: Fixer.io (Secondary - butuh API key)
        $fixerApiKey = config('services.fixer.api_key');
        if ($fixerApiKey) {
            Log::info('Trying Fixer.io...', ['date' => $date->toDateString()]);
            $rateData = $this->fetchFromFixerIO($date, $fixerApiKey);
            if ($rateData) {
                Log::info('Fixer.io SUCCESS', [
                    'date' => $date->toDateString(),
                    'rate' => $rateData['rate_value'],
                    'source' => $rateData['source'],
                ]);
                return $rateData;
            }
            Log::warning('Fixer.io failed', ['date' => $date->toDateString()]);
        } else {
            Log::info('Fixer.io skipped (no API key)', ['date' => $date->toDateString()]);
        }

        // Coba API 3: CurrencyAPI.net (Tertiary - butuh API key)
        $currencyApiKey = config('services.currencyapi.api_key');
        if ($currencyApiKey) {
            Log::info('Trying CurrencyAPI.net...', ['date' => $date->toDateString()]);
            $rateData = $this->fetchFromCurrencyAPI($date, $currencyApiKey);
            if ($rateData) {
                Log::info('CurrencyAPI.net SUCCESS', [
                    'date' => $date->toDateString(),
                    'rate' => $rateData['rate_value'],
                    'source' => $rateData['source'],
                ]);
                return $rateData;
            }
            Log::warning('CurrencyAPI.net failed', ['date' => $date->toDateString()]);
        } else {
            Log::info('CurrencyAPI.net skipped (no API key)', ['date' => $date->toDateString()]);
        }

        // Coba API 4: BI API (Backup - original)
        Log::info('Trying BI API (backup)...', ['date' => $date->toDateString()]);
        $rateData = $this->fetchFromBIAPI($date);
        if ($rateData) {
            Log::info('BI API SUCCESS', [
                'date' => $date->toDateString(),
                'rate' => $rateData['rate_value'],
                'source' => $rateData['source'],
            ]);
            return $rateData;
        }
        Log::warning('BI API failed', ['date' => $date->toDateString()]);

        Log::error('ALL exchange rate APIs FAILED - returning null', [
            'date' => $date->toDateString(),
        ]);

        return null;
    }

    /**
     * Fetch from ExchangeRate-API (Primary - Free, No API Key Required)
     * Docs: https://www.exchangerate-api.com/docs/standard-requests
     * 
     * Endpoints:
     * - Latest: https://api.exchangerate-api.com/v4/latest/USD
     * - Historical: https://api.exchangerate-api.com/v4/history/USD/2024-01-01
     */
    private function fetchFromExchangeRateAPI(Carbon $date): ?array
    {
        try {
            $dateStr = $date->format('Y-m-d');
            $isToday = $date->isToday() || $date->isFuture();
            
            // Untuk hari ini atau masa depan, gunakan latest endpoint
            // Untuk historical, gunakan history endpoint
            if ($isToday) {
                $url = self::EXCHANGERATE_API_URL . '/latest/USD';
            } else {
                // Historical data - format: /history/{base}/{date}
                $url = self::EXCHANGERATE_API_URL . '/history/USD/' . $dateStr;
            }

            Log::info('ExchangeRate-API: Making request', [
                'url' => $url,
                'date' => $dateStr,
            ]);

            // Skip SSL verification in development if needed
            $httpClient = Http::timeout(15);
            if (config('app.env') === 'local' || config('app.debug')) {
                $httpClient = $httpClient->withoutVerifying();
            }
            
            $response = $httpClient->get($url);

            Log::info('ExchangeRate-API: Response received', [
                'date' => $dateStr,
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if (!$response->successful()) {
                Log::error('ExchangeRate-API: HTTP request failed', [
                    'date' => $dateStr,
                    'status' => $response->status(),
                    'body_preview' => substr($response->body(), 0, 500),
                ]);
                return null;
            }

            $data = $response->json();
            
            Log::info('ExchangeRate-API: Response parsed', [
                'date' => $dateStr,
                'has_data' => !empty($data),
                'is_array' => is_array($data),
                'keys' => is_array($data) ? array_keys($data) : [],
            ]);
            
            if (!$data || !is_array($data)) {
                Log::error('ExchangeRate-API: Invalid response format', [
                    'date' => $dateStr,
                    'response_type' => gettype($data),
                    'body_preview' => substr($response->body(), 0, 500),
                ]);
                return null;
            }
            
            // Check for error in response
            if (isset($data['error'])) {
                Log::error('ExchangeRate-API: API returned error', [
                    'date' => $dateStr,
                    'error' => $data['error'],
                ]);
                return null;
            }
            
            if (!isset($data['rates'])) {
                Log::error('ExchangeRate-API: "rates" key not found in response', [
                    'date' => $dateStr,
                    'response_keys' => array_keys($data),
                ]);
                return null;
            }
            
            if (!isset($data['rates']['IDR'])) {
                Log::error('ExchangeRate-API: IDR rate not found', [
                    'date' => $dateStr,
                    'available_rates' => array_keys($data['rates'] ?? []),
                    'rates_count' => count($data['rates'] ?? []),
                ]);
                return null;
            }

            $rateValue = (float) $data['rates']['IDR'];

            // Validasi nilai kurs masuk akal (USD/IDR biasanya antara 14000-20000)
            if ($rateValue <= 0 || $rateValue < 10000 || $rateValue > 30000) {
                Log::warning('ExchangeRate-API: Invalid rate value (out of range)', [
                    'date' => $dateStr,
                    'rate' => $rateValue,
                ]);
                return null;
            }

            // Update rate_date dari response jika ada (untuk historical)
            $responseDate = $dateStr;
            if (isset($data['date'])) {
                $responseDate = $data['date'];
            }

            Log::info('ExchangeRate-API: Successfully fetched rate', [
                'date' => $responseDate,
                'rate' => $rateValue,
            ]);

            return [
                'rate_date' => $responseDate,
                'base_currency' => 'USD',
                'quote_currency' => 'IDR',
                'rate_value' => $rateValue,
                'source' => 'JISDOR', // Tetap pakai JISDOR untuk konsistensi
                'raw_payload' => json_encode($data),
            ];
        } catch (\Exception $e) {
            Log::error('ExchangeRate-API: Exception occurred', [
                'date' => $date->toDateString(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Fetch from ExchangeRate-Host (Alternative - Free, No API Key, No SSL issues)
     * Docs: https://exchangerate.host/
     * This API is more reliable for local development
     */
    private function fetchFromExchangeRateHost(Carbon $date): ?array
    {
        try {
            $dateStr = $date->format('Y-m-d');
            $isToday = $date->isToday() || $date->isFuture();
            
            // ExchangeRate-Host API
            // Latest: https://api.exchangerate.host/latest?base=USD
            // Historical: https://api.exchangerate.host/{date}?base=USD
            if ($isToday) {
                $url = self::EXCHANGERATE_HOST_URL . '/latest';
            } else {
                $url = self::EXCHANGERATE_HOST_URL . '/' . $dateStr;
            }
            
            $params = [
                'base' => 'USD',
                'symbols' => 'IDR',
            ];

            Log::info('ExchangeRate-Host: Making request', [
                'url' => $url,
                'params' => $params,
                'date' => $dateStr,
            ]);

            // Skip SSL verification in development if needed
            $httpClient = Http::timeout(15);
            if (config('app.env') === 'local' || config('app.debug')) {
                $httpClient = $httpClient->withoutVerifying();
            }
            
            $response = $httpClient->get($url, $params);

            Log::info('ExchangeRate-Host: Response received', [
                'date' => $dateStr,
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if (!$response->successful()) {
                Log::error('ExchangeRate-Host: HTTP request failed', [
                    'date' => $dateStr,
                    'status' => $response->status(),
                    'body_preview' => substr($response->body(), 0, 500),
                ]);
                return null;
            }

            $data = $response->json();
            
            Log::info('ExchangeRate-Host: Response parsed', [
                'date' => $dateStr,
                'has_data' => !empty($data),
                'is_array' => is_array($data),
                'keys' => is_array($data) ? array_keys($data) : [],
            ]);
            
            if (!$data || !is_array($data)) {
                Log::error('ExchangeRate-Host: Invalid response format', [
                    'date' => $dateStr,
                    'response_type' => gettype($data),
                ]);
                return null;
            }
            
            // Check for error in response
            if (isset($data['error'])) {
                Log::error('ExchangeRate-Host: API returned error', [
                    'date' => $dateStr,
                    'error' => $data['error'],
                ]);
                return null;
            }
            
            if (!isset($data['rates']) || !isset($data['rates']['IDR'])) {
                Log::error('ExchangeRate-Host: IDR rate not found', [
                    'date' => $dateStr,
                    'has_rates' => isset($data['rates']),
                    'available_rates' => isset($data['rates']) ? array_keys($data['rates']) : [],
                ]);
                return null;
            }

            $rateValue = (float) $data['rates']['IDR'];

            // Validasi nilai kurs masuk akal
            if ($rateValue <= 0 || $rateValue < 10000 || $rateValue > 30000) {
                Log::error('ExchangeRate-Host: Invalid rate value (out of range)', [
                    'date' => $dateStr,
                    'rate' => $rateValue,
                ]);
                return null;
            }

            // Get date from response
            $responseDate = $dateStr;
            if (isset($data['date'])) {
                $responseDate = $data['date'];
            }

            Log::info('ExchangeRate-Host: Successfully fetched rate', [
                'date' => $responseDate,
                'rate' => $rateValue,
            ]);

            return [
                'rate_date' => $responseDate,
                'base_currency' => 'USD',
                'quote_currency' => 'IDR',
                'rate_value' => $rateValue,
                'source' => 'JISDOR',
                'raw_payload' => json_encode($data),
            ];
        } catch (\Exception $e) {
            Log::error('ExchangeRate-Host: Exception occurred', [
                'date' => $date->toDateString(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return null;
        }
    }

    /**
     * Fetch from Fixer.io (Secondary - Requires API Key)
     * Docs: https://fixer.io/documentation
     */
    private function fetchFromFixerIO(Carbon $date, string $apiKey): ?array
    {
        try {
            $dateStr = $date->format('Y-m-d');
            $url = self::FIXER_API_URL . '/' . $dateStr;
            
            $response = Http::timeout(10)->get($url, [
                'access_key' => $apiKey,
                'base' => 'USD',
                'symbols' => 'IDR',
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            
            if (!isset($data['success']) || !$data['success']) {
                return null;
            }

            if (!isset($data['rates']['IDR'])) {
                return null;
            }

            $rateValue = (float) $data['rates']['IDR'];

            if ($rateValue <= 0 || $rateValue < 10000 || $rateValue > 30000) {
                return null;
            }

            return [
                'rate_date' => $dateStr,
                'base_currency' => 'USD',
                'quote_currency' => 'IDR',
                'rate_value' => $rateValue,
                'source' => 'JISDOR',
                'raw_payload' => json_encode($data),
            ];
        } catch (\Exception $e) {
            Log::debug('Fixer.io exception', [
                'date' => $date->toDateString(),
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Fetch from CurrencyAPI.net (Tertiary - Requires API Key)
     * Docs: https://currencyapi.com/docs
     */
    private function fetchFromCurrencyAPI(Carbon $date, string $apiKey): ?array
    {
        try {
            $dateStr = $date->format('Y-m-d');
            $url = self::CURRENCYAPI_URL . '/historical';
            
            $response = Http::timeout(10)->get($url, [
                'apikey' => $apiKey,
                'base_currency' => 'USD',
                'date' => $dateStr,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            
            if (!isset($data['data']['IDR']['value'])) {
                return null;
            }

            $rateValue = (float) $data['data']['IDR']['value'];

            if ($rateValue <= 0 || $rateValue < 10000 || $rateValue > 30000) {
                return null;
            }

            return [
                'rate_date' => $dateStr,
                'base_currency' => 'USD',
                'quote_currency' => 'IDR',
                'rate_value' => $rateValue,
                'source' => 'JISDOR',
                'raw_payload' => json_encode($data),
            ];
        } catch (\Exception $e) {
            Log::debug('CurrencyAPI.net exception', [
                'date' => $date->toDateString(),
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Fetch from BI API (Backup - Original implementation)
     */
    private function fetchFromBIAPI(Carbon $date): ?array
    {
        try {
            $endpoints = [
                '/GetKursJISDOR',
                '/GetKurs',
            ];
            
            foreach ($endpoints as $endpoint) {
                try {
                    $response = Http::timeout(10)->get(self::BI_API_URL . $endpoint, [
                        'tanggal' => $date->format('Y-m-d'),
                        'matauang' => 'USD',
                    ]);

                    if ($response->successful()) {
                        $xml = $response->body();
                        $parsed = $this->parseXmlResponse($xml, $date);
                        
                        if ($parsed) {
                            return $parsed;
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            return null;
        } catch (\Exception $e) {
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
     * Menggunakan hash tanggal untuk konsistensi (nilai sama untuk tanggal yang sama)
     */
    public function getMockRateForDate(Carbon $date): array
    {
        // Generate konsisten berdasarkan tanggal (deterministic)
        // Setiap tanggal akan selalu menghasilkan nilai yang sama
        $baseRate = 16600;
        $dateHash = crc32($date->toDateString()); // Hash tanggal untuk konsistensi
        $variation = ($dateHash % 200) - 100; // Range -100 sampai +100, tapi konsisten per tanggal
        $mockRate = $baseRate + $variation;

        return [
            'rate_date' => $date->toDateString(),
            'base_currency' => 'USD',
            'quote_currency' => 'IDR',
            'rate_value' => $mockRate,
            'source' => 'JISDOR_MOCK',
            'raw_payload' => '<Mock>Rate for testing - Generated from date hash</Mock>',
        ];
    }
}

