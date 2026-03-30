<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UpgradeResult;
use App\Models\Skin;
use App\Models\Upgrade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Upgrade>
 */
class UpgradeFactory extends Factory
{
    protected $model = Upgrade::class;

    public function definition(): array
    {
        $betAmount = fake()->numberBetween(100, 1_000_000);
        $targetPrice = fake()->numberBetween($betAmount + 100, $betAmount * 5);

        return [
            'user_id' => User::factory(),
            'target_skin_id' => Skin::factory(),
            'bet_amount' => $betAmount,
            'balance_amount' => $betAmount,
            'target_price' => $targetPrice,
            'chance' => round($betAmount / $targetPrice * 95, 5),
            'multiplier' => round($targetPrice / $betAmount, 2),
            'house_edge' => 5.00,
            'chance_modifier' => 1.000,
            'result' => fake()->randomElement(UpgradeResult::cases()),
            'roll_value' => fake()->randomFloat(10, 0, 1),
            'roll_hex' => fake()->regexify('[a-f0-9]{16}'),
            'client_seed' => fake()->sha1(),
            'server_seed' => hash('sha256', fake()->sha1()),
            'server_seed_raw' => fake()->sha1(),
            'nonce' => fake()->numberBetween(1, 1000),
            'is_revealed' => false,
            'created_at' => now(),
        ];
    }

    public function won(): static
    {
        return $this->state(['result' => UpgradeResult::Win]);
    }

    public function lost(): static
    {
        return $this->state(['result' => UpgradeResult::Lose]);
    }
}
