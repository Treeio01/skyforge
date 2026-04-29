<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\ActivityLog\Pages;

use App\MoonShine\Resources\ActivityLog\ActivityLogResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;

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
        ];
    }
}
