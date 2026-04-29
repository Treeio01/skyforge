<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Enums\UpgradeResult;
use App\Models\Deposit;
use App\Models\Skin;
use App\Models\Upgrade;
use App\Models\User;
use App\Models\UserSkin;
use Illuminate\Support\Carbon;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;

class DashboardPage extends Page
{
    public function getTitle(): string
    {
        return 'Дашборд';
    }

    private function formatRub(int $kopecks): string
    {
        return number_format($kopecks / 100, 2, '.', ' ').' ₽';
    }

    private function metric(string $label, string $value): Column
    {
        return Column::make([
            ValueMetric::make($label)->value($value),
        ])->columnSpan(4, 6);
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        $totalUsers = User::count();
        $activeUsers = User::where('last_active_at', '>=', now()->subDay())->count();
        $bannedUsers = User::where('is_banned', true)->count();
        $totalBalance = (int) User::sum('balance');

        $totalSkins = Skin::where('is_active', true)->count();
        $totalUserSkins = UserSkin::where('status', 'available')->count();

        $totalUpgrades = Upgrade::count();
        $wins = Upgrade::where('result', 'win')->count();
        $losses = Upgrade::where('result', 'lose')->count();
        $winRate = $totalUpgrades > 0 ? round($wins / $totalUpgrades * 100, 1) : 0;

        $totalBet = (int) Upgrade::sum('bet_amount');
        $totalWon = (int) Upgrade::where('result', 'win')->sum('target_price');
        $profit = $totalBet - $totalWon;

        $totalDeposited = (int) Deposit::where('status', 'completed')->sum('amount');
        $pendingDeposits = Deposit::where('status', 'pending')->count();

        $todayUpgrades = Upgrade::whereDate('created_at', today())->count();
        $todayBet = (int) Upgrade::whereDate('created_at', today())->sum('bet_amount');

        // --- Chart data: last 14 days ---
        $days = collect(range(13, 0))->map(fn (int $i) => now()->subDays($i)->startOfDay());

        $depositRows = Deposit::query()
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(completed_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $upgradeRows = Upgrade::query()
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as day, result, COUNT(*) as cnt')
            ->groupBy('day', 'result')
            ->get()
            ->groupBy('day');

        $depositLabels = $days->map(fn (Carbon $d) => $d->format('d.m'))->values()->all();
        $depositAmounts = $days->map(fn (Carbon $d) => round(((int) ($depositRows[$d->toDateString()] ?? 0)) / 100, 2))->values()->all();

        $upgradeLabels = $depositLabels;
        $upgradeWins = $days->map(fn (Carbon $d) => (int) ($upgradeRows[$d->toDateString()]?->firstWhere('result', UpgradeResult::Win->value)?->cnt ?? 0))->values()->all();
        $upgradeLoses = $days->map(fn (Carbon $d) => (int) ($upgradeRows[$d->toDateString()]?->firstWhere('result', UpgradeResult::Lose->value)?->cnt ?? 0))->values()->all();

        return [
            FlexibleRender::make(view('admin.charts.dashboard', compact(
                'depositLabels',
                'depositAmounts',
                'upgradeLabels',
                'upgradeWins',
                'upgradeLoses',
            ))),

            Grid::make([
                $this->metric('Пользователей', (string) $totalUsers),
                $this->metric('Активных (24ч)', (string) $activeUsers),
                $this->metric('Забанено', (string) $bannedUsers),
            ]),

            Grid::make([
                $this->metric('Общий баланс юзеров', $this->formatRub($totalBalance)),
                $this->metric('Скинов в базе', number_format($totalSkins, 0, '.', ' ')),
                $this->metric('Скинов у юзеров', number_format($totalUserSkins, 0, '.', ' ')),
            ]),

            Grid::make([
                $this->metric('Всего апгрейдов', (string) $totalUpgrades),
                $this->metric('Побед', (string) $wins),
                $this->metric('Поражений', (string) $losses),
            ]),

            Grid::make([
                $this->metric('Винрейт', $winRate.'%'),
                $this->metric('Профит', $this->formatRub($profit)),
                $this->metric('Депозитов', $this->formatRub($totalDeposited)),
            ]),

            Grid::make([
                $this->metric('Ожидают оплаты', (string) $pendingDeposits),
                $this->metric('Апгрейдов сегодня', (string) $todayUpgrades),
                $this->metric('Ставок сегодня', $this->formatRub($todayBet)),
            ]),
        ];
    }
}
