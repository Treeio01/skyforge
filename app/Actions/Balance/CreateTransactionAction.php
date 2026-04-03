<?php

declare(strict_types=1);

namespace App\Actions\Balance;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CreateTransactionAction
{
    public function execute(
        User $user,
        TransactionType $type,
        int $amount,
        int $balanceBefore,
        int $balanceAfter,
        ?Model $reference = null,
        ?string $description = null,
    ): Transaction {
        return Transaction::create([
            'user_id' => $user->id,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_type' => $reference ? $reference->getMorphClass() : null,
            'reference_id' => $reference?->getKey(),
            'description' => $description,
        ]);
    }
}
