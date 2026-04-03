<?php

declare(strict_types=1);

namespace App\Actions\Balance;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreditBalanceAction
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
            $balanceBefore = $user->balance;
            $balanceAfter = $balanceBefore + $amount;

            $user->balance = $balanceAfter;

            if ($type === TransactionType::Deposit) {
                $user->total_deposited += $amount;
            } elseif ($type === TransactionType::UpgradeWin) {
                $user->total_won += $amount;
            }

            $user->save();

            return $this->createTransaction->execute(
                $user,
                $type,
                $amount,
                $balanceBefore,
                $balanceAfter,
                $reference,
                $description,
            );
        });
    }
}
