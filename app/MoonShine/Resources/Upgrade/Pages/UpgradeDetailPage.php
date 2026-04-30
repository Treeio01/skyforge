<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Upgrade\Pages;

use App\MoonShine\Resources\Upgrade\UpgradeResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<UpgradeResource>
 */
class UpgradeDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Игрок', formatted: fn ($item) => $item->user?->username ?? 'ID:'.$item->user_id),
            Text::make('Steam ID', formatted: fn ($item) => $item->user?->steam_id ?? '—'),
            Text::make('Целевой скин', formatted: fn ($item) => $item->targetSkin?->market_hash_name ?? 'ID:'.$item->target_skin_id),
            Number::make('Ставка', 'bet_amount')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Балансом', 'balance_amount')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Цена цели', 'target_price')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Шанс', 'chance'),
            Number::make('Множитель', 'multiplier'),
            Text::make('Результат', formatted: fn ($item) => $item->result?->value ?? (string) $item->result),
            Number::make('Roll', 'roll_value'),
            Text::make('Roll Hex', 'roll_hex'),
            Text::make('Client Seed', 'client_seed'),
            Text::make('Server Seed Hash', 'server_seed_hash'),
            Number::make('Nonce', 'nonce'),
            Switcher::make('Seed раскрыт', 'is_revealed'),
            Date::make('Дата', 'created_at'),
        ];
    }
}
