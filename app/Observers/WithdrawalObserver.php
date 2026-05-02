<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

/**
 * Owns the per-user withdrawn aggregate. Counter previously lived inline
 * in DebitBalance with type=Withdrawal — but a real skin withdrawal does
 * not pass through DebitBalance (the skin leaves inventory directly), so
 * total_withdrawn was never bumped on actual withdrawals. The only path
 * that hit it was MarketService::buy, which uses TransactionType::Withdrawal
 * for accounting reasons — counting market purchases as withdrawals was
 * incorrect.
 *
 * Fires on the {pending|processing|sent} -> completed transition.
 */
class WithdrawalObserver
{
    public function updated(Withdrawal $withdrawal): void
    {
        if (! $withdrawal->wasChanged('status')) {
            return;
        }

        $current = $withdrawal->status?->value ?? $withdrawal->status;

        if ($current !== WithdrawalStatus::Completed->value) {
            return;
        }

        User::query()->whereKey($withdrawal->user_id)->update([
            'total_withdrawn' => DB::raw('total_withdrawn + '.(int) $withdrawal->amount),
        ]);
    }
}
