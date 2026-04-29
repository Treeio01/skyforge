<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Upgrade\Pages;

use App\MoonShine\Resources\Upgrade\UpgradeResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;

/**
 * @extends DetailPage<UpgradeResource>
 */
class UpgradeDetailPage extends DetailPage
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
