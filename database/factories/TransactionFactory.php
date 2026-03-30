<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $balanceBefore = fake()->numberBetween(0, 5_000_000);
        $amount = fake()->numberBetween(100, 1_000_000);

        return [
            'user_id' => User::factory(),
            'type' => TransactionType::Deposit,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore + $amount,
            'created_at' => now(),
        ];
    }
}
