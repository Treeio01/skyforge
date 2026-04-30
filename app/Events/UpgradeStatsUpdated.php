<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class UpgradeStatsUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public int $totalUpgrades) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel('stats')];
    }

    public function broadcastAs(): string
    {
        return 'upgrades.updated';
    }

    /** @return array<string, int> */
    public function broadcastWith(): array
    {
        return ['total_upgrades' => $this->totalUpgrades];
    }
}
