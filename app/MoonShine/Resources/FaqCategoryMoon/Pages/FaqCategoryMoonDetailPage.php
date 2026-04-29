<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqCategoryMoon\Pages;

use App\MoonShine\Resources\FaqCategoryMoon\FaqCategoryMoonResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;

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
        ];
    }
}
