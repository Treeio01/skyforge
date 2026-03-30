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
        $count = 0;

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

            if (count($batch) >= 500) {
                Skin::upsert($batch, ['market_hash_name'], [
                    'weapon_type', 'skin_name', 'exterior', 'category',
                    'image_path', 'price', 'updated_at',
                ]);
                $count += count($batch);
                $batch = [];
                $bar->advance(500);
            }
        }

        if (count($batch) > 0) {
            Skin::upsert($batch, ['market_hash_name'], [
                'weapon_type', 'skin_name', 'exterior', 'category',
                'image_path', 'price', 'updated_at',
            ]);
            $count += count($batch);
            $bar->advance(count($batch));
        }

        $bar->finish();
        $this->newLine();
        $this->info("Imported {$count} skins.");

        return self::SUCCESS;
    }
}
