<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public function __construct(int $required, int $available)
    {
        parent::__construct("Insufficient balance: required {$required}, available {$available}");
    }
}
