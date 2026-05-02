<?php

declare(strict_types=1);

namespace App\Actions\Balance;

use App\Enums\TransactionType;
use App\Events\BalanceUpdated;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DebitBalanceAction
{
    public function __construct(
        private CreateTransactionAction $createTransaction,
    ) {}

    public function execute(
        User $user,
        int $amount,
        TransactionType $type,
        ?Model $reference = null,
        ?string $description = null,
    ): Transaction {
        return DB::transaction(function () use ($user, $amount, $type, $reference, $description) {
            $user = User::lockForUpdate()->find($user->id);

            if ($user->balance < $amount) {
                throw new InsufficientBalanceException($amount, $user->balance);
            }

            $balanceBefore = $user->balance;
            $balanceAfter = $balanceBefore - $amount;

            $user->balance = $balanceAfter;
            $user->save();

            $tx = $this->createTransaction->execute(
                $user,
                $type,
                -$amount,
                $balanceBefore,
                $balanceAfter,
                $reference,
                $description,
            );

            BalanceUpdated::dispatch($user);

            return $tx;
        });
    }
}
