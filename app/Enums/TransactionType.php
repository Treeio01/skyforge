<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case UpgradeBet = 'upgrade_bet';
    case UpgradeWin = 'upgrade_win';
    case Refund = 'refund';
    case Bonus = 'bonus';
    case AdminAdjustment = 'admin_adjustment';
}
