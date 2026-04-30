<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UpgradeCompleted;
use App\Services\UpgradeStatsService;
use Illuminate\Support\Facades\Redis;

class PushToLiveFeed
{
    public function __construct(private UpgradeStatsService $upgradeStats) {}

    public function handle(UpgradeCompleted $event): void
    {
        $data = json_encode($event->broadcastWith());

        Redis::lpush('feed:recent', $data);
        Redis::ltrim('feed:recent', 0, 49);

        $this->upgradeStats->broadcastCurrentTotal();
    }
}
