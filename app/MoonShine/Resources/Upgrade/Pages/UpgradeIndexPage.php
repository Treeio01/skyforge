<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Upgrade\Pages;

use App\Enums\UpgradeResult;
use App\Models\Upgrade;
use App\MoonShine\Resources\Upgrade\UpgradeResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<UpgradeResource>
 */
class UpgradeIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Игрок', formatted: fn ($item) => $item->user?->username ?? '—'),
            Number::make('Ставка', 'bet_amount')
                ->modifyRawValue(MoneyFormatter::field()),
            Text::make('Скин', formatted: fn ($item) => mb_substr($item->targetSkin?->market_hash_name ?? '—', 0, 35)),
            Number::make('Цена цели', 'target_price')
                ->modifyRawValue(MoneyFormatter::field()),
            Number::make('Шанс', 'chance')
                ->modifyRawValue(fn (mixed $value) => round((float) $value, 2).'%'),
            Text::make('Результат', formatted: fn ($item) => $item->result?->value === 'win' ? '✅ Победа' : '❌ Проигрыш'),
            Text::make('Тип', formatted: fn ($item) => $item->is_fake ? 'Фейковый' : 'Реальный'),
            Date::make('Дата', 'created_at'),
        ];
    }

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Select::make('Результат', 'result')
                ->options(['win' => 'Победа', 'lose' => 'Проигрыш'])
                ->nullable(),
            Select::make('Тип', 'is_fake')
                ->options(['0' => 'Реальный', '1' => 'Фейковый'])
                ->nullable(),
        ];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [
            QueryTag::make('Все', fn ($q) => $q),
            QueryTag::make('Реальные', fn ($q) => $q->where('is_fake', false)),
            QueryTag::make('Фейковые', fn ($q) => $q->where('is_fake', true)),
            QueryTag::make('Победы сегодня', fn ($q) => $q->where('result', UpgradeResult::Win->value)->where('created_at', '>=', now()->startOfDay())),
            QueryTag::make('Проигрыши сегодня', fn ($q) => $q->where('result', UpgradeResult::Lose->value)->where('created_at', '>=', now()->startOfDay())),
            QueryTag::make('За 7д', fn ($q) => $q->where('created_at', '>=', now()->subWeek())),
        ];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        // Reflect real player behaviour — exclude bot-generated fake feed.
        $todayQuery = Upgrade::query()
            ->where('is_fake', false)
            ->where('created_at', '>=', now()->startOfDay());
        $todayCount = (clone $todayQuery)->count();
        $todayWins = (clone $todayQuery)->where('result', UpgradeResult::Win->value)->count();
        $todayBet = (int) (clone $todayQuery)->sum('bet_amount');
        $todayPayout = (int) (clone $todayQuery)
            ->where('result', UpgradeResult::Win->value)
            ->sum('target_price');
        $todayMargin = $todayBet - $todayPayout;
        $rtp = $todayBet > 0 ? round(($todayPayout / $todayBet) * 100, 1) : 0.0;

        return [
            ValueMetric::make('Апгрейдов сегодня')->value($todayCount)->columnSpan(3, 12),
            ValueMetric::make('Побед сегодня')
                ->value($todayCount > 0 ? $todayWins.' ('.round($todayWins / $todayCount * 100, 1).'%)' : '0')
                ->columnSpan(3, 12),
            ValueMetric::make('Маржа сегодня')
                ->value(number_format($todayMargin / 100, 2, '.', ' ').' ₽')
                ->columnSpan(3, 12),
            ValueMetric::make('RTP сегодня')->value($rtp.'%')->columnSpan(3, 12),
        ];
    }
}
