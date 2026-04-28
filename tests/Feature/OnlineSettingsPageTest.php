<?php

declare(strict_types=1);

use App\Jobs\OnlineDriftJob;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    Bus::fake([OnlineDriftJob::class]);
    Setting::set('online.enabled', false, 'boolean');
});

function adminUser(): User
{
    return User::factory()->create(['is_admin' => true]);
}

it('saves settings and cleans cache on valid input', function () {
    $this->actingAs(adminUser())
        ->post('/admin/online-settings', [
            'online_enabled' => '1',
            'online_min' => '1000',
            'online_max' => '2000',
            'online_tick_seconds' => '10',
            'online_max_step' => '5',
        ])->assertRedirect();

    expect(Setting::get('online.enabled'))->toBeTrue();
    expect(Setting::get('online.min'))->toBe(1000);
    expect(Setting::get('online.max'))->toBe(2000);
});

it('rejects min greater than max', function () {
    $this->actingAs(adminUser())
        ->post('/admin/online-settings', [
            'online_enabled' => '0',
            'online_min' => '2000',
            'online_max' => '1000',
            'online_tick_seconds' => '8',
            'online_max_step' => '3',
        ])->assertSessionHasErrors(['online_max']);
});

it('dispatches drift job when enabled toggles false to true', function () {
    Bus::fake([OnlineDriftJob::class]);

    $this->actingAs(adminUser())
        ->post('/admin/online-settings', [
            'online_enabled' => '1',
            'online_min' => '1500',
            'online_max' => '1600',
            'online_tick_seconds' => '8',
            'online_max_step' => '3',
        ]);

    Bus::assertDispatched(OnlineDriftJob::class);
});

it('does not dispatch when enabled stays true', function () {
    Setting::set('online.enabled', true, 'boolean');
    Bus::fake([OnlineDriftJob::class]);

    $this->actingAs(adminUser())
        ->post('/admin/online-settings', [
            'online_enabled' => '1',
            'online_min' => '1500',
            'online_max' => '1600',
            'online_tick_seconds' => '8',
            'online_max_step' => '3',
        ]);

    Bus::assertNotDispatched(OnlineDriftJob::class);
});

it('reset endpoint clears fake_state cache', function () {
    Cache::put('online.fake_state', ['value' => 1234, 'direction' => 1], 60);

    $this->actingAs(adminUser())
        ->post('/admin/online-settings/reset')
        ->assertRedirect();

    expect(Cache::get('online.fake_state'))->toBeNull();
});
