<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestExchangeRateAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:exchange-rate-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test ExchangeRate-API connection and response format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing ExchangeRate-API...');
        $this->info('URL: https://api.exchangerate-api.com/v4/latest/USD');
        $this->newLine();

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)->get('https://api.exchangerate-api.com/v4/latest/USD');
            
            $this->info('Status: ' . $response->status());
            $this->info('Successful: ' . ($response->successful() ? 'YES' : 'NO'));
            $this->newLine();
            
            $data = $response->json();
            
            $this->info('Response Type: ' . gettype($data));
            
            if (is_array($data)) {
                $this->info('Keys: ' . implode(', ', array_keys($data)));
                $this->newLine();
                
                if (isset($data['rates'])) {
                    $this->info('Rates found: YES');
                    $this->info('Total rates: ' . count($data['rates']));
                    
                    if (isset($data['rates']['IDR'])) {
                        $this->info('IDR rate found: YES');
                        $this->info('IDR rate value: ' . $data['rates']['IDR']);
                        $this->newLine();
                        $this->info('✅ SUCCESS! API is working and IDR rate is available.');
                    } else {
                        $this->error('IDR rate found: NO');
                        $this->warn('Available currencies (first 20): ' . implode(', ', array_slice(array_keys($data['rates']), 0, 20)));
                    }
                } else {
                    $this->error('Rates key NOT found');
                }
                
                $this->newLine();
                $this->info('Full response structure:');
                $this->line(json_encode([
                    'base' => $data['base'] ?? 'N/A',
                    'date' => $data['date'] ?? 'N/A',
                    'rates_count' => isset($data['rates']) ? count($data['rates']) : 0,
                    'has_idr' => isset($data['rates']['IDR']),
                    'idr_value' => $data['rates']['IDR'] ?? 'N/A',
                ], JSON_PRETTY_PRINT));
            } else {
                $this->error('Response is not an array');
                $this->warn('Response body (first 500 chars):');
                $this->line(substr($response->body(), 0, 500));
            }
        } catch (\Exception $e) {
            $this->error('ERROR: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile());
            $this->error('Line: ' . $e->getLine());
        }
    }
}
