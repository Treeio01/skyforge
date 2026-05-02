<?php

declare(strict_types=1);

namespace App\Actions\Upgrade;

use App\Models\ProvablyFairSeed;
use App\Models\User;
use DomainException;

class IncrementSeedNonceAction
{
    public function execute(User $user): ProvablyFairSeed
    {
        $seedPair = $user->activeSeedPair ?? throw new DomainException('Нет активной пары сидов. Обновите страницу.');
        $seedPair->increment('nonce');

        return $seedPair->refresh();
    }
}
