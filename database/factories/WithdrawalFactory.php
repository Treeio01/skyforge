<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WithdrawalStatus;
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
            'amount' => fake()->numberBetween(100, 5_000_000),
            'status' => WithdrawalStatus::Pending,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Withdrawal $withdrawal) {
            if (! $withdrawal->user_skin_id) {
                $userSkin = UserSkin::factory()->create([
                    'user_id' => $withdrawal->user_id ?? User::factory(),
                ]);

                $withdrawal->user_id = $userSkin->user_id;
                $withdrawal->user_skin_id = $userSkin->id;
                $withdrawal->skin_id = $userSkin->skin_id;
                $withdrawal->amount = $userSkin->price_at_acquisition;
            }
        });
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
