<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Deposit\Pages;

use App\MoonShine\Resources\Deposit\DepositResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<DepositResource>
 */
class DepositDetailPage extends DetailPage
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
            Text::make('Метод', formatted: fn ($item) => $item->method?->value ?? (string) $item->method),
            Number::make('Сумма', 'amount')->modifyRawValue(MoneyFormatter::field()),
            Text::make('Статус', formatted: fn ($item) => $item->status?->value ?? (string) $item->status),
            Text::make('Provider ID', 'provider_id'),
            Text::make('Idempotency Key', 'idempotency_key'),
            Textarea::make('Provider Data', formatted: fn ($item) => json_encode($item->provider_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '—'),
            Date::make('Завершён', 'completed_at'),
            Date::make('Создан', 'created_at'),
        ];
    }
}
