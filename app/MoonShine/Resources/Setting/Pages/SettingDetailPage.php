<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Setting\Pages;

use App\MoonShine\Resources\Setting\SettingResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends DetailPage<SettingResource>
 */
class SettingDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Key', 'key'),
            Text::make('Value', 'value'),
            Text::make('Type', 'type'),
            Textarea::make('Description', 'description'),
            Date::make('Updated', 'updated_at'),
        ];
    }
}
