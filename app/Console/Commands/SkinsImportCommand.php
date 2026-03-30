<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SkinCategory;
use App\Enums\SkinExterior;
use App\Models\Skin;
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
            $parsed = self::parseMarketHashName($marketHashName);

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

    /**
     * Parse market_hash_name into components.
     *
     * Examples:
     *   "AK-47 | Redline (Field-Tested)" → weapon: AK-47, skin: Redline, exterior: FT, category: weapon
     *   "★ Karambit | Doppler (Factory New)" → weapon: Karambit, skin: Doppler, exterior: FN, category: knife
     *   "Sticker | karrigan | Paris 2023" → skin: karrigan | Paris 2023, category: sticker
     *
     * @return array{weapon_type: ?string, skin_name: ?string, exterior: ?string, category: string}
     */
    public static function parseMarketHashName(string $name): array
    {
        $category = self::detectCategory($name);
        $exterior = self::extractExterior($name);

        $cleanName = preg_replace('/\s*\([^)]*\)\s*$/', '', $name);
        $cleanName = ltrim($cleanName, "★ \t\n\r\0\x0B");

        if (in_array($category, [SkinCategory::Sticker->value, SkinCategory::Graffiti->value, SkinCategory::Container->value])) {
            $parts = explode(' | ', $cleanName, 2);

            return [
                'weapon_type' => null,
                'skin_name' => $parts[1] ?? $parts[0],
                'exterior' => $exterior,
                'category' => $category,
            ];
        }

        if (str_contains($cleanName, ' | ')) {
            [$weaponType, $skinName] = explode(' | ', $cleanName, 2);

            return [
                'weapon_type' => $weaponType,
                'skin_name' => $skinName,
                'exterior' => $exterior,
                'category' => $category,
            ];
        }

        return [
            'weapon_type' => null,
            'skin_name' => $cleanName,
            'exterior' => $exterior,
            'category' => $category,
        ];
    }

    private static function detectCategory(string $name): string
    {
        if (str_starts_with($name, 'Sticker |')) {
            return SkinCategory::Sticker->value;
        }

        if (str_starts_with($name, 'Sealed Graffiti |') || str_starts_with($name, 'Graffiti |')) {
            return SkinCategory::Graffiti->value;
        }

        if (str_starts_with($name, '★')) {
            if (str_contains($name, 'Gloves') || str_contains($name, 'Wraps') || str_contains($name, 'Hand Wraps')) {
                return SkinCategory::Gloves->value;
            }

            return SkinCategory::Knife->value;
        }

        if (str_contains($name, 'Charm |')) {
            return SkinCategory::Charm->value;
        }

        if (str_contains($name, 'Agent') || str_contains($name, '| NSWC') || str_contains($name, '| Guerrilla') || str_contains($name, '| Sabre') || str_contains($name, '| FBI') || str_contains($name, '| SWAT') || str_contains($name, '| KSK') || str_contains($name, '| SAS') || str_contains($name, '| SEAL')) {
            // Agents have specific patterns but are hard to detect reliably
            // We'll keep them as "other" unless explicitly matched
        }

        if (preg_match('/^(.*Case|.*Capsule|.*Package)$/i', $name)) {
            return SkinCategory::Container->value;
        }

        return SkinCategory::Weapon->value;
    }

    private static function extractExterior(string $name): ?string
    {
        $map = [
            'Factory New' => SkinExterior::FactoryNew->value,
            'Minimal Wear' => SkinExterior::MinimalWear->value,
            'Field-Tested' => SkinExterior::FieldTested->value,
            'Well-Worn' => SkinExterior::WellWorn->value,
            'Battle-Scarred' => SkinExterior::BattleScarred->value,
        ];

        foreach ($map as $label => $value) {
            if (str_contains($name, "({$label})")) {
                return $value;
            }
        }

        return null;
    }
}
