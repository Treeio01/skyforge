<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\PromoCodeUsage\Pages;

use App\MoonShine\Resources\PromoCodeUsage\PromoCodeUsageResource;
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
 * @extends IndexPage<PromoCodeUsageResource>
 */
class PromoCodeUsageIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Пользователь', formatted: fn ($item) => $item->user?->username ?? 'ID:'.$item->user_id),
            Text::make('Промокод', formatted: fn ($item) => $item->promoCode?->code ?? 'ID:'.$item->promo_code_id),
            Number::make('Начислено', 'amount')
                ->modifyRawValue(MoneyFormatter::field()),
            Date::make('Использован', 'created_at'),
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
            QueryTag::make('Сегодня', fn ($q) => $q->where('created_at', '>=', now()->startOfDay())),
            QueryTag::make('За 7д', fn ($q) => $q->where('created_at', '>=', now()->subWeek())),
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
