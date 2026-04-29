<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Deposit\Pages;

use App\MoonShine\Resources\Deposit\DepositResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;

/**
 * @extends DetailPage<DepositResource>
 */
class DepositDetailPage extends DetailPage
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
