<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Skin;
use App\Models\SkinPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncSkinPricesCommand extends Command
{
    protected $signature = 'skins:sync-prices';

    protected $description = 'Fetch and update skin prices from market API';

    public function handle(): int
    {
        if (! config('skyforge.price_sync.enabled')) {
            $this->info('Price sync is disabled.');

            return self::SUCCESS;
        }

        $url = config('skyforge.price_sync.source_url');
        $threshold = (float) config('skyforge.price_sync.change_threshold');

        $this->info('Fetching prices from market API...');

        $response = Http::timeout(30)->connectTimeout(10)->retry(3, 1000, throw: false)->get($url);

        if (! $response->successful()) {
            $this->error('Failed to fetch prices: HTTP '.$response->status());

            return self::FAILURE;
        }

        $prices = $response->json();

        if (! is_array($prices)) {
            $this->error('Invalid price data format.');

            return self::FAILURE;
        }

        $this->info('Processing '.count($prices).' prices...');

        $updated = 0;
        $logged = 0;

        Skin::query()->chunkById(500, function ($skins) use ($prices, $threshold, &$updated, &$logged) {
            foreach ($skins as $skin) {
                $marketData = $prices[$skin->market_hash_name] ?? null;

                if (! $marketData || ! isset($marketData['price'])) {
                    continue;
                }

                $newPrice = (int) round((float) $marketData['price'] * 100);

                if ($newPrice <= 0) {
                    continue;
                }

                $oldPrice = $skin->price;
                $changePercent = $oldPrice > 0
                    ? abs($newPrice - $oldPrice) / $oldPrice * 100
                    : 100;

                if ($changePercent >= $threshold) {
                    SkinPrice::create([
                        'skin_id' => $skin->id,
                        'price' => $newPrice,
                        'fetched_at' => now(),
                    ]);
                    $logged++;
                }

                $skin->update([
                    'price' => $newPrice,
                    'price_updated_at' => now(),
                ]);
                $updated++;
            }
        });

        $this->info("Updated {$updated} prices, logged {$logged} significant changes.");

        return self::SUCCESS;
    }
}
