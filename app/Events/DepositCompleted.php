<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Deposit;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class DepositCompleted implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(
        public Deposit $deposit,
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->deposit->user_id)];
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->deposit->id,
            'amount' => $this->deposit->amount,
            'status' => $this->deposit->status->value,
        ];
    }
}
