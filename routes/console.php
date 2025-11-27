<?php

use App\Jobs\SyncExchangeRatesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule exchange rate sync daily at 8 AM (skip weekends)
Schedule::call(function () {
    SyncExchangeRatesJob::dispatch(Carbon\Carbon::today(), false);
})->dailyAt('08:00')->weekdays();
