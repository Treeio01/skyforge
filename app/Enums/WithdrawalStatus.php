<?php

declare(strict_types=1);

namespace App\Enums;

enum WithdrawalStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Sent = 'sent';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
