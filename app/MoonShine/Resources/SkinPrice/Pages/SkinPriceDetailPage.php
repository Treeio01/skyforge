<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\SkinPrice\Pages;

use App\MoonShine\Resources\SkinPrice\SkinPriceResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;

/**
 * @extends DetailPage<SkinPriceResource>
 */
class SkinPriceDetailPage extends DetailPage
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
