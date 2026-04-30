<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UpgradeCompleted;
use App\Services\UpgradeStatsService;

class PushToLiveFeed
{
    public function __construct(private UpgradeStatsService $upgradeStats) {}

    public function handle(UpgradeCompleted $event): void
    {
        $this->upgradeStats->broadcastCurrentTotal();
    }
}
