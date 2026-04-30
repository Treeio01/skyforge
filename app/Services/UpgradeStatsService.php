<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\UpgradeStatsUpdated;
use App\Models\Upgrade;
use Illuminate\Support\Facades\Cache;

class UpgradeStatsService
{
    public function total(): int
    {
        return Upgrade::query()->count();
    }

    public function broadcastCurrentTotal(): int
    {
        Cache::forget('site_stats');

        $total = $this->total();

        UpgradeStatsUpdated::dispatch($total);

        return $total;
    }
}
