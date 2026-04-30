<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Skin\Pages;

use App\MoonShine\Resources\Skin\SkinResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<SkinResource>
 */
class SkinDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Preview::make('Изображение', formatted: fn ($item) => $item->image_path ? asset('storage/'.$item->image_path) : null)->image(),
            Text::make('Название', 'market_hash_name'),
            Text::make('Оружие', 'weapon_type'),
            Text::make('Скин', 'skin_name'),
            Text::make('Exterior', formatted: fn ($item) => $item->exterior?->value ?? (string) $item->exterior),
            Text::make('Редкость', formatted: fn ($item) => $item->rarity?->value ?? (string) $item->rarity),
            Text::make('Категория', formatted: fn ($item) => $item->category?->value ?? (string) $item->category),
            Number::make('Цена', 'price')->modifyRawValue(MoneyFormatter::field()),
            Switcher::make('Активен', 'is_active'),
            Switcher::make('Для апгрейда', 'is_available_for_upgrade'),
            Date::make('Цена обновлена', 'price_updated_at'),
            Date::make('Создан', 'created_at'),
            Date::make('Обновлён', 'updated_at'),
        ];
    }
}
