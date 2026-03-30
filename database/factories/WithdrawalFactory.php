<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WithdrawalStatus;
use App\Models\Skin;
use App\Models\User;
use App\Models\UserSkin;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    protected $model = Withdrawal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'user_skin_id' => UserSkin::factory(),
            'skin_id' => Skin::factory(),
            'amount' => fake()->numberBetween(100, 5_000_000),
            'status' => WithdrawalStatus::Pending,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => WithdrawalStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function failed(string $reason = 'Trade offer declined'): static
    {
        return $this->state([
            'status' => WithdrawalStatus::Failed,
            'failure_reason' => $reason,
        ]);
    }
}
