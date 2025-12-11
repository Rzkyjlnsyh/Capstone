<?php

namespace App\Http\Controllers;

use App\Jobs\SyncExchangeRatesJob;
use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ExchangeRate::query()->orderBy('rate_date', 'desc');

        if ($request->has('date_from')) {
            $query->where('rate_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('rate_date', '<=', $request->date_to);
        }

        if ($request->has('base_currency')) {
            $query->where('base_currency', $request->base_currency);
        }

        $perPage = $request->get('per_page', 15);
        $rates = $query->paginate($perPage);

        return response()->json($rates);
    }

    public function show(ExchangeRate $exchangeRate): JsonResponse
    {
        return response()->json($exchangeRate);
    }

    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['nullable', 'date'],
            'date_range' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}-\d{2}:\d{4}-\d{2}-\d{2}$/'],
            'use_mock' => ['nullable', 'boolean'],
            'sync_now' => ['nullable', 'boolean'], // Option untuk sync langsung tanpa queue
        ]);

        // Default: coba real API dulu, baru fallback ke mock jika gagal
        // Hanya pakai mock otomatis jika explicitly set use_mock=true
        $useMock = $data['use_mock'] ?? false;
        $syncNow = $data['sync_now'] ?? true; // Default sync langsung untuk development

        // Jika sync_now = true, eksekusi langsung tanpa queue
        if ($syncNow || config('queue.default') === 'sync') {
            $service = app(\App\Services\BiExchangeRateService::class);
            
            if (isset($data['date_range'])) {
                [$start, $end] = explode(':', $data['date_range']);
                $startDate = Carbon::parse($start);
                $endDate = Carbon::parse($end);

                $current = $startDate->copy();
                $count = 0;
                $synced = [];

                while ($current->lte($endDate)) {
                    if (! $current->isWeekend()) {
                        try {
                            // Coba ambil data real dulu, baru fallback ke mock jika gagal
                            $rateData = null;
                            if (!$useMock) {
                                $rateData = $service->fetchRateForDate($current);
                            }
                            
                            // Jika gagal atau use_mock=true, pakai mock
                            if (!$rateData) {
                                $rateData = $service->getMockRateForDate($current);
                            }
                            
                            if ($rateData) {
                                $rate = $service->saveRate($rateData);
                                $synced[] = $rate;
                                $count++;
                            }
                        } catch (\Exception $e) {
                            \Log::error('Sync rate failed', ['date' => $current->toDateString(), 'error' => $e->getMessage()]);
                        }
                    }
                    $current->addDay();
                }

                return response()->json([
                    'message' => "Berhasil menyinkronkan {$count} kurs",
                    'synced_count' => $count,
                    'date_range' => [
                        'start' => $startDate->toDateString(),
                        'end' => $endDate->toDateString(),
                    ],
                ], 200);
            }

            $date = isset($data['date'])
                ? Carbon::parse($data['date'])
                : Carbon::today();

            try {
                // Coba ambil data real dulu, baru fallback ke mock jika gagal
                $rateData = null;
                if (!$useMock) {
                    \Log::info('ExchangeRateController: Attempting to fetch real API data', [
                        'date' => $date->toDateString(),
                        'use_mock' => false,
                    ]);
                    $rateData = $service->fetchRateForDate($date);
                    
                    if ($rateData) {
                        \Log::info('ExchangeRateController: Real API data fetched successfully', [
                            'date' => $date->toDateString(),
                            'rate' => $rateData['rate_value'],
                            'source' => $rateData['source'],
                        ]);
                    } else {
                        \Log::warning('ExchangeRateController: Real API fetch returned null', [
                            'date' => $date->toDateString(),
                        ]);
                    }
                } else {
                    \Log::info('ExchangeRateController: use_mock=true, skipping real API', [
                        'date' => $date->toDateString(),
                    ]);
                }
                
                // Jika gagal atau use_mock=true, pakai mock
                if (!$rateData) {
                    \Log::warning('ExchangeRateController: Falling back to mock data', [
                        'date' => $date->toDateString(),
                        'use_mock' => $useMock,
                        'env' => config('app.env'),
                    ]);
                    $rateData = $service->getMockRateForDate($date);
                    
                    \Log::info('ExchangeRateController: Mock data generated', [
                        'date' => $date->toDateString(),
                        'rate' => $rateData['rate_value'],
                        'source' => $rateData['source'],
                    ]);
                }

                if ($rateData) {
                    $rate = $service->saveRate($rateData);
                    
                    \Log::info('ExchangeRateController: Rate saved to database', [
                        'date' => $date->toDateString(),
                        'rate_id' => $rate->id,
                        'rate_value' => $rate->rate_value,
                        'source' => $rate->source,
                    ]);
                    
                    return response()->json([
                        'message' => 'Kurs berhasil disinkronkan',
                        'date' => $date->toDateString(),
                        'rate' => $rate,
                        'is_mock' => $rate->source === 'JISDOR_MOCK',
                    ], 200);
                }

                return response()->json([
                    'message' => 'Gagal mengambil data kurs',
                ], 422);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Gagal sinkronisasi kurs',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        // Fallback ke queue jika sync_now = false
        if (isset($data['date_range'])) {
            [$start, $end] = explode(':', $data['date_range']);
            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);

            $current = $startDate->copy();
            $count = 0;

            while ($current->lte($endDate)) {
                if (! $current->isWeekend()) {
                    SyncExchangeRatesJob::dispatch($current, $useMock);
                    $count++;
                }
                $current->addDay();
            }

            return response()->json([
                'message' => "Dispatched {$count} job(s) untuk sinkronisasi",
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
            ], 202);
        }

        $date = isset($data['date'])
            ? Carbon::parse($data['date'])
            : Carbon::today();

        SyncExchangeRatesJob::dispatch($date, $useMock);

        return response()->json([
            'message' => 'Job sinkronisasi kurs telah di-dispatch',
            'date' => $date->toDateString(),
        ], 202);
    }

    public function latest(): JsonResponse
    {
        $latest = ExchangeRate::query()
            ->where('base_currency', 'USD')
            ->where('quote_currency', 'IDR')
            ->orderBy('rate_date', 'desc')
            ->first();

        if (! $latest) {
            return response()->json([
                'message' => 'Tidak ada kurs tersedia',
            ], 404);
        }

        return response()->json($latest);
    }
}
