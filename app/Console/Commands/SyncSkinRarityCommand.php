<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Skin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncSkinRarityCommand extends Command
{
    protected $signature = 'skins:sync-rarity';

    protected $description = 'Fetch real rarity from CS2 game data (ByMykel/CSGO-API) and update skins';

    private const SOURCE_URL = 'https://raw.githubusercontent.com/ByMykel/CSGO-API/main/public/api/en/skins.json';

    private const RARITY_MAP = [
        'Consumer Grade' => ['consumer', '#878F9D'],
        'Industrial Grade' => ['industrial', '#356C9D'],
        'Mil-Spec Grade' => ['mil_spec', '#2D4FFA'],
        'Restricted' => ['restricted', '#50318D'],
        'Classified' => ['classified', '#A64BB5'],
        'Covert' => ['covert', '#EA2F2F'],
        'Extraordinary' => ['covert', '#EA2F2F'],
        'Contraband' => ['contraband', '#D4AF37'],
    ];

    private const KNIFE_RARITY = ['extraordinary', '#D4AF37'];

    public function handle(): int
    {
        $this->info('Fetching CS2 skin rarity data...');

        $response = Http::timeout(30)->get(self::SOURCE_URL);

        if (! $response->successful()) {
            $this->error('Failed: HTTP '.$response->status());

            return self::FAILURE;
        }

        $items = $response->json();

        if (! is_array($items)) {
            $this->error('Invalid response format.');

            return self::FAILURE;
        }

        $this->info('Loaded '.count($items).' skin definitions.');

        // Build lookup: normalized base name → [rarity, color]
        $lookup = [];

        foreach ($items as $item) {
            $name = $item['name'] ?? '';
            $rarityData = $item['rarity'] ?? null;

            if (! $name || ! is_array($rarityData)) {
                continue;
            }

            $rarityName = $rarityData['name'] ?? '';
            $mapping = self::RARITY_MAP[$rarityName] ?? null;

            if (! $mapping) {
                continue;
            }

            // Ножи/перчатки → золото
            if (str_starts_with($name, '★')) {
                $mapping = self::KNIFE_RARITY;
            }

            $lookup[$name] = $mapping;
        }

        $this->info('Built lookup with '.count($lookup).' entries.');

        $updated = 0;
        $bar = $this->output->createProgressBar(Skin::count());

        Skin::query()->chunkById(500, function ($skins) use ($lookup, &$updated, $bar) {
            foreach ($skins as $skin) {
                $baseName = $this->extractBaseName($skin->market_hash_name);
                $mapping = $lookup[$baseName] ?? null;

                if ($mapping) {
                    $skin->update([
                        'rarity' => $mapping[0],
                        'rarity_color' => $mapping[1],
                    ]);
                    $updated++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Updated {$updated} skins with real CS2 rarity.");

        return self::SUCCESS;
    }

    /**
     * "StatTrak™ AK-47 | Redline (Field-Tested)" → "AK-47 | Redline"
     * "★ Karambit | Doppler (Factory New)" → "★ Karambit | Doppler"
     */
    private function extractBaseName(string $marketHashName): string
    {
        // Remove exterior suffix: (Factory New), (Minimal Wear), etc.
        $name = preg_replace('/\s*\((Factory New|Minimal Wear|Field-Tested|Well-Worn|Battle-Scarred)\)\s*$/', '', $marketHashName);

        // Remove StatTrak™ prefix
        $name = preg_replace('/^StatTrak™\s+/', '', $name);

        return trim($name);
    }
}
