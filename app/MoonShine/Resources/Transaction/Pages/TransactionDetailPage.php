<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Transaction\Pages;

use App\MoonShine\Resources\Transaction\TransactionResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\ID;

/**
 * @extends DetailPage<TransactionResource>
 */
class TransactionDetailPage extends DetailPage
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
