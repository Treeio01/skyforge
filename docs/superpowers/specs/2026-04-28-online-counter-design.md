# Настройка онлайна с сокет-дрейфом — дизайн

**Статус:** утверждён к плану
**Дата:** 2026-04-28
**Контекст:** Skyforge (Laravel 13 + Inertia + React, Reverb, Horizon, MoonShine)

---

## Цель

Заменить вывод числа онлайна на сайте на формулу `realCount + fake`, где:

- `realCount` — реальные активные пользователи за последние 5 минут (как сейчас).
- `fake` — случайное число в диапазоне, заданном админом, плавно дрейфующее во времени и синхронизированное между всеми клиентами через Reverb.

Админ должен иметь человеческую страницу настроек без ключей-строк типа `online.min`.

---

## Архитектура

```
Admin (MoonShine page)
        │ write
        ▼
Settings (DB + Cache)
        │ read
        ▼
OnlineDriftJob (self-rescheduling, queue=online)
   each tick:
     1. read settings
     2. read state from cache
     3. shift ±step, clamp to [min, max], flip direction on bound
     4. save state to cache
     5. broadcast OnlineUpdated
     6. dispatch self with delay = tick_seconds
        │ broadcast
        ▼
Reverb public channel `stats`
        │ subscribe
        ▼
Frontend useOnlineCount hook → animated counter in header
```

**Ключевые решения:**

- State дрейфа хранится в **Redis cache**, не в БД (тики каждые 3–60 сек, нагрузка на запись).
- Реальный счёт — через Inertia props на запросе. Фейк — initial value через Inertia + live updates через Reverb.
- Loop реализуется как **self-rescheduling Horizon job**, потому что Laravel scheduler не умеет sub-minute и поднимать supervisor отдельно — overkill, когда Horizon уже есть.
- Канал **public** (`stats`) — фейк-число не секретное.

---

## Компоненты

### 1. Admin: страница «Онлайн на сайте»

**Расположение:** новый раздел `Настройки сайта` в сайдбаре MoonShine, внутри подвкладка `Онлайн`.

**Реализация:** одна `CustomPage` MoonShine, не resource. Внутри обычная form с tailwind/MoonShine компонентами. При сохранении пишет в `Setting`-строки.

**Поля и блоки:**

- Блок «Накрутка»:
  - Toggle «Включить накрутку» с подсказкой «Если выключено, в шапке показывается только реальное количество активных пользователей».
- Блок «Диапазон»:
  - Поле «Минимум» (int, ≥ 0)
  - Поле «Максимум» (int, ≥ Минимум)
- Блок «Поведение» (раскрывается, по умолчанию открыт):
  - Slider «Частота обновления» (3–60 сек, default 8) с подсказкой «Как часто число меняется на сайте».
  - Slider «Максимальный шаг» (1–10, default 3) с подсказкой «На сколько максимум прыгает за один шаг».
- Блок «Превью»:
  - Большое число — текущее значение (live через подписку на `stats` в самой админке).
  - Бейдж: «Реальный N» + «Накрутка M» = «Итого N+M».
  - Кнопка «Сбросить и пересчитать» — `Cache::forget('online.fake_state')`, фейк перегенерится на следующем тике.
- Sticky кнопка «Сохранить» внизу. После сохранения — toast «Настройки применены, обновляются на всех клиентах».

**Валидация формы:** `max ≥ min`, `tick_seconds ∈ [3, 60]`, `max_step ∈ [1, 10]`.

**Поведение при сохранении:**
- Запись `Setting::set('online.enabled' | 'online.min' | 'online.max' | 'online.tick_seconds' | 'online.max_step', value)`.
- `Cache::forget('online.fake_state')`.
- Если `enabled` переключён `false → true` — `dispatch(new OnlineDriftJob)`.
- Если `enabled` переключён `true → false` — ничего, loop умрёт сам на следующем тике.

### 2. Backend

**`App\Jobs\OnlineDriftJob` (queue=online, tries=1, без ретраев):**

```
handle():
  enabled = Setting::get('online.enabled', false)
  if !enabled: return  // loop умирает

  lock = Cache::lock('online.loop', tick_default * 2)
  if !lock->get(): return  // другой инстанс уже работает

  try:
    min = Setting::get('online.min', 1500)
    max = Setting::get('online.max', 1600)
    tick = Setting::get('online.tick_seconds', 8)
    max_step = Setting::get('online.max_step', 3)
    if min >= max: log warning; return

    // heartbeat для safety-net команды
    Cache::put('online.loop_heartbeat', now()->timestamp, ttl: tick * 3)

    state = Cache::get('online.fake_state') ?? init(min, max)

    if random(0, 99) < 15:
      state.direction = -state.direction

    step = random(1, max_step) * state.direction
    new_value = clamp(state.value + step, min, max)

    if new_value == min: state.direction = +1
    if new_value == max: state.direction = -1

    Cache::put('online.fake_state', { value: new_value, direction: state.direction }, ttl: tick * 5)
    broadcast(new OnlineUpdated(new_value))
  finally:
    lock->release()

  OnlineDriftJob::dispatch()->onQueue('online')->delay($tick)

init(min, max):
  return { value: random(min, max), direction: random([-1, +1]) }
```

**`App\Events\OnlineUpdated` implements `ShouldBroadcast`:**

```php
public function __construct(public int $fake) {}
public function broadcastOn(): Channel { return new Channel('stats'); }
public function broadcastAs(): string { return 'online.updated'; }
public function broadcastWith(): array { return ['fake' => $this->fake]; }
```

**`App\Console\Commands\OnlineLoopBoot` (artisan: `online:boot`):**
- Дёргается из `app/Console/Kernel.php` каждый час как safety net.
- Проверяет `enabled === true && Cache::get('online.loop_heartbeat') === null` — если loop умер, поднимает первый job.
- Heartbeat (sliding TTL = tick * 3) выставляется самим `OnlineDriftJob` в начале `handle()`.

### 3. Frontend

**`HandleInertiaRequests::share()` — расширить `stats`:**

```php
'stats' => Cache::remember('site_stats', 30, fn () => [
    'online_real' => User::where('last_active_at', '>=', now()->subMinutes(5))->count(),
    'online_fake_initial' => Cache::get('online.fake_state')['value'] ?? 0,
    'online_enabled' => (bool) Setting::get('online.enabled', false),
    'total_upgrades' => /* как сейчас */,
]),
```

**`resources/js/hooks/useOnlineCount.ts` (новый):**

```typescript
function useOnlineCount(): number {
  const { stats } = usePage<PageProps>().props;
  const target = stats.online_real + (stats.online_enabled ? stats.online_fake_initial : 0);
  const [display, setDisplay] = useState(target);

  // Subscribe to Reverb
  useEffect(() => {
    if (!stats.online_enabled || !window.Echo) return;
    const channel = window.Echo.channel('stats');
    channel.listen('.online.updated', ({ fake }: { fake: number }) => {
      const next = stats.online_real + fake;
      animateNumber(display, next, 600, setDisplay);
    });
    return () => window.Echo.leaveChannel('stats');
  }, [stats.online_real, stats.online_enabled]);

  return display;
}
```

`animateNumber` — простой `requestAnimationFrame` от `from` к `to` с easing (например, easeOutQuad), 600ms.

**Использование в `Header/index.tsx`:**
- Заменить `stats?.online?.toLocaleString('ru-RU')` на `useOnlineCount().toLocaleString('ru-RU')`.

---

## Поток данных

**Запрос страницы:**
1. Inertia rendering вызывает `HandleInertiaRequests::share`.
2. Возвращаются `online_real`, `online_fake_initial`, `online_enabled`.
3. Frontend моментально показывает правильное число без ожидания сокета.

**Фоновый дрейф:**
1. `OnlineDriftJob` каждые `tick` секунд считает новое значение.
2. Сохраняет в `Cache::put('online.fake_state', ...)` (для следующего render и следующего тика).
3. Broadcast на канал `stats` event `online.updated` с `fake: int`.

**Live-обновление:**
1. Все клиенты подписаны на `stats`.
2. `useOnlineCount` ловит event, плавно анимирует от текущего к новому числу за 600ms.

---

## Обработка ошибок

| Ситуация | Поведение |
|----------|-----------|
| Reverb недоступен (broadcast кидает exception) | `Log::warning`, продолжаем re-dispatch — не теряем loop. |
| Horizon рестарт | Loop помер. `OnlineLoopBoot` (cron каждый час) поднимает заново по heartbeat. |
| Redis рестарт (state потерян) | На следующем тике `init()` создаёт state заново. Один резкий прыжок числа — приемлемо. |
| `min >= max` в settings | Job логирует warning, exit без re-dispatch. Форма в админке не пропустит такие значения, но защита в job на случай прямого SQL. |
| Job упал в середине | `tries=1`, без ретраев. Horizon видит fail в `failed_jobs`, safety net через час поднимет loop. |

---

## Тестирование

**Pest feature/unit:**

- `OnlineDriftJobTest`:
  - дрейф остаётся в `[min, max]` на 100 тиках (с фиксированным seed).
  - flip direction при ударе границы.
  - `enabled=false` → job exit без broadcast и без re-dispatch.
  - re-dispatch с правильным `delay`.
  - lock не позволяет двойной запуск.
- `OnlineSettingsPageTest`:
  - сохранение валидной формы → `Setting::get('online.*')` возвращает значения.
  - `min > max` → ошибка валидации, форма не сохраняется.
  - переключение `enabled false→true` диспатчит `OnlineDriftJob` (через `Bus::fake`).
  - кнопка «Сбросить и пересчитать» очищает cache state.
- `HandleInertiaRequestsTest`:
  - `stats.online_real` равен реальному `last_active_at` count.
  - `stats.online_fake_initial` читается из cache.
  - `stats.online_enabled` булев из настроек.
- `OnlineUpdatedEventTest`:
  - `Event::fake` + проверка что job дёргает event с правильным `fake` значением.

**Не тестируем:**
- Реальный Reverb-канал (это Reverb internals).
- Frontend `useOnlineCount` (UI hook без сложной логики, проверяется руками + смоук-тест Pest 4 browser «открой страницу, увидь число»).

---

## Что вне scope

- График онлайна за время — отдельная задача (графики в админке, есть в общем списке отдельным пунктом).
- Per-user RTP/подкрутка — отдельный спек.
- Логирование истории фейка — не нужно, это просто отображалка.
