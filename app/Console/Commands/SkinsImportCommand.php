<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Skin;
use App\Services\SkinNameParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SkinsImportCommand extends Command
{
    protected $signature = 'skins:import {path : Path to skins_index.json}';

    protected $description = 'Import skins from skins_index.json into the database';

    private const BATCH_SIZE = 500;

    private const UPSERT_COLUMNS = [
        'weapon_type', 'skin_name', 'exterior', 'category',
        'image_path', 'price', 'updated_at',
    ];

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! File::exists($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $json = File::get($path);
        $skins = json_decode($json, true);

        if (! is_array($skins)) {
            $this->error('Invalid JSON format.');

            return self::FAILURE;
        }

        $this->info('Importing '.count($skins).' skins...');
        $bar = $this->output->createProgressBar(count($skins));

        $batch = [];

        foreach ($skins as $marketHashName => $data) {
            $parsed = SkinNameParser::parse($marketHashName);

            $batch[] = [
                'market_hash_name' => $marketHashName,
                'weapon_type' => $parsed['weapon_type'],
                'skin_name' => $parsed['skin_name'],
                'exterior' => $parsed['exterior'],
                'category' => $parsed['category'],
                'rarity' => null,
                'rarity_color' => null,
                'image_path' => 'skins/'.$data['file'],
                'price' => (int) round(($data['price'] ?? 0) * 100),
                'is_active' => true,
                'is_available_for_upgrade' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                $this->flushBatch($batch);
                $bar->advance(count($batch));
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            $this->flushBatch($batch);
            $bar->advance(count($batch));
        }

        $bar->finish();
        $this->newLine();
        $this->info('Imported '.count($skins).' skins.');

        return self::SUCCESS;
    }

    /** @param array<int, array<string, mixed>> $batch */
    private function flushBatch(array $batch): void
    {
        Skin::upsert($batch, ['market_hash_name'], self::UPSERT_COLUMNS);
    }
}
