<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SkinRarity;
use App\Models\Skin;
use Illuminate\Console\Command;

class AssignSkinRarityCommand extends Command
{
    protected $signature = 'skins:assign-rarity {--force : Overwrite existing rarity values}';

    protected $description = 'Assign rarity and rarity_color to skins based on category + price heuristics';

    public function handle(): int
    {
        $force = $this->option('force');

        $query = Skin::query();

        if (! $force) {
            $query->whereNull('rarity');
        }

        $total = $query->count();
        $this->info("Assigning rarity to {$total} skins...");

        $bar = $this->output->createProgressBar($total);
        $updated = 0;

        $query->chunkById(500, function ($skins) use ($bar, &$updated) {
            foreach ($skins as $skin) {
                $rarity = $this->determineRarity($skin);
                $skin->update([
                    'rarity' => $rarity->value,
                    'rarity_color' => $rarity->color(),
                ]);
                $updated++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Updated {$updated} skins.");

        return self::SUCCESS;
    }

    private function determineRarity(Skin $skin): SkinRarity
    {
        $category = $skin->category?->value ?? 'other';
        $name = $skin->market_hash_name;

        // M4A4 | Howl — единственный Contraband
        if (str_contains($name, 'M4A4 | Howl')) {
            return SkinRarity::Contraband;
        }

        // Ножи и перчатки — золотые (Extraordinary)
        if (in_array($category, ['knife', 'gloves']) || str_starts_with($name, '★')) {
            return SkinRarity::Extraordinary;
        }

        // Наклейки, граффити, контейнеры, шармы — Consumer
        if (in_array($category, ['sticker', 'graffiti', 'container', 'charm'])) {
            return SkinRarity::Consumer;
        }

        // Агенты — Classified
        if ($category === 'agent') {
            return SkinRarity::Classified;
        }

        // Оружие — по паттернам имени (известные скины)
        if ($category === 'weapon') {
            return $this->determineWeaponRarity($name);
        }

        return SkinRarity::Consumer;
    }

    private function determineWeaponRarity(string $name): SkinRarity
    {
        // Covert скины (красные) — самые известные
        $covert = [
            'Dragon Lore', 'Fire Serpent', 'The Prince', 'Wild Lotus',
            'Gungnir', 'Medusa', 'Printstream', 'Fade', 'Asiimov',
            'Hyper Beast', 'Neon Rider', 'Oni Taiji', 'Gold Arabesque',
            'X-Ray', 'Neo-Noir', 'Emerald Dragon', 'Chantico\'s Fire',
        ];

        foreach ($covert as $pattern) {
            if (str_contains($name, $pattern)) {
                return SkinRarity::Covert;
            }
        }

        // Classified скины (розовые)
        $classified = [
            'Vulcan', 'Aquamarine Revenge', 'Desolate Space', 'Bloodsport',
            'Kill Confirmed', 'Mecha Industries', 'Cyrex', 'Golden Coil',
            'Frontside Misty', 'Phantom Disruptor', 'In Living Color',
            'Basilisk', 'Player Two', 'Daimyo', 'Buzz Kill',
        ];

        foreach ($classified as $pattern) {
            if (str_contains($name, $pattern)) {
                return SkinRarity::Classified;
            }
        }

        // Restricted скины (фиолетовые)
        $restricted = [
            'Redline', 'Trigon', 'Wasteland Rebel', 'Neon Revolution',
            'Jet Set', 'Orion', 'Caiman', 'Bright Water', 'Atomic Alloy',
            'Hot Rod', 'Dark Water', 'Decimator',
        ];

        foreach ($restricted as $pattern) {
            if (str_contains($name, $pattern)) {
                return SkinRarity::Restricted;
            }
        }

        // Всё остальное — Mil-Spec (синий) как нейтральный дефолт для оружий
        return SkinRarity::MilSpec;
    }
}
