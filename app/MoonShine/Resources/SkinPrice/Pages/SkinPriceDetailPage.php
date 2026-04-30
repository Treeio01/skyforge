<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\SkinPrice\Pages;

use App\MoonShine\Resources\SkinPrice\SkinPriceResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<SkinPriceResource>
 */
class SkinPriceDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Скин', formatted: fn ($item) => $item->skin?->market_hash_name ?? 'ID:'.$item->skin_id),
            Number::make('Цена', 'price')->modifyRawValue(MoneyFormatter::field()),
            Text::make('Источник', 'source'),
            Date::make('Получена', 'fetched_at'),
        ];
    }
}
