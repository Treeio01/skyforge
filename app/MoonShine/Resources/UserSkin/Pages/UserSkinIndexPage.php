<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\UserSkin\Pages;

use App\Enums\UserSkinStatus;
use App\MoonShine\Resources\UserSkin\UserSkinResource;
use App\Support\Admin\MoneyFormatter;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<UserSkinResource>
 */
class UserSkinIndexPage extends IndexPage
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
            Text::make('Скин', formatted: fn ($item) => mb_substr($item->skin?->market_hash_name ?? '—', 0, 40)),
            Number::make('Цена получения', 'price_at_acquisition')
                ->modifyRawValue(MoneyFormatter::field()),
            Text::make('Источник', formatted: fn ($item) => match ($item->source?->value ?? $item->source) {
                'upgrade' => 'Апгрейд',
                'purchase' => 'Покупка',
                'admin' => 'Админ',
                default => (string) ($item->source?->value ?? $item->source),
            }),
            Text::make('Статус', formatted: fn ($item) => match ($item->status?->value ?? $item->status) {
                'available' => '✅ Доступен',
                'in_upgrade' => '🔄 В апгрейде',
                'withdrawn' => '📤 Выведен',
                'sold' => '💰 Продан',
                default => (string) ($item->status?->value ?? $item->status),
            }),
            Date::make('Выведен', 'withdrawn_at'),
            Date::make('Получен', 'created_at'),
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
            Select::make('Источник', 'source')
                ->options([
                    'upgrade' => 'Апгрейд',
                    'purchase' => 'Покупка',
                    'admin' => 'Админ',
                ])
                ->nullable(),
            Select::make('Статус', 'status')
                ->options([
                    'available' => 'Доступен',
                    'in_upgrade' => 'В апгрейде',
                    'withdrawn' => 'Выведен',
                    'sold' => 'Продан',
                ])
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
            QueryTag::make('Доступные', fn ($q) => $q->where('status', UserSkinStatus::Available->value)),
            QueryTag::make('В апгрейде', fn ($q) => $q->where('status', UserSkinStatus::InUpgrade->value)),
            QueryTag::make('Выведенные', fn ($q) => $q->where('status', UserSkinStatus::Withdrawn->value)),
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
