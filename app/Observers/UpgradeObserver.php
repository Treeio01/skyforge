<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\UpgradeResult;
use App\Models\Upgrade;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Owns the per-user upgrade aggregate counters. Counters previously lived
 * inline in DebitBalance/CreditBalance, but those only saw the cash
 * portion of the bet — skin-only upgrades didn't bump total_upgraded
 * and total_won was never incremented at all (no Transaction was ever
 * created for the won skin). Owning it here off the Upgrade row fixes
 * both gaps.
 */
class UpgradeObserver
{
    public function created(Upgrade $upgrade): void
    {
        $wonAmount = $upgrade->result === UpgradeResult::Win ? (int) $upgrade->target_price : 0;

        User::query()->whereKey($upgrade->user_id)->update([
            'total_upgraded' => DB::raw('total_upgraded + '.(int) $upgrade->bet_amount),
            'total_won' => DB::raw('total_won + '.$wonAmount),
        ]);
    }
}
