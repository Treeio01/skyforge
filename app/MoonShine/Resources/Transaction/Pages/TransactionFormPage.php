<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Transaction\Pages;

use App\MoonShine\Resources\Transaction\TransactionResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;

/**
 * @extends FormPage<TransactionResource>
 */
class TransactionFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [];
    }

    /**
     * @return array<string, array<string>>
     */
    protected function rules(DataWrapperContract $item): array
    {
        return [];
    }
}
