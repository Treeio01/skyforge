<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProvablyFairSeed;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProvablyFairSeed>
 */
class ProvablyFairSeedFactory extends Factory
{
    protected $model = ProvablyFairSeed::class;

    public function definition(): array
    {
        $serverSeed = Str::random(64);

        return [
            'user_id' => User::factory(),
            'client_seed' => Str::random(32),
            'server_seed' => $serverSeed,
            'server_seed_hash' => hash('sha256', $serverSeed),
            'nonce' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withNonce(int $nonce): static
    {
        return $this->state(['nonce' => $nonce]);
    }
}
