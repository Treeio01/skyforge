<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\UserSkin\Pages;

use App\MoonShine\Resources\UserSkin\UserSkinResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;

/**
 * @extends DetailPage<UserSkinResource>
 */
class UserSkinDetailPage extends DetailPage
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
