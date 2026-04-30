<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqCategoryMoon\Pages;

use App\MoonShine\Resources\FaqCategoryMoon\FaqCategoryMoonResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<FaqCategoryMoonResource>
 */
class FaqCategoryMoonDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Slug', 'slug'),
            Text::make('Название', 'name'),
            Number::make('Порядок', 'sort_order'),
            Switcher::make('Активна', 'is_active'),
            Date::make('Создана', 'created_at'),
            Date::make('Обновлена', 'updated_at'),
        ];
    }
}
