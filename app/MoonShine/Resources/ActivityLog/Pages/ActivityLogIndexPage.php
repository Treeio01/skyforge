<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\ActivityLog\Pages;

use App\MoonShine\Resources\ActivityLog\ActivityLogResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<ActivityLogResource>
 */
class ActivityLogIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Лог', 'log_name'),
            Text::make('Описание', 'description'),
            Text::make('Объект', formatted: fn ($item) => $item->subject_type
                ? \class_basename($item->subject_type).'#'.$item->subject_id
                : '—'),
            Text::make('Инициатор', formatted: fn ($item) => $item->causer_type
                ? \class_basename($item->causer_type).'#'.$item->causer_id
                : 'система'),
            Text::make('Изменения', formatted: fn ($item) => $item->properties?->isNotEmpty()
                ? \mb_substr($item->properties->toJson(), 0, 100)
                : '—'),
            Date::make('Дата', 'created_at'),
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
            Select::make('Лог', 'log_name')
                ->options([
                    'default' => 'default',
                    'user' => 'user',
                    'deposit' => 'deposit',
                    'withdrawal' => 'withdrawal',
                    'promo_code' => 'promo_code',
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
