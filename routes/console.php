<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('skins:sync-prices')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('skyforge:sync-rates')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('horizon:snapshot')
    ->everyFiveMinutes();

Schedule::command('online:boot')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('feed:fake --once --count=6')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();
