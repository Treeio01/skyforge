<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\FaqItemMoon\Pages;

use App\MoonShine\Resources\FaqItemMoon\FaqItemMoonResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;

/**
 * @extends DetailPage<FaqItemMoonResource>
 */
class FaqItemMoonDetailPage extends DetailPage
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
