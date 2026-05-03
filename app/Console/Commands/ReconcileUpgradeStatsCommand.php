<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\UpgradeStatsService;
use Illuminate\Console\Command;

class ReconcileUpgradeStatsCommand extends Command
{
    protected $signature = 'upgrade:stats-reconcile';

    protected $description = 'Reseed the Redis materialised upgrades total counter from the database';

    public function handle(UpgradeStatsService $stats): int
    {
        $total = $stats->reconcileTotalFromDatabase();
        $this->info("upgrade total counter synced to {$total}.");

        return self::SUCCESS;
    }
}
