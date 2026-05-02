<?php

declare(strict_types=1);

namespace App\Actions\Withdrawal;

use App\Enums\WithdrawalStatus;
use App\Models\User;
use DomainException;

class ValidatePendingWithdrawalsLimitAction
{
    private const MAX_PENDING = 3;

    public function execute(User $user): void
    {
        $pending = $user->withdrawals()
            ->whereIn('status', [WithdrawalStatus::Pending, WithdrawalStatus::Processing])
            ->count();

        if ($pending >= self::MAX_PENDING) {
            throw new DomainException('Достигнут лимит ожидающих выводов. Дождитесь завершения предыдущих.');
        }
    }
}
