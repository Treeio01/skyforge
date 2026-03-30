<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'steam_id' => (string) fake()->unique()->numberBetween(70000000000000000, 79999999999999999),
            'username' => fake()->userName(),
            'avatar_url' => fake()->imageUrl(64, 64),
            'trade_url' => null,
            'balance' => 0,
            'remember_token' => Str::random(10),
        ];
    }

    public function withBalance(int $kopecks): static
    {
        return $this->state(['balance' => $kopecks]);
    }

    public function banned(string $reason = 'Test ban'): static
    {
        return $this->state([
            'is_banned' => true,
            'ban_reason' => $reason,
        ]);
    }

    public function admin(): static
    {
        return $this->state(['is_admin' => true]);
    }

    public function streamer(float $houseEdgeOverride = 0.00, float $chanceModifier = 1.100): static
    {
        return $this->state([
            'house_edge_override' => $houseEdgeOverride,
            'chance_modifier' => $chanceModifier,
        ]);
    }
}
