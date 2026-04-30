<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\UserSkin\Pages;

use App\MoonShine\Resources\UserSkin\UserSkinResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<UserSkinResource>
 */
class UserSkinDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Пользователь', formatted: fn ($item) => $item->user?->username ?? 'ID:'.$item->user_id),
            Text::make('Steam ID', formatted: fn ($item) => $item->user?->steam_id ?? '—'),
            Text::make('Скин', formatted: fn ($item) => $item->skin?->market_hash_name ?? 'ID:'.$item->skin_id),
            Number::make('Цена получения', 'price_at_acquisition')->modifyRawValue(MoneyFormatter::field()),
            Text::make('Источник', formatted: fn ($item) => $item->source?->value ?? (string) $item->source),
            Text::make('Source ID', 'source_id'),
            Text::make('Статус', formatted: fn ($item) => $item->status?->value ?? (string) $item->status),
            Date::make('Выведен', 'withdrawn_at'),
            Date::make('Получен', 'created_at'),
        ];
    }
}
