<?php

declare(strict_types=1);

namespace App\Actions\Deposit;

use App\Actions\Balance\CreditBalanceAction;
use App\Enums\DepositStatus;
use App\Enums\TransactionType;
use App\Events\DepositCompleted;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompleteDepositAction
{
    public function __construct(
        private CreditBalanceAction $creditBalance,
    ) {}

    public function execute(Deposit $deposit): bool
    {
        if ($deposit->status === DepositStatus::Completed) {            return false;
        }

        return DB::transaction(function () use ($deposit) {
            /** @var Deposit $deposit */
            $deposit = Deposit::lockForUpdate()->findOrFail($deposit->id);

            if ($deposit->status === DepositStatus::Completed) {                return false;
            }

            $deposit->update([
                'status' => DepositStatus::Completed,
                'completed_at' => now(),
            ]);

            /** @var User $user */
            $user = $deposit->user;

            $this->creditBalance->execute(
                $user,
                $deposit->amount,
                TransactionType::Deposit,
                $deposit,
            );

            DepositCompleted::dispatch($deposit);

            return true;
        });
    }
}
