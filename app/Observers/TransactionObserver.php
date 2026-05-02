<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Transaction;
use RuntimeException;

/**
 * Transactions form an immutable ledger. Once written, a row cannot be
 * updated or deleted — any code that needs a correction must insert a
 * compensating Transaction with the opposite sign.
 */
class TransactionObserver
{
    public function updating(Transaction $transaction): bool
    {
        throw new RuntimeException('Transactions are immutable: cannot update id='.$transaction->id);
    }

    public function deleting(Transaction $transaction): bool
    {
        throw new RuntimeException('Transactions are immutable: cannot delete id='.$transaction->id);
    }
}
