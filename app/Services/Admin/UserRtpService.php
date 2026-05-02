<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\UpgradeResult;
use App\Models\Upgrade;
use App\Models\User;

final class UserRtpService
{
    /**
     * @return array{
     *     bet_all: int,
     *     won_all: int,
     *     rtp_all: float|null,
     *     bet_30d: int,
     *     won_30d: int,
     *     rtp_30d: float|null,
     *     count_all: int,
     *     count_30d: int,
     *     wins_all: int,
     *     wins_30d: int,
     * }
     */
    public function forUser(User $user): array
    {
        $base = Upgrade::query()->where('user_id', $user->id);

        $betAll = (int) (clone $base)->sum('bet_amount');
        $wonAll = (int) (clone $base)->where('result', UpgradeResult::Win->value)->sum('target_price');
        $countAll = (clone $base)->count();
        $winsAll = (clone $base)->where('result', UpgradeResult::Win->value)->count();

        $base30 = (clone $base)->where('created_at', '>=', now()->subDays(30));
        $bet30d = (int) (clone $base30)->sum('bet_amount');
        $won30d = (int) (clone $base30)->where('result', UpgradeResult::Win->value)->sum('target_price');
        $count30d = (clone $base30)->count();
        $wins30d = (clone $base30)->where('result', UpgradeResult::Win->value)->count();

        return [
            'bet_all' => $betAll,
            'won_all' => $wonAll,
            'rtp_all' => $betAll > 0 ? round($wonAll / $betAll * 100, 2) : null,
            'bet_30d' => $bet30d,
            'won_30d' => $won30d,
            'rtp_30d' => $bet30d > 0 ? round($won30d / $bet30d * 100, 2) : null,
            'count_all' => $countAll,
            'count_30d' => $count30d,
            'wins_all' => $winsAll,
            'wins_30d' => $wins30d,
        ];
    }
}
