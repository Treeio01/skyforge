<?php

declare(strict_types=1);

namespace App\Actions\Upgrade;

use App\Models\Setting;
use DomainException;

class ValidateUpgradeBetAction
{
    public function execute(int $betAmount, int $targetPrice): void
    {
        if ($betAmount <= 0) {
            throw new DomainException('Сумма ставки должна быть больше нуля.');
        }

        $minBet = (int) Setting::get('min_bet_amount', 100);
        $maxBet = (int) Setting::get('max_bet_amount', 5_000_000);

        if ($betAmount < $minBet) {
            throw new DomainException('Сумма ставки меньше минимальной.');
        }

        if ($betAmount > $maxBet) {
            throw new DomainException('Сумма ставки больше максимальной.');
        }

        if ($betAmount >= $targetPrice) {
            throw new DomainException('Ставка должна быть меньше цены цели.');
        }
    }
}
