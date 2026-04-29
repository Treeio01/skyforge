<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\SkinPrice\Pages;

use App\MoonShine\Resources\SkinPrice\SkinPriceResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<SkinPriceResource>
 */
class SkinPriceIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Скин', formatted: fn ($item) => mb_substr($item->skin?->market_hash_name ?? 'ID:'.$item->skin_id, 0, 45)),
            Number::make('Цена', 'price')
                ->modifyRawValue(MoneyFormatter::field()),
            Text::make('Источник', 'source'),
            Date::make('Получена', 'fetched_at'),
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
        return [];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [
            QueryTag::make('Все', fn ($q) => $q),
            QueryTag::make('Сегодня', fn ($q) => $q->where('fetched_at', '>=', now()->startOfDay())),
        ];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        return [];
    }
}
