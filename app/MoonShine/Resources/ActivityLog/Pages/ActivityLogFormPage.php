<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\ActivityLog\Pages;

use App\MoonShine\Resources\ActivityLog\ActivityLogResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;

/**
 * @extends FormPage<ActivityLogResource>
 */
class ActivityLogFormPage extends FormPage
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
