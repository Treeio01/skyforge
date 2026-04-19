<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Deposit;

use App\Models\Deposit;
use App\MoonShine\Resources\Deposit\Pages\DepositDetailPage;
use App\MoonShine\Resources\Deposit\Pages\DepositFormPage;
use App\MoonShine\Resources\Deposit\Pages\DepositIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Deposit, DepositIndexPage, DepositFormPage, DepositDetailPage>
 */
class DepositResource extends ModelResource
{
    protected string $model = Deposit::class;

    protected string $title = 'Депозиты';

    protected array $with = ['user'];

    protected bool $isCreatable = false;

    protected bool $isEditable = false;

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            DepositIndexPage::class,
            DepositFormPage::class,
            DepositDetailPage::class,
        ];
    }
}
