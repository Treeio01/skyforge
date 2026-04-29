<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Skin\Pages;

use App\MoonShine\Resources\Skin\SkinResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;

/**
 * @extends DetailPage<SkinResource>
 */
class SkinDetailPage extends DetailPage
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
