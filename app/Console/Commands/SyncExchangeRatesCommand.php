<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SyncExchangeRatesCommand extends Command
{
    protected $signature = 'skyforge:sync-rates';

    protected $description = 'Fetch fiat (CBR) and crypto exchange rates, cache them';

    public function handle(): int
    {
        $this->info('Fetching exchange rates...');

        try {
            $rates = $this->fetchFiatRates();
            $cryptoRates = $this->fetchCryptoRates($rates['USD']);

            $all = array_merge($rates, $cryptoRates);

            Cache::put('exchange_rates', $all, now()->addMinutes(60));
            Cache::put('exchange_rates_updated_at', now()->toISOString(), now()->addMinutes(60));

            $this->info('Exchange rates cached:');

            foreach ($all as $currency => $rate) {
                $this->line("  {$currency}: {$rate} ₽");
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to fetch rates: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /** @return array<string, float> */
    private function fetchFiatRates(): array
    {
        $response = Http::timeout(10)->get('https://www.cbr-xml-daily.ru/daily_json.js');

        if (! $response->ok()) {
            throw new \RuntimeException('CBR API returned status: '.$response->status());
        }

        $valutes = $response->json('Valute') ?? [];

        return [
            'RUB' => 1.0,
            'USD' => $valutes['USD']['Value'] ?? 96.0,
            'EUR' => $valutes['EUR']['Value'] ?? 105.0,
            'UAH' => $valutes['UAH']['Value'] ?? 2.2,
            'KZT' => ($valutes['KZT']['Value'] ?? 19.0) / ($valutes['KZT']['Nominal'] ?? 100),
            'BYN' => $valutes['BYN']['Value'] ?? 29.0,
        ];
    }

    /** @return array<string, float> */
    private function fetchCryptoRates(float $usdToRub): array
    {
        // CoinGecko free API — no key required
        $response = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
            'ids' => 'tether,toncoin,tron',
            'vs_currencies' => 'usd',
        ]);

        if (! $response->ok()) {
            $this->warn('CoinGecko API failed, using fallback crypto rates');

            return [
                'USDT' => $usdToRub,
                'TON' => $usdToRub * 3.5,
                'TRX' => $usdToRub * 0.25,
            ];
        }

        $data = $response->json();

        $usdtUsd = $data['tether']['usd'] ?? 1.0;
        $tonUsd = $data['toncoin']['usd'] ?? 3.5;
        $trxUsd = $data['tron']['usd'] ?? 0.25;

        return [
            'USDT' => round($usdtUsd * $usdToRub, 4),
            'TON' => round($tonUsd * $usdToRub, 4),
            'TRX' => round($trxUsd * $usdToRub, 4),
        ];
    }
}
