<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\UpgradeResult;
use App\Models\Deposit;
use App\Models\Skin;
use App\Models\Upgrade;
use App\Models\User;
use App\Models\UserSkin;
use Illuminate\Support\Carbon;

final class DashboardMetricsService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        // Real users only — bots from feed:fake distort every metric.
        $users = User::query()->where('is_bot', false);
        $upgrades = Upgrade::query()->where('is_fake', false);

        return [
            'totalUsers' => (clone $users)->count(),
            'activeUsers' => (clone $users)->where('last_active_at', '>=', now()->subDay())->count(),
            'bannedUsers' => (clone $users)->where('is_banned', true)->count(),
            'totalBalance' => (int) (clone $users)->sum('balance'),

            'totalSkins' => Skin::where('is_active', true)->count(),
            'totalUserSkins' => UserSkin::where('status', 'available')->count(),

            'totalUpgrades' => $total = (clone $upgrades)->count(),
            'wins' => $wins = (clone $upgrades)->where('result', 'win')->count(),
            'losses' => (clone $upgrades)->where('result', 'lose')->count(),
            'winRate' => $total > 0 ? round($wins / $total * 100, 1) : 0,

            'totalBet' => (int) (clone $upgrades)->sum('bet_amount'),
            'totalWon' => (int) (clone $upgrades)->where('result', 'win')->sum('target_price'),

            'totalDeposited' => (int) Deposit::where('status', 'completed')->sum('amount'),
            'pendingDeposits' => Deposit::where('status', 'pending')->count(),

            'todayUpgrades' => (clone $upgrades)->whereDate('created_at', today())->count(),
            'todayBet' => (int) (clone $upgrades)->whereDate('created_at', today())->sum('bet_amount'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function charts(): array
    {
        $days = collect(range(13, 0))->map(fn (int $i) => now()->subDays($i)->startOfDay());

        $depositRows = Deposit::query()
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(completed_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $upgradeRows = Upgrade::query()
            ->where('is_fake', false)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as day, result, COUNT(*) as cnt')
            ->groupBy('day', 'result')
            ->get()
            ->groupBy('day');

        $labels = $days->map(fn (Carbon $d) => $d->format('d.m'))->values()->all();

        return [
            'depositLabels' => $labels,
            'depositAmounts' => $days->map(fn (Carbon $d) => round(((int) ($depositRows->get($d->toDateString()) ?? 0)) / 100, 2))->values()->all(),
            'upgradeLabels' => $labels,
            'upgradeWins' => $days->map(fn (Carbon $d) => (int) ($upgradeRows->get($d->toDateString())?->firstWhere('result', UpgradeResult::Win->value)?->cnt ?? 0))->values()->all(),
            'upgradeLoses' => $days->map(fn (Carbon $d) => (int) ($upgradeRows->get($d->toDateString())?->firstWhere('result', UpgradeResult::Lose->value)?->cnt ?? 0))->values()->all(),
        ];
    }
}
