<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\OnlineDriftJob;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class OnlineLoopBootCommand extends Command
{
    protected $signature = 'online:boot';

    protected $description = 'Boot the online drift loop if it is enabled and not already running';

    public function handle(): int
    {
        if (! Setting::get('online.enabled', false)) {
            $this->info('Online accuracy disabled. Skip.');

            return self::SUCCESS;
        }

        if (Cache::get('online.loop_heartbeat') !== null) {
            $this->info('Loop is already running. Skip.');

            return self::SUCCESS;
        }

        OnlineDriftJob::dispatch()->onQueue('online');
        $this->info('Online drift loop dispatched.');

        return self::SUCCESS;
    }
}
