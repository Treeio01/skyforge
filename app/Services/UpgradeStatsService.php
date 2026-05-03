<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\UpgradeStatsUpdated;
use App\Models\Upgrade;
use Illuminate\Support\Facades\Cache;

class UpgradeStatsService
{
    public const TOTAL_CACHE_KEY = 'stats.upgrades_total';

    /** Materialised upgrade total for sidebar stats — avoids COUNT(*) on hot path. */
    public function total(): int
    {
        $cached = Cache::get(self::TOTAL_CACHE_KEY);

        if ($cached !== null) {
            return (int) $cached;
        }

        $count = Upgrade::query()->count();
        Cache::forever(self::TOTAL_CACHE_KEY, $count);

        return $count;
    }

    /**
     * Increment the persistent counter after a new upgrade row is committed,
     * clear aggregated site_stats, and broadcast the new total over Reverb.
     */
    public function syncAfterUpgradeCompleted(): void
    {
        if (! Cache::has(self::TOTAL_CACHE_KEY)) {
            Cache::forever(self::TOTAL_CACHE_KEY, Upgrade::query()->count());
            $total = (int) Cache::get(self::TOTAL_CACHE_KEY);
        } else {
            $total = (int) Cache::increment(self::TOTAL_CACHE_KEY);
        }

        Cache::forget('site_stats');
        UpgradeStatsUpdated::dispatch($total);
    }

    /**
     * Re-seed Redis counter from the database (migrations / manual repair).
     */
    public function reconcileTotalFromDatabase(): int
    {
        $total = Upgrade::query()->count();
        Cache::forever(self::TOTAL_CACHE_KEY, $total);
        Cache::forget('site_stats');
        UpgradeStatsUpdated::dispatch($total);

        return $total;
    }
}
