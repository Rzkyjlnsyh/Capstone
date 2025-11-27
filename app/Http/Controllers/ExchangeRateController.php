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
        ]);

        $useMock = $data['use_mock'] ?? (config('app.env') === 'local');

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
