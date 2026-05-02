<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\DepositStatus;
use App\Models\Deposit;
use DomainException;

/**
 * Defense-in-depth + audit trail. The EnsureUserIsNotBanned middleware
 * already blocks banned users at the HTTP layer, but observer-level
 * check protects programmatic deposit creation (jobs, console commands).
 */
class DepositObserver
{
    public function creating(Deposit $deposit): void
    {
        if ($deposit->user?->is_banned) {
            throw new DomainException('Banned user cannot create deposits');
        }
    }

    public function updated(Deposit $deposit): void
    {
        if (! $deposit->wasChanged('status')) {
            return;
        }

        $previous = (string) $deposit->getOriginal('status');
        $current = (string) ($deposit->status?->value ?? $deposit->status);

        activity('deposit')
            ->performedOn($deposit)
            ->withProperties([
                'from' => $previous,
                'to' => $current,
                'amount' => $deposit->amount,
                'user_id' => $deposit->user_id,
            ])
            ->log("Deposit #{$deposit->id} status: {$previous} -> {$current}");

        if ($current === DepositStatus::Completed->value && $previous !== DepositStatus::Completed->value) {
            // Counter bump already handled by CreditBalanceAction when the
            // matching Transaction is inserted; nothing to do here besides
            // the audit log entry above.
        }
    }
}
