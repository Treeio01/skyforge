<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DepositMethod;
use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deposit>
 */
class DepositFactory extends Factory
{
    protected $model = Deposit::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'method' => fake()->randomElement(DepositMethod::cases()),
            'amount' => fake()->numberBetween(10_000, 1_000_000),
            'status' => DepositStatus::Pending,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => DepositStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function withIdempotencyKey(): static
    {
        return $this->state(['idempotency_key' => fake()->uuid()]);
    }
}
