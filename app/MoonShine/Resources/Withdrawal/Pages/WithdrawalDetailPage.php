<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Withdrawal\Pages;

use App\MoonShine\Resources\Withdrawal\WithdrawalResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<WithdrawalResource>
 */
class WithdrawalDetailPage extends DetailPage
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
            Number::make('Сумма', 'amount')->modifyRawValue(MoneyFormatter::field()),
            Text::make('Статус', formatted: fn ($item) => $item->status?->value ?? (string) $item->status),
            Text::make('Trade Offer', 'trade_offer_id'),
            Text::make('Trade Offer Status', 'trade_offer_status'),
            Textarea::make('Причина ошибки', 'failure_reason'),
            Date::make('Завершён', 'completed_at'),
            Date::make('Создан', 'created_at'),
        ];
    }
}
