# Online Counter Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build admin-configurable online counter (`real + fake`) with smooth socket-driven drift across all clients.

**Architecture:** Self-rescheduling Horizon job mutates `fake` value in Redis cache and broadcasts via Reverb. Inertia provides initial values for real and fake; frontend hook subscribes to Reverb for live updates and animates the counter.

**Tech Stack:** Laravel 13, Inertia, React 18, MoonShine, Reverb, Horizon, Pest 4, Echo, Tailwind v4.

**Spec:** [docs/superpowers/specs/2026-04-28-online-counter-design.md](../specs/2026-04-28-online-counter-design.md)

---

## File Structure

**Create:**
- `database/migrations/2026_04_28_120000_seed_online_settings.php` — insert default `settings` rows with proper `type` column.
- `app/Events/OnlineUpdated.php` — broadcast event on channel `stats`, name `online.updated`.
- `app/Jobs/OnlineDriftJob.php` — self-rescheduling drift job, queue `online`.
- `app/Console/Commands/OnlineLoopBoot.php` — safety-net command (artisan `online:boot`).
- `app/MoonShine/Pages/OnlineSettingsPage.php` — admin page under «Настройки сайта → Онлайн».
- `resources/js/hooks/useOnlineCount.ts` — React hook subscribing to Reverb.
- `tests/Unit/OnlineDriftJobTest.php` — pure-function drift tests.
- `tests/Feature/OnlineSettingsPageTest.php` — admin form tests.
- `tests/Feature/OnlineInertiaShareTest.php` — Inertia share contract test.
- `tests/Feature/OnlineLoopBootTest.php` — boot command test.

**Modify:**
- `app/Http/Middleware/HandleInertiaRequests.php:55-58` — replace `online` with `online_real / online_fake_initial / online_enabled`.
- `app/Models/Setting.php` — add `set()` overload accepting `type` for backward-safe updates.
- `app/Providers/MoonShineServiceProvider.php` — register `OnlineSettingsPage` in `pages([])`.
- `config/horizon.php` — add `online` to supervisor queues.
- `routes/console.php` (or `app/Console/Kernel.php`) — `online:boot` hourly.
- `resources/js/types/index.ts` — extend `PageProps['stats']`.
- `resources/js/Components/Layout/Header/index.tsx:25-27` — replace `stats?.online` with `useOnlineCount()`.
- `CLAUDE.md` — append section about online counter (one paragraph).

---

## Phase 1 — Settings & defaults

### Task 1: Seed migration for online settings

**Files:**
- Create: `database/migrations/2026_04_28_120000_seed_online_settings.php`

- [ ] **Step 1: Create migration file**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'online.enabled', 'value' => '0', 'type' => 'boolean', 'description' => 'Включить накрутку онлайна'],
            ['key' => 'online.min', 'value' => '1500', 'type' => 'integer', 'description' => 'Минимум фейкового онлайна'],
            ['key' => 'online.max', 'value' => '1600', 'type' => 'integer', 'description' => 'Максимум фейкового онлайна'],
            ['key' => 'online.tick_seconds', 'value' => '8', 'type' => 'integer', 'description' => 'Период обновления онлайна (сек)'],
            ['key' => 'online.max_step', 'value' => '3', 'type' => 'integer', 'description' => 'Максимальный шаг дрейфа онлайна'],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(['key' => $row['key']], $row + ['updated_at' => now()]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'online.enabled',
            'online.min',
            'online.max',
            'online.tick_seconds',
            'online.max_step',
        ])->delete();
    }
};
```

- [ ] **Step 2: Run migration**

```bash
php artisan migrate
```

Expected: `INFO  Running migrations.` and one new migration line.

- [ ] **Step 3: Verify defaults**

```bash
php artisan tinker --execute 'echo \App\Models\Setting::get("online.enabled") === false ? "ok" : "fail"; echo PHP_EOL; echo \App\Models\Setting::get("online.min") === 1500 ? "ok" : "fail";'
```

Expected: `ok\nok`.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_04_28_120000_seed_online_settings.php
git commit -m "feat(online): seed default settings rows"
```

---

### Task 2: Setting::set() should preserve type

**Why:** Current `Setting::set()` writes only `value` and `updated_at`, so type is set on row creation only. After admin form updates, `type` stays as it was (good). But to be explicit, harden `set()` to optionally accept type.

**Files:**
- Modify: `app/Models/Setting.php` (add optional `?string $type` parameter to `set`)

- [ ] **Step 1: Add failing test for type preservation**

Create `tests/Unit/SettingTypeTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Setting;

it('preserves type when updating existing setting', function () {
    Setting::create(['key' => 'foo.bar', 'value' => '42', 'type' => 'integer']);

    Setting::set('foo.bar', 99);

    $row = Setting::where('key', 'foo.bar')->first();
    expect($row->type)->toBe('integer');
    expect(Setting::get('foo.bar'))->toBe(99);
});

it('accepts explicit type on set', function () {
    Setting::set('flag.x', true, 'boolean');

    $row = Setting::where('key', 'flag.x')->first();
    expect($row->type)->toBe('boolean');
    expect(Setting::get('flag.x'))->toBeTrue();
});
```

- [ ] **Step 2: Run test, expect failures**

```bash
php artisan test --filter SettingTypeTest
```

Expected: 2 failures (second test calls `set` with 3 args; current signature accepts 2).

- [ ] **Step 3: Update `Setting::set` signature**

In `app/Models/Setting.php`, replace `set` method:

```php
public static function set(string $key, mixed $value, ?string $type = null): void
{
    $attrs = ['value' => static::stringify($value), 'updated_at' => now()];
    if ($type !== null) {
        $attrs['type'] = $type;
    }

    static::updateOrCreate(['key' => $key], $attrs);

    Cache::forget("settings.{$key}");
}

private static function stringify(mixed $value): string
{
    return match (true) {
        is_bool($value) => $value ? '1' : '0',
        is_array($value) => json_encode($value),
        default => (string) $value,
    };
}
```

- [ ] **Step 4: Run test again**

```bash
php artisan test --filter SettingTypeTest
```

Expected: 2 passed.

- [ ] **Step 5: Commit**

```bash
git add app/Models/Setting.php tests/Unit/SettingTypeTest.php
git commit -m "feat(settings): preserve type on update; allow explicit type"
```

---

## Phase 2 — Event & Job (TDD)

### Task 3: OnlineUpdated broadcast event

**Files:**
- Create: `app/Events/OnlineUpdated.php`

- [ ] **Step 1: Create event class**

```php
<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class OnlineUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public int $fake) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel('stats')];
    }

    public function broadcastAs(): string
    {
        return 'online.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['fake' => $this->fake];
    }
}
```

- [ ] **Step 2: Smoke-test in tinker**

```bash
php artisan tinker --execute 'event(new \App\Events\OnlineUpdated(1500)); echo "ok";'
```

Expected: `ok` (no exception). Reverb might log a broadcast; ignore.

- [ ] **Step 3: Commit**

```bash
git add app/Events/OnlineUpdated.php
git commit -m "feat(online): add OnlineUpdated broadcast event"
```

---

### Task 4: OnlineDriftJob — pure drift logic (unit test)

The drift logic is pure and easy to unit-test in isolation. Extract it to a static method, test that.

**Files:**
- Create: `app/Jobs/OnlineDriftJob.php` (skeleton with `static computeNext`)
- Create: `tests/Unit/OnlineDriftJobTest.php`

- [ ] **Step 1: Write failing unit test**

`tests/Unit/OnlineDriftJobTest.php`:

```php
<?php

declare(strict_types=1);

use App\Jobs\OnlineDriftJob;

it('drifts within bounds', function () {
    $state = ['value' => 1550, 'direction' => 1];
    for ($i = 0; $i < 200; $i++) {
        $state = OnlineDriftJob::computeNext($state, 1500, 1600, 3);
        expect($state['value'])->toBeGreaterThanOrEqual(1500);
        expect($state['value'])->toBeLessThanOrEqual(1600);
    }
});

it('flips direction at upper bound', function () {
    $state = ['value' => 1599, 'direction' => 1];
    $next = OnlineDriftJob::computeNext($state, 1500, 1600, 3);
    expect($next['value'])->toBe(1600);
    expect($next['direction'])->toBe(-1);
});

it('flips direction at lower bound', function () {
    $state = ['value' => 1501, 'direction' => -1];
    $next = OnlineDriftJob::computeNext($state, 1500, 1600, 3);
    expect($next['value'])->toBe(1500);
    expect($next['direction'])->toBe(1);
});

it('initializes within range when state is null', function () {
    $state = OnlineDriftJob::initState(1500, 1600);
    expect($state['value'])->toBeGreaterThanOrEqual(1500);
    expect($state['value'])->toBeLessThanOrEqual(1600);
    expect($state['direction'])->toBeIn([-1, 1]);
});
```

- [ ] **Step 2: Run, expect failures**

```bash
php artisan test --filter OnlineDriftJobTest
```

Expected: errors about `OnlineDriftJob` class not found.

- [ ] **Step 3: Create job skeleton with `computeNext` and `initState`**

`app/Jobs/OnlineDriftJob.php`:

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OnlineDriftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(): void
    {
        // populated in next tasks
    }

    /**
     * Pure: next drift step within [min, max], with occasional direction flip.
     *
     * @param  array{value:int,direction:int}  $state
     * @return array{value:int,direction:int}
     */
    public static function computeNext(array $state, int $min, int $max, int $maxStep): array
    {
        $direction = $state['direction'];

        // 15% случайных разворотов для естественного движения
        if (random_int(0, 99) < 15) {
            $direction = -$direction;
        }

        $step = random_int(1, $maxStep) * $direction;
        $value = max($min, min($max, $state['value'] + $step));

        if ($value === $min) {
            $direction = 1;
        } elseif ($value === $max) {
            $direction = -1;
        }

        return ['value' => $value, 'direction' => $direction];
    }

    /** @return array{value:int,direction:int} */
    public static function initState(int $min, int $max): array
    {
        return [
            'value' => random_int($min, $max),
            'direction' => random_int(0, 1) === 0 ? -1 : 1,
        ];
    }
}
```

- [ ] **Step 4: Run, expect 4 passed**

```bash
php artisan test --filter OnlineDriftJobTest
```

Expected: 4 passed.

- [ ] **Step 5: Commit**

```bash
git add app/Jobs/OnlineDriftJob.php tests/Unit/OnlineDriftJobTest.php
git commit -m "feat(online): drift algorithm with bounded random walk"
```

---

### Task 5: OnlineDriftJob handle() — integration

**Files:**
- Modify: `app/Jobs/OnlineDriftJob.php` (fill in `handle()`)
- Create: `tests/Feature/OnlineDriftJobIntegrationTest.php`

- [ ] **Step 1: Write feature test**

```php
<?php

declare(strict_types=1);

use App\Events\OnlineUpdated;
use App\Jobs\OnlineDriftJob;
use App\Models\Setting;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

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

    (new OnlineDriftJob())->handle();

    Event::assertNotDispatched(OnlineUpdated::class);
    Bus::assertNotDispatched(OnlineDriftJob::class);
});

it('broadcasts and re-dispatches when enabled', function () {
    Event::fake([OnlineUpdated::class]);
    Bus::fake([OnlineDriftJob::class]);

    (new OnlineDriftJob())->handle();

    Event::assertDispatched(OnlineUpdated::class, function (OnlineUpdated $e) {
        return $e->fake >= 1500 && $e->fake <= 1600;
    });
    Bus::assertDispatched(OnlineDriftJob::class);
});

it('writes state and heartbeat to cache', function () {
    Bus::fake([OnlineDriftJob::class]);

    (new OnlineDriftJob())->handle();

    $state = Cache::get('online.fake_state');
    expect($state)->toHaveKeys(['value', 'direction']);
    expect(Cache::get('online.loop_heartbeat'))->not->toBeNull();
});

it('skips work when min >= max', function () {
    Setting::set('online.min', 1600, 'integer');
    Setting::set('online.max', 1500, 'integer');
    Event::fake();
    Bus::fake();

    (new OnlineDriftJob())->handle();

    Event::assertNotDispatched(OnlineUpdated::class);
    Bus::assertNotDispatched(OnlineDriftJob::class);
});
```

- [ ] **Step 2: Run, expect failures**

```bash
php artisan test --filter OnlineDriftJobIntegrationTest
```

Expected: 4 failures (handle() empty).

- [ ] **Step 3: Implement handle()**

In `app/Jobs/OnlineDriftJob.php`, replace `handle()` body:

```php
public function handle(): void
{
    if (! \App\Models\Setting::get('online.enabled', false)) {
        return;
    }

    $tickDefault = 8;
    $lock = \Illuminate\Support\Facades\Cache::lock('online.loop', $tickDefault * 2);
    if (! $lock->get()) {
        return;
    }

    try {
        $min = (int) \App\Models\Setting::get('online.min', 1500);
        $max = (int) \App\Models\Setting::get('online.max', 1600);
        $tick = (int) \App\Models\Setting::get('online.tick_seconds', 8);
        $maxStep = (int) \App\Models\Setting::get('online.max_step', 3);

        if ($min >= $max) {
            \Illuminate\Support\Facades\Log::warning('online drift: min >= max, skipping', compact('min', 'max'));
            return;
        }

        \Illuminate\Support\Facades\Cache::put('online.loop_heartbeat', now()->timestamp, $tick * 3);

        $state = \Illuminate\Support\Facades\Cache::get('online.fake_state') ?? self::initState($min, $max);
        $state = self::computeNext($state, $min, $max, $maxStep);

        \Illuminate\Support\Facades\Cache::put('online.fake_state', $state, $tick * 5);

        event(new \App\Events\OnlineUpdated($state['value']));
    } finally {
        $lock->release();
    }

    self::dispatch()->onQueue('online')->delay(now()->addSeconds($tick));
}
```

- [ ] **Step 4: Run, expect 4 passed**

```bash
php artisan test --filter OnlineDriftJobIntegrationTest
```

Expected: 4 passed.

- [ ] **Step 5: Commit**

```bash
git add app/Jobs/OnlineDriftJob.php tests/Feature/OnlineDriftJobIntegrationTest.php
git commit -m "feat(online): drift job handle() with cache state and broadcast"
```

---

## Phase 3 — Boot command

### Task 6: OnlineLoopBoot artisan command

**Files:**
- Create: `app/Console/Commands/OnlineLoopBoot.php`
- Create: `tests/Feature/OnlineLoopBootTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

declare(strict_types=1);

use App\Jobs\OnlineDriftJob;
use App\Models\Setting;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

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
```

- [ ] **Step 2: Run, expect command-not-found error**

```bash
php artisan test --filter OnlineLoopBootTest
```

Expected: 3 failures with "Command 'online:boot' is not defined".

- [ ] **Step 3: Create command**

`app/Console/Commands/OnlineLoopBoot.php`:

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\OnlineDriftJob;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class OnlineLoopBoot extends Command
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
```

- [ ] **Step 4: Run, expect 3 passed**

```bash
php artisan test --filter OnlineLoopBootTest
```

Expected: 3 passed.

- [ ] **Step 5: Commit**

```bash
git add app/Console/Commands/OnlineLoopBoot.php tests/Feature/OnlineLoopBootTest.php
git commit -m "feat(online): add online:boot safety-net command"
```

---

### Task 7: Schedule online:boot hourly

**Files:**
- Modify: `routes/console.php`

- [ ] **Step 1: Check current console routes**

```bash
cat routes/console.php
```

Note current schedule entries.

- [ ] **Step 2: Add hourly schedule**

Append to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('online:boot')->hourly()->withoutOverlapping();
```

(If file already imports `Schedule`, do not duplicate the `use` line.)

- [ ] **Step 3: Verify schedule list**

```bash
php artisan schedule:list
```

Expected: line containing `online:boot` running `hourly`.

- [ ] **Step 4: Commit**

```bash
git add routes/console.php
git commit -m "feat(online): schedule online:boot hourly safety-net"
```

---

## Phase 4 — Admin page

### Task 8: OnlineSettingsPage skeleton with form rendering

**Files:**
- Create: `app/MoonShine/Pages/OnlineSettingsPage.php`
- Modify: `app/Providers/MoonShineServiceProvider.php`

- [ ] **Step 1: Create the page**

```php
<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Setting;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

class OnlineSettingsPage extends Page
{
    public function getTitle(): string
    {
        return 'Онлайн на сайте';
    }

    public function getBreadcrumbs(): array
    {
        return ['#' => 'Настройки сайта', '' => 'Онлайн'];
    }

    /** @return list<ComponentContract> */
    protected function components(): iterable
    {
        $values = [
            'online_enabled' => (bool) Setting::get('online.enabled', false),
            'online_min' => (int) Setting::get('online.min', 1500),
            'online_max' => (int) Setting::get('online.max', 1600),
            'online_tick_seconds' => (int) Setting::get('online.tick_seconds', 8),
            'online_max_step' => (int) Setting::get('online.max_step', 3),
        ];

        return [
            Box::make('Накрутка', [
                Switcher::make('Включить накрутку', 'online_enabled')
                    ->hint('Если выключено, в шапке показывается только реальное число активных пользователей.'),
            ]),
            Box::make('Диапазон', [
                Number::make('Минимум', 'online_min')->min(0)->required(),
                Number::make('Максимум', 'online_max')->min(1)->required(),
            ]),
            Box::make('Поведение', [
                Number::make('Частота обновления (сек)', 'online_tick_seconds')->min(3)->max(60)->required(),
                Number::make('Максимальный шаг', 'online_max_step')->min(1)->max(10)->required(),
            ]),
            FormBuilder::make(route('moonshine.online.save'))
                ->fillCast($values, null)
                ->submit('Сохранить')
                ->async(),
        ];
    }
}
```

(Note: actual MoonShine API for forms in a custom page may need slight adjustment — see existing `DashboardPage.php` pattern for `Box`/`Column` use. If `FormBuilder::make(route())` shape differs, follow what MoonShine docs accept; test below catches mismatches.)

- [ ] **Step 2: Register page in MoonShineServiceProvider**

In `app/Providers/MoonShineServiceProvider.php`, replace `->pages([])` with:

```php
->pages([
    \App\MoonShine\Pages\OnlineSettingsPage::class,
]);
```

- [ ] **Step 3: Verify page route exists**

```bash
php artisan route:list --path=admin | grep -i online
```

Expected: line with `online-settings-page` or similar from MoonShine auto-routing.

- [ ] **Step 4: Commit**

```bash
git add app/MoonShine/Pages/OnlineSettingsPage.php app/Providers/MoonShineServiceProvider.php
git commit -m "feat(admin): online settings MoonShine page skeleton"
```

---

### Task 9: Admin save endpoint with validation and side-effects

**Files:**
- Create: `app/Http/Controllers/Admin/OnlineSettingsController.php`
- Modify: `routes/web.php` (or admin routes file) — add `moonshine.online.save` route
- Create: `tests/Feature/OnlineSettingsPageTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

declare(strict_types=1);

use App\Jobs\OnlineDriftJob;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
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
```

- [ ] **Step 2: Run, expect failures**

```bash
php artisan test --filter OnlineSettingsPageTest
```

Expected: 5 failures (no route, no controller).

- [ ] **Step 3: Create controller**

`app/Http/Controllers/Admin/OnlineSettingsController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\OnlineDriftJob;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class OnlineSettingsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'online_enabled' => ['required', 'boolean'],
            'online_min' => ['required', 'integer', 'min:0'],
            'online_max' => ['required', 'integer', 'min:1', 'gt:online_min'],
            'online_tick_seconds' => ['required', 'integer', 'min:3', 'max:60'],
            'online_max_step' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $wasEnabled = (bool) Setting::get('online.enabled', false);

        Setting::set('online.enabled', (bool) $data['online_enabled'], 'boolean');
        Setting::set('online.min', (int) $data['online_min'], 'integer');
        Setting::set('online.max', (int) $data['online_max'], 'integer');
        Setting::set('online.tick_seconds', (int) $data['online_tick_seconds'], 'integer');
        Setting::set('online.max_step', (int) $data['online_max_step'], 'integer');

        Cache::forget('online.fake_state');

        if (! $wasEnabled && (bool) $data['online_enabled']) {
            OnlineDriftJob::dispatch()->onQueue('online');
        }

        return back()->with('success', 'Настройки применены, обновляются на всех клиентах');
    }

    public function reset(): RedirectResponse
    {
        Cache::forget('online.fake_state');
        return back()->with('success', 'Текущее значение сброшено');
    }
}
```

- [ ] **Step 4: Add routes**

In `routes/web.php`, add inside an admin-protected group (or use existing admin middleware structure — check `routes/web.php` for how `/admin` is wired and place these consistently):

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('moonshine.online.')->group(function () {
    Route::post('online-settings', [\App\Http\Controllers\Admin\OnlineSettingsController::class, 'update'])->name('save');
    Route::post('online-settings/reset', [\App\Http\Controllers\Admin\OnlineSettingsController::class, 'reset'])->name('reset');
});
```

(If MoonShine admin uses its own middleware/group, follow that — open `routes/web.php` and `app/Http/Middleware` to see what guards admin endpoints. The test uses `actingAs($admin)` so it bypasses the guard layer details, but in production routes must be properly protected.)

- [ ] **Step 5: Run, expect 5 passed**

```bash
php artisan test --filter OnlineSettingsPageTest
```

Expected: 5 passed.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/OnlineSettingsController.php routes/web.php tests/Feature/OnlineSettingsPageTest.php
git commit -m "feat(admin): online settings save endpoint with validation"
```

---

### Task 10: Wire form action to controller

**Files:**
- Modify: `app/MoonShine/Pages/OnlineSettingsPage.php`

- [ ] **Step 1: Update FormBuilder action and add reset button**

In the page's `components()`, change `FormBuilder::make(...)` to use the named route, and add a separate small form for reset:

```php
FormBuilder::make(route('moonshine.online.save'))
    ->fillCast($values, null)
    ->submit('Сохранить')
    ->async(),

FormBuilder::make(route('moonshine.online.reset'))
    ->submit('Сбросить и пересчитать')
    ->async(),
```

- [ ] **Step 2: Manually verify in browser**

```bash
make dev
```

Open `/admin/page/online-settings-page` (or whatever auto-route MoonShine generates). Toggle, change values, submit. Check toast.

(No automated test for the page render itself — the controller test above covers the submit path.)

- [ ] **Step 3: Commit**

```bash
git add app/MoonShine/Pages/OnlineSettingsPage.php
git commit -m "feat(admin): wire online settings form to save/reset routes"
```

---

## Phase 5 — Frontend integration

### Task 11: Inertia share — extend stats

**Files:**
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`
- Create: `tests/Feature/OnlineInertiaShareTest.php`

- [ ] **Step 1: Write failing contract test**

```php
<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('shares online_real, online_fake_initial, online_enabled', function () {
    Setting::set('online.enabled', true, 'boolean');
    Cache::put('online.fake_state', ['value' => 1555, 'direction' => 1], 60);

    User::factory()->count(3)->create(['last_active_at' => now()->subMinute()]);

    $this->get('/')->assertInertia(fn ($page) => $page
        ->where('stats.online_real', 3)
        ->where('stats.online_fake_initial', 1555)
        ->where('stats.online_enabled', true)
    );
});

it('online_fake_initial defaults to 0 if cache empty', function () {
    Setting::set('online.enabled', true, 'boolean');

    $this->get('/')->assertInertia(fn ($page) => $page
        ->where('stats.online_fake_initial', 0)
    );
});

it('online_enabled false when setting disabled', function () {
    Setting::set('online.enabled', false, 'boolean');

    $this->get('/')->assertInertia(fn ($page) => $page
        ->where('stats.online_enabled', false)
    );
});
```

- [ ] **Step 2: Run, expect failures**

```bash
php artisan test --filter OnlineInertiaShareTest
```

Expected: 3 failures (`stats.online_real` doesn't exist).

- [ ] **Step 3: Modify share()**

In `app/Http/Middleware/HandleInertiaRequests.php`, replace the `'stats'` block:

```php
'stats' => Cache::remember('site_stats', 30, fn () => [
    'online_real' => User::where('last_active_at', '>=', now()->subMinutes(5))->count(),
    'online_fake_initial' => Cache::get('online.fake_state')['value'] ?? 0,
    'online_enabled' => (bool) Setting::get('online.enabled', false),
    'total_upgrades' => Upgrade::count(),
]),
```

(Make sure `use App\Models\Setting;` import is already present — it is from socials block.)

- [ ] **Step 4: Run, expect 3 passed**

```bash
php artisan test --filter OnlineInertiaShareTest
```

Expected: 3 passed.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Middleware/HandleInertiaRequests.php tests/Feature/OnlineInertiaShareTest.php
git commit -m "feat(online): inertia share online_real/online_fake_initial/online_enabled"
```

---

### Task 12: Update PageProps types

**Files:**
- Modify: `resources/js/types/index.ts`

- [ ] **Step 1: Find current `stats` shape**

```bash
grep -n "online\|total_upgrades\|stats:" resources/js/types/index.ts
```

- [ ] **Step 2: Update interface**

In `resources/js/types/index.ts`, replace `stats` definition (within `PageProps` or shared types):

```typescript
stats: {
    online_real: number;
    online_fake_initial: number;
    online_enabled: boolean;
    total_upgrades: number;
};
```

(If old `online: number` is referenced elsewhere on the frontend, search and update — `grep -rn "stats.online" resources/js`.)

- [ ] **Step 3: Run typecheck**

```bash
npx tsc --noEmit
```

Expected: errors at all old `stats.online` usages — fix in next task.

- [ ] **Step 4: Commit**

```bash
git add resources/js/types/index.ts
git commit -m "feat(online): update PageProps stats types"
```

---

### Task 13: useOnlineCount hook

**Files:**
- Create: `resources/js/hooks/useOnlineCount.ts`

- [ ] **Step 1: Implement hook**

```typescript
import { useEffect, useRef, useState } from 'react';
import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';

const ANIMATION_MS = 600;

function easeOutQuad(t: number): number {
    return 1 - (1 - t) * (1 - t);
}

export function useOnlineCount(): number {
    const { stats } = usePage<PageProps>().props;
    const initial = stats.online_real + (stats.online_enabled ? stats.online_fake_initial : 0);
    const [display, setDisplay] = useState(initial);
    const fromRef = useRef(initial);
    const targetRef = useRef(initial);
    const animationStartRef = useRef<number | null>(null);

    useEffect(() => {
        if (!stats.online_enabled || typeof window === 'undefined' || !window.Echo) {
            return;
        }

        const channel = window.Echo.channel('stats');
        channel.listen('.online.updated', ({ fake }: { fake: number }) => {
            const next = stats.online_real + fake;
            fromRef.current = display;
            targetRef.current = next;
            animationStartRef.current = performance.now();
            tick();
        });

        function tick() {
            const now = performance.now();
            const elapsed = animationStartRef.current ? now - animationStartRef.current : 0;
            const progress = Math.min(1, elapsed / ANIMATION_MS);
            const eased = easeOutQuad(progress);
            const value = Math.round(fromRef.current + (targetRef.current - fromRef.current) * eased);
            setDisplay(value);
            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        }

        return () => {
            window.Echo?.leaveChannel('stats');
        };
    }, [stats.online_real, stats.online_enabled]);

    return display;
}
```

- [ ] **Step 2: Visual smoke test**

(Hook will be wired in next task; defer test until then.)

- [ ] **Step 3: Commit**

```bash
git add resources/js/hooks/useOnlineCount.ts
git commit -m "feat(online): useOnlineCount hook with smooth interpolation"
```

---

### Task 14: Wire useOnlineCount into Header

**Files:**
- Modify: `resources/js/Components/Layout/Header/index.tsx`

- [ ] **Step 1: Import and use hook**

In `resources/js/Components/Layout/Header/index.tsx`, replace the `StatsChips` function:

```tsx
import { useOnlineCount } from '@/hooks/useOnlineCount';

function StatsChips() {
    const { stats } = usePage<PageProps>().props;
    const onlineDisplay = useOnlineCount();

    return (
        <div className="flex gap-[3px] items-stretch">
            <Chip className="bg-chip text-white">
                <GlobeIcon />
                <ChipLabel>{onlineDisplay.toLocaleString('ru-RU')}</ChipLabel>
            </Chip>
            <Chip className="bg-chip text-white">
                <LevelsIcon />
                <ChipLabel>{stats?.total_upgrades?.toLocaleString('ru-RU') ?? '0'}</ChipLabel>
            </Chip>
            <Chip
                interactive
                className="bg-linear-to-r from-[#FE7A02] to-[#FE4D00] text-white"
            >
                <BonusIcon />
                <ChipLabel>Бонусы</ChipLabel>
            </Chip>
        </div>
    );
}
```

- [ ] **Step 2: Run typecheck**

```bash
npx tsc --noEmit
```

Expected: no errors related to `stats.online`.

- [ ] **Step 3: Manual browser test**

1. Open `/admin/page/online-settings-page`, set enabled=true, min=1500, max=1600, save.
2. Run `php artisan tinker --execute 'dispatch(new \App\Jobs\OnlineDriftJob());'`.
3. Open homepage, observe number animating in chip every 8s.

- [ ] **Step 4: Commit**

```bash
git add resources/js/Components/Layout/Header/index.tsx
git commit -m "feat(online): wire useOnlineCount into Header StatsChips"
```

---

## Phase 6 — Infrastructure & docs

### Task 15: Add `online` queue to Horizon

**Files:**
- Modify: `config/horizon.php`

- [ ] **Step 1: Add online queue to all environments**

In `config/horizon.php`, in `environments.production.supervisor-default`, add `'online'` to the queues array:

```php
'queue' => ['default', 'broadcasts', 'online'],
```

Do the same for `environments.local` (or whatever the local env block is named in this file — check existing keys first).

- [ ] **Step 2: Verify config parses**

```bash
php artisan config:show horizon.environments
```

Expected: `online` appears in the listed queues.

- [ ] **Step 3: Restart horizon (in dev)**

```bash
php artisan horizon:terminate
```

Expected: graceful shutdown; supervisord (or `make dev`) will respawn with new config.

- [ ] **Step 4: Commit**

```bash
git add config/horizon.php
git commit -m "feat(online): add 'online' queue to Horizon supervisors"
```

---

### Task 16: Update CLAUDE.md

**Files:**
- Modify: `CLAUDE.md`

- [ ] **Step 1: Append section**

At the end of `CLAUDE.md` (after the existing rules), add:

```markdown
## Online counter

`stats.online` is shown in the header as `online_real + online_fake`.

- `online_real` — `User::where('last_active_at', '>=', now()->subMinutes(5))->count()`.
- `online_fake` — random walk in `[Setting::online.min, Setting::online.max]`, drifted by `OnlineDriftJob` every `Setting::online.tick_seconds`, broadcast via `OnlineUpdated` event on Reverb channel `stats`.
- Admin manages all params via MoonShine page «Настройки сайта → Онлайн».
- Safety net: `online:boot` artisan command runs hourly via scheduler; respawns the loop if Horizon was down.
- State is in Redis cache key `online.fake_state` (`{value, direction}`). If lost, re-initialized from random.
```

- [ ] **Step 2: Commit**

```bash
git add CLAUDE.md
git commit -m "docs: explain online counter mechanism in CLAUDE.md"
```

---

## Self-review / completion checks

After running all tasks:

- [ ] `php artisan test --filter Online` — all green.
- [ ] `npx tsc --noEmit` — no errors.
- [ ] `vendor/bin/pint --dirty --format agent` — formatted.
- [ ] `make quality` — full quality gate green.
- [ ] Manual: admin page renders, save persists, reset clears state, real chip animates smoothly between values via socket.
