<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\ActivityLog\Pages;

use App\MoonShine\Resources\ActivityLog\ActivityLogResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<ActivityLogResource>
 */
class ActivityLogDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Лог', 'log_name'),
            Text::make('Описание', 'description'),
            Text::make('Объект', formatted: fn ($item) => $item->subject_type ? \class_basename($item->subject_type).'#'.$item->subject_id : '—'),
            Text::make('Инициатор', formatted: fn ($item) => $item->causer_type ? \class_basename($item->causer_type).'#'.$item->causer_id : '—'),
            Textarea::make('Изменения', formatted: fn ($item) => $item->properties?->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '—'),
            Date::make('Дата', 'created_at'),
        ];
    }
}
