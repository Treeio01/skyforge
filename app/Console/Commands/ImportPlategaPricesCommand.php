<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Skin;
use Illuminate\Console\Command;

class ImportPlategaPricesCommand extends Command
{
    protected $signature = 'skins:import-platega
        {path : Path to prices.csv}
        {--rate=95 : USD to RUB exchange rate}
        {--deactivate : Deactivate skins not in CSV}';

    protected $description = 'Import skin prices from Platega CSV (USD) and optionally deactivate missing skins';

    public function handle(): int
    {
        $path = $this->argument('path');
        $rate = (float) $this->option('rate');
        $deactivate = $this->option('deactivate');

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $this->info("Importing prices from {$path}");
        $this->info("USD/RUB rate: {$rate}");

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle); // skip header

        $prices = [];
        $lineCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3) {
                continue;
            }

            $game = trim($row[0]);
            $name = trim($row[1]);
            $priceUsd = (float) $row[2];

            if ($game !== 'CS2' || $priceUsd <= 0 || $name === '') {
                continue;
            }

            $priceRub = $priceUsd * $rate;
            $priceKopecks = (int) round($priceRub * 100);

            $prices[$name] = $priceKopecks;
            $lineCount++;
        }

        fclose($handle);

        $this->info("Parsed {$lineCount} CS2 skins from CSV.");

        // Update prices in batches
        $updated = 0;
        $notFound = 0;
        $bar = $this->output->createProgressBar(count($prices));

        foreach (array_chunk($prices, 500, true) as $batch) {
            foreach ($batch as $name => $priceKopecks) {
                $affected = Skin::where('market_hash_name', $name)->update([
                    'price' => $priceKopecks,
                    'price_updated_at' => now(),
                ]);

                if ($affected > 0) {
                    $updated++;
                } else {
                    $notFound++;
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated: {$updated}, not found in DB: {$notFound}");

        // Deactivate skins not in CSV
        if ($deactivate) {
            $csvNames = array_keys($prices);

            $deactivated = Skin::where('is_active', true)
                ->whereNotIn('market_hash_name', $csvNames)
                ->update([
                    'is_active' => false,
                    'is_available_for_upgrade' => false,
                ]);

            $this->info("Deactivated {$deactivated} skins not in CSV.");

            // Re-activate skins that ARE in CSV
            $reactivated = Skin::where('is_active', false)
                ->whereIn('market_hash_name', $csvNames)
                ->update([
                    'is_active' => true,
                    'is_available_for_upgrade' => true,
                ]);

            if ($reactivated > 0) {
                $this->info("Re-activated {$reactivated} skins from CSV.");
            }
        }

        $active = Skin::where('is_active', true)->count();
        $this->info("Active skins now: {$active}");

        return self::SUCCESS;
    }
}
