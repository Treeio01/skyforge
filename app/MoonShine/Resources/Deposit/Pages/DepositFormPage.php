<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Deposit\Pages;

use App\MoonShine\Resources\Deposit\DepositResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;

/**
 * @extends FormPage<DepositResource>
 */
class DepositFormPage extends FormPage
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
