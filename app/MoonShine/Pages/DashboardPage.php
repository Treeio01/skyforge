<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Services\Admin\DashboardMetricsService;
use App\Support\Admin\MoneyFormatter;
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
        $service = new DashboardMetricsService;
        $s = $service->summary();
        $charts = $service->charts();

        $profit = $s['totalBet'] - $s['totalWon'];

        return [
            FlexibleRender::make(view('admin.charts.dashboard', $charts)),

            Grid::make([
                $this->metric('Пользователей', (string) $s['totalUsers']),
                $this->metric('Активных (24ч)', (string) $s['activeUsers']),
                $this->metric('Забанено', (string) $s['bannedUsers']),
            ]),

            Grid::make([
                $this->metric('Общий баланс юзеров', MoneyFormatter::format($s['totalBalance'])),
                $this->metric('Скинов в базе', number_format($s['totalSkins'], 0, '.', ' ')),
                $this->metric('Скинов у юзеров', number_format($s['totalUserSkins'], 0, '.', ' ')),
            ]),

            Grid::make([
                $this->metric('Всего апгрейдов', (string) $s['totalUpgrades']),
                $this->metric('Побед', (string) $s['wins']),
                $this->metric('Поражений', (string) $s['losses']),
            ]),

            Grid::make([
                $this->metric('Винрейт', $s['winRate'].'%'),
                $this->metric('Профит', MoneyFormatter::format($profit)),
                $this->metric('Депозитов', MoneyFormatter::format($s['totalDeposited'])),
            ]),

            Grid::make([
                $this->metric('Ожидают оплаты', (string) $s['pendingDeposits']),
                $this->metric('Апгрейдов сегодня', (string) $s['todayUpgrades']),
                $this->metric('Ставок сегодня', MoneyFormatter::format($s['todayBet'])),
            ]),
        ];
    }
}
