<?php

declare(strict_types=1);

use App\Events\OnlineUpdated;
use App\Jobs\OnlineDriftJob;
use App\Models\Setting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Cache::forget('online.fake_state');
    Cache::forget('online.loop_heartbeat');
    Setting::set('online.enabled', true, 'boolean');
    Setting::set('online.min', 1500, 'integer');
    Setting::set('online.max', 1600, 'integer');
    Setting::set('online.tick_seconds', 8, 'integer');
    Setting::set('online.max_step', 3, 'integer');
});

it('exits early without broadcast or re-dispatch when disabled', function () {
    Setting::set('online.enabled', false, 'boolean');
    Event::fake();
    Bus::fake();

    (new OnlineDriftJob)->handle();

    Event::assertNotDispatched(OnlineUpdated::class);
    Bus::assertNotDispatched(OnlineDriftJob::class);
});

it('broadcasts and re-dispatches when enabled', function () {
    Event::fake([OnlineUpdated::class]);
    Bus::fake([OnlineDriftJob::class]);

    (new OnlineDriftJob)->handle();

    Event::assertDispatched(OnlineUpdated::class, function (OnlineUpdated $e) {
        return $e->fake >= 1500 && $e->fake <= 1600;
    });
    Bus::assertDispatched(OnlineDriftJob::class);
});

it('writes state and heartbeat to cache', function () {
    Bus::fake([OnlineDriftJob::class]);

    (new OnlineDriftJob)->handle();

    $state = Cache::get('online.fake_state');
    expect($state)->toHaveKeys(['value', 'direction']);
    expect(Cache::get('online.loop_heartbeat'))->not->toBeNull();
});

it('skips work when min >= max', function () {
    Setting::set('online.min', 1600, 'integer');
    Setting::set('online.max', 1500, 'integer');
    Event::fake();
    Bus::fake();

    (new OnlineDriftJob)->handle();

    Event::assertNotDispatched(OnlineUpdated::class);
    Bus::assertNotDispatched(OnlineDriftJob::class);
});
