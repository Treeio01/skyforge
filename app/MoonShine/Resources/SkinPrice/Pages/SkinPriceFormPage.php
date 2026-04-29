<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\SkinPrice\Pages;

use App\MoonShine\Resources\SkinPrice\SkinPriceResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;

/**
 * @extends FormPage<SkinPriceResource>
 */
class SkinPriceFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
            ]),
        ];
    }
}
