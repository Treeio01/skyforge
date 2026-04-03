<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Transaction;

use App\Models\Transaction;
use App\MoonShine\Resources\Transaction\Pages\TransactionDetailPage;
use App\MoonShine\Resources\Transaction\Pages\TransactionFormPage;
use App\MoonShine\Resources\Transaction\Pages\TransactionIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Transaction, TransactionIndexPage, TransactionFormPage, TransactionDetailPage>
 */
class TransactionResource extends ModelResource
{
    protected string $model = Transaction::class;

    protected string $title = 'Транзакции';

    protected bool $isCreatable = false;

    protected bool $isEditable = false;

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            TransactionIndexPage::class,
            TransactionFormPage::class,
            TransactionDetailPage::class,
        ];
    }
}
