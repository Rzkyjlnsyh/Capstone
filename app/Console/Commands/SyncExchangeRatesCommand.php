<?php

namespace App\Console\Commands;

use App\Jobs\SyncExchangeRatesJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncExchangeRatesCommand extends Command
{
    protected $signature = 'rates:sync {--date= : Tanggal kurs (Y-m-d, default: hari ini)} {--range= : Range tanggal (start:end)} {--mock : Gunakan data mock}';

    protected $description = 'Sinkronisasi kurs JISDOR dari Bank Indonesia';

    public function handle(): int
    {
        $useMock = $this->option('mock') || config('app.env') === 'local';

        if ($range = $this->option('range')) {
            [$start, $end] = explode(':', $range);
            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);

            $this->info("Menyinkronkan kurs dari {$startDate->format('Y-m-d')} hingga {$endDate->format('Y-m-d')}...");

            $current = $startDate->copy();
            $count = 0;

            while ($current->lte($endDate)) {
                if (! $current->isWeekend()) {
                    SyncExchangeRatesJob::dispatch($current, $useMock);
                    $count++;
                }
                $current->addDay();
            }

            $this->info("Dispatched {$count} job(s) untuk sinkronisasi.");
            return Command::SUCCESS;
        }

        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::today();

        $this->info("Menyinkronkan kurs untuk tanggal: {$date->format('Y-m-d')}...");

        SyncExchangeRatesJob::dispatch($date, $useMock);

        $this->info('Job dispatched. Cek log untuk hasilnya.');

        return Command::SUCCESS;
    }
}
