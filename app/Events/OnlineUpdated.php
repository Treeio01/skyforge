<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class OnlineUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public int $fake) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel('stats')];
    }

    public function broadcastAs(): string
    {
        return 'online.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['fake' => $this->fake];
    }
}
