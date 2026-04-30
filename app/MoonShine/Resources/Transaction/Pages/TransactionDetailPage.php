<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Transaction\Pages;

use App\MoonShine\Resources\Transaction\TransactionResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<TransactionResource>
 */
class TransactionDetailPage extends DetailPage
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
            Text::make('Тип', formatted: fn ($item) => $item->type?->value ?? (string) $item->type),
            Number::make('Сумма', 'amount')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Баланс до', 'balance_before')->modifyRawValue(MoneyFormatter::field()),
            Number::make('Баланс после', 'balance_after')->modifyRawValue(MoneyFormatter::field()),
            Text::make('Reference Type', 'reference_type'),
            Text::make('Reference ID', 'reference_id'),
            Textarea::make('Описание', 'description'),
            Date::make('Дата', 'created_at'),
        ];
    }
}
