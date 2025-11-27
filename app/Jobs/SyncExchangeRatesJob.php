<?php

namespace App\Jobs;

use App\Services\BiExchangeRateService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncExchangeRatesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?Carbon $date = null,
        public bool $useMock = false
    ) {
        $this->date = $date ?? Carbon::today();
    }

    public function handle(BiExchangeRateService $service): void
    {
        $targetDate = $this->date ?? Carbon::today();

        if ($targetDate->isWeekend()) {
            Log::info('Skipping weekend date for exchange rate sync', ['date' => $targetDate->toDateString()]);
            return;
        }

        try {
            if ($this->useMock || config('app.env') === 'local') {
                $rateData = $service->getMockRateForDate($targetDate);
            } else {
                $rateData = $service->fetchRateForDate($targetDate);
                if (! $rateData) {
                    Log::warning('Failed to fetch rate from BI API, using mock', ['date' => $targetDate->toDateString()]);
                    $rateData = $service->getMockRateForDate($targetDate);
                }
            }

            $rate = $service->saveRate($rateData);

            Log::info('Exchange rate synced', [
                'date' => $rate->rate_date,
                'rate' => $rate->rate_value,
            ]);
        } catch (\Exception $e) {
            Log::error('Exchange rate sync failed', [
                'date' => $targetDate->toDateString(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
