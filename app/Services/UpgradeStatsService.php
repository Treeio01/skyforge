<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\UpgradeStatsUpdated;
use App\Models\Upgrade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class UpgradeStatsService
{
    private const FAKE_COUNT_KEY = 'stats:fake_upgrades_count';

    public function total(): int
    {
        return Upgrade::query()->count() + $this->fakeCount();
    }

    public function incrementFakeAndBroadcast(): int
    {
        Redis::incr(self::FAKE_COUNT_KEY);

        return $this->broadcastCurrentTotal();
    }

    public function broadcastCurrentTotal(): int
    {
        Cache::forget('site_stats');

        $total = $this->total();

        UpgradeStatsUpdated::dispatch($total);

        return $total;
    }

    private function fakeCount(): int
    {
        return (int) (Redis::get(self::FAKE_COUNT_KEY) ?: 0);
    }
}
