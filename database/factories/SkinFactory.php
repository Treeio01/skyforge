<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SkinCategory;
use App\Enums\SkinExterior;
use App\Enums\SkinRarity;
use App\Models\Skin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Skin>
 */
class SkinFactory extends Factory
{
    protected $model = Skin::class;

    public function definition(): array
    {
        $weapons = ['AK-47', 'M4A4', 'AWP', 'USP-S', 'Glock-18', 'Desert Eagle'];
        $names = ['Redline', 'Asiimov', 'Dragon Lore', 'Hyper Beast', 'Fade', 'Vulcan'];
        $exterior = fake()->randomElement(SkinExterior::cases());
        $weapon = fake()->randomElement($weapons);
        $name = fake()->randomElement($names);

        return [
            'market_hash_name' => "{$weapon} | {$name} ({$exterior->label()})",
            'weapon_type' => $weapon,
            'skin_name' => $name,
            'exterior' => $exterior,
            'rarity' => fake()->randomElement(SkinRarity::cases()),
            'rarity_color' => fake()->randomElement(['#b0c3d9', '#4b69ff', '#8847ff', '#d32ce6', '#eb4b4b']),
            'category' => SkinCategory::Weapon,
            'image_path' => 'skins/default.webp',
            'price' => fake()->numberBetween(100, 5_000_000),
            'is_active' => true,
            'is_available_for_upgrade' => true,
        ];
    }

    public function expensive(): static
    {
        return $this->state(['price' => fake()->numberBetween(1_000_000, 10_000_000)]);
    }

    public function cheap(): static
    {
        return $this->state(['price' => fake()->numberBetween(100, 10_000)]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
