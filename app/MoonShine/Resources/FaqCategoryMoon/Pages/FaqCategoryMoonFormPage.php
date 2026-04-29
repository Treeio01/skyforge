<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqCategoryMoon\Pages;

use App\MoonShine\Resources\FaqCategoryMoon\FaqCategoryMoonResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends FormPage<FaqCategoryMoonResource>
 */
class FaqCategoryMoonFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Slug', 'slug')->required()->hint('Латиница, например: provably, upgrade, deposit'),
                Text::make('Название', 'name')->required(),
                Number::make('Порядок', 'sort_order')->hint('Меньше = выше'),
                Switcher::make('Активна', 'is_active'),
            ]),
        ];
    }
}
