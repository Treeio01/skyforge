<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Withdrawal\Pages;

use App\MoonShine\Resources\Withdrawal\WithdrawalResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;

/**
 * @extends DetailPage<WithdrawalResource>
 */
class WithdrawalDetailPage extends DetailPage
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
