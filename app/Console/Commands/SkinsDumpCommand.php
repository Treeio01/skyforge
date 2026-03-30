<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Skin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SkinsDumpCommand extends Command
{
    protected $signature = 'skins:dump {--path=database/dumps/skins.sql : Output file path}';

    protected $description = 'Export skins table to SQL dump file';

    public function handle(): int
    {
        $path = base_path($this->option('path'));
        $total = Skin::count();

        if ($total === 0) {
            $this->error('No skins in database to dump.');

            return self::FAILURE;
        }

        $this->info("Dumping {$total} skins...");

        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'w');
        fwrite($handle, "-- SKYFORGE skins dump\n");
        fwrite($handle, "-- Generated from skins table ({$total} skins)\n");
        fwrite($handle, "-- Prices in kopecks (1 RUB = 100 kopecks)\n\n");
        fwrite($handle, "SET NAMES utf8mb4;\n\n");

        $columns = [
            'market_hash_name', 'weapon_type', 'skin_name', 'exterior',
            'rarity', 'rarity_color', 'category', 'image_path', 'price',
            'is_active', 'is_available_for_upgrade',
        ];

        $columnList = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));

        Skin::query()->orderBy('id')->chunk(500, function ($skins) use ($handle, $columnList, $columns) {
            $values = [];

            foreach ($skins as $skin) {
                $row = [];

                foreach ($columns as $col) {
                    $val = $skin->{$col};

                    if ($val === null) {
                        $row[] = 'NULL';
                    } elseif (is_bool($val)) {
                        $row[] = $val ? '1' : '0';
                    } elseif (is_int($val)) {
                        $row[] = (string) $val;
                    } elseif ($val instanceof \BackedEnum) {
                        $row[] = "'".addslashes((string) $val->value)."'";
                    } else {
                        $row[] = "'".addslashes((string) $val)."'";
                    }
                }
                $values[] = '('.implode(', ', $row).')';
            }

            fwrite($handle, "INSERT INTO `skins` ({$columnList}) VALUES\n");
            fwrite($handle, implode(",\n", $values));
            fwrite($handle, "\nON DUPLICATE KEY UPDATE `price` = VALUES(`price`), `updated_at` = NOW();\n\n");
        });

        fclose($handle);

        $size = round(File::size($path) / 1024 / 1024, 1);
        $this->info("Dump saved to {$path} ({$size} MB)");

        return self::SUCCESS;
    }
}
