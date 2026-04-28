<?php

declare(strict_types=1);

use App\Jobs\OnlineDriftJob;
use App\Models\Setting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Cache::forget('online.loop_heartbeat');
    Setting::set('online.enabled', true, 'boolean');
});

it('does nothing when disabled', function () {
    Setting::set('online.enabled', false, 'boolean');
    Bus::fake();

    $this->artisan('online:boot')->assertExitCode(0);

    Bus::assertNotDispatched(OnlineDriftJob::class);
});

it('dispatches drift job when no heartbeat present', function () {
    Bus::fake();

    $this->artisan('online:boot')->assertExitCode(0);

    Bus::assertDispatched(OnlineDriftJob::class);
});

it('does nothing when heartbeat is fresh', function () {
    Cache::put('online.loop_heartbeat', now()->timestamp, 60);
    Bus::fake();

    $this->artisan('online:boot')->assertExitCode(0);

    Bus::assertNotDispatched(OnlineDriftJob::class);
});
