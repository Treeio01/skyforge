# План оптимизации производительности (backend + frontend)

Документ фиксирует **найденные узкие места по текущему коду** Skyforge/Inertia/React и задаёт **приоритетный план работ** измерениями успеха по каждой фазе.

---

## 1. Цели и метрики

| Область | Что считаем успехом |
|--------|----------------------|
| TTFB / сервер | Меньше запросов к БД/Redis на **каждый** Inertia-запрос, меньше тяжёлых операций при прогреве кеша |
| Payload | Меньший JSON initial page + меньше «лишних» shared props на страницах, где они не нужны |
| Клиент | Меньший JS для холодной загрузки неигровых страниц, меньше сетевой/CPU активности без участия пользователя |
| Надёжность | Нет регрессий по реалтайму (Reverb/Live Feed), авторизации, блокировке баланса |

**Обязательно до крупных правок**: baseline в Chrome Lighthouse (desktop/mobile) на главной, маркете, апгрейде; `APP_DEBUG=false`, репрезентативная БД. Для Laravel — Telescope/Debugbar только локально или выборочно.

---

## 2. Backend: обзор и рекомендации

### 2.1 Критический приоритет (высокий эффект / относительно безопасно)

#### A. Общие Inertia-shared props (`HandleInertiaRequests`)

**Факт:** При каждом запросе Inertia выполняется `share()` со статистикой, соцссылками, `upgradeSettings` и записью активности пользователя.

**Наблюдения:**

1. **`last_active_at` обновление в middleware** — это **write в БД** на части запросов (раз в минуту на пользователя), причём на **любой** навигации Inertia, не только на «игровых» экранах. При росте онлайна это даёт стабильный фоновый UPDATE-трафик.
   - **Направление:** перенести на `defer()` после ответа (Laravel 11+) или троттлинг через Redis счётчик «последний flush», или обновлять только на «значимых» маршрутах/job heartbeat.
   - **Файл:** `app/Http/Middleware/HandleInertiaRequests.php`

2. **`stats` блок** — считается из `Cache::remember('site_stats', 30, …)` но внутри closure всё равно:
   - `User::where('last_active_at', '>=', …)->count()` — индекс на `last_active_at` желателен;
   - **`UpgradeStatsService::total()` → `Upgrade::query()->count()`** — на большой таблице `upgrades` **полный COUNT(*)** каждый раз при **прогреве** кеша каждые 30 с — дорого.
   - **Направления (выбери одну стратегию):**
     - **Материализованный счётчик** в Redis/кеш-ключ `upgrades.total` + инкремент/декремент в месте создания апгрейда (или reconcile по cron на низкой частоте);
     - или **отдельная маленькая таблица** «статистика» строка-снимок обновляемая асинхронно;
     - или **TTL длиннее** + фоновый прогрев по расписанию, чтобы пользовательские запросы не били по холодному count.
   - **Файлы:** `app/Http/Middleware/HandleInertiaRequests.php`, `app/Services/UpgradeStatsService.php` (есть уже `broadcastCurrentTotal` с `Cache::forget('site_stats')` — использовать тот же кеш ключ консистентно).

#### B. Частота обращений к настройкам

**Факт:** `Setting::get` каждый раз кешируется по ключу на 60 с — уже неплохо, но в одном `share()` идёт **много ключей подряд** (соцсети + upgrade + online flag внутри stats closure).

**Направление:** один снимок **`settings.bundle.frontend`** (один ключ кеша) с массивом всех нужных значений и инвалидировать его в `Setting::set` при изменении любого участвующего ключа — меньше round-trip к Redis под нагрузкой.

**Файлы:** `app/Models/Setting.php`, `app/Http/Middleware/HandleInertiaRequests.php`.

---

### 2.2 Средний приоритет (архитектурная гигиена Inertia в Laravel)

#### C. Отложенная загрузка тяжёлых данных (Inertia v2)

**Факт:** В кодовой базе пока почти не используется паттерн `defer` / частичная отдача тяжёлых блоков профиля/маркета.

**Направление:** для страниц где первый экран может обойтись лёгкими данными:
- профиль: «шапка» пользователя синхронно, история транзакций / тяжёлые блоки — `Inertia::defer()` или отдельные JSON endpoints с skeleton (у вас уже есть JSON для депозитов в `UserController::deposits`);
- главная/market первый экран: уменьшить «всё в одном» ответе, если появится тяжёлый контент.

Это уменьшает **TTI** и время сериализации на сервере.

#### D. Пагинация маркета (API/Inertia ресурс)

**Факт:** `SkinCatalogService::listForMarket` по умолчанию **150** элементов за страницу (`per_page`), `useMarket` тоже передаёт `perPage: 150`.

**Наблюдение:** большой HTML/JSON-пейлоуд, высокая нагрузка на сериализацию и на клиент (DOM если не виртуализовано).

**Направление:** снизить default (например 48–72) + «load more», либо виртуализировать сетку (см. фронт секцию).

**Файлы:** `app/Services/SkinCatalogService.php`, `resources/js/Components/Market/useMarket.ts`.

---

### 2.3 Наблюдаемость и индексы (низкий риск, профит после измерений)

#### E. Индексы БД под реальные запросы

Проверить миграции / `EXPLAIN` на:

- `upgrades`: `created_at`, `user_id`, композиты под `LiveFeedService`/`UserProfileService` выборки;
- `users.last_active_at` под online count в shared stats;
- `user_skins(status, …)` под инвентарь на апгрейде.

#### F. Кеширование read-heavy JSON

`/api/live-feed` дергается с каждой загрузкой layout-сайдбара (**см. фронт**). На сервере можно короткий кеш ответа (5–15 с) или ETag/`Cache-Control`, если допустимо по «свежести».

**Файл:** `app/Services/LiveFeedService.php`.

---

### 2.4 Auth bridge (узкий контур, уже неплохо)

Текущее разнесение регистров/whitelist в `ConsumerDomainRegistry` ок по CPU. Основное:

- следить чтобы **TTL токена** и **replay lock** не порождали горячих ключей в Redis при burst (при необходимости bucket по IP уже есть `throttle:auth`);

**Файлы:** `app/Services/AuthBridge/*`, `routes/auth.php`.

---

## 3. Frontend: обзор и рекомендации

### 3.1 Критический приоритет

#### G. Глобальный bootstrap: Axios + Laravel Echo/Reverb на **каждой** странице

**Факт:** `resources/js/bootstrap.ts` поднимает **`Echo`/Pusher** сразу при загрузке бандла. Это означает:
- тяжёлый JS даже там, где веб-сокеты не нужны;
- возможное открытие WS соединения до первого реального события.

**Направление:**

1. **Ленивая инициализация Echo** — `import()` динамически при первом подключении компонента, который слушает каналы (или при входе авторизованного пользователя только на нужных маршрутах);
2. разделить `bootstrap` на «minimal» (csrf/axios defaults) и «realtime».

**Файлы:** `resources/js/bootstrap.ts`, `resources/js/app.tsx`, потребители Echo (найти через `Echo.` / `window.Echo`).

#### H. Live feed в общем layout

**Факт:** `AppLayout` монтирует `LiveFeed`, который при `useEffect` делает **`axios.get('/api/live-feed')`** на каждой странице с этим лейаутом.

**Направление:**

1. Связать с Reverb-событием, которое уже может обновлять ленту (если есть event с сервера при новом апгрейде — не опрашивать REST на mount);
2. или один глобальный кеш на клиенте (SWR/React Query) с **`staleTime`**, не дёргать API при каждой смене маршрута;
3. либо **Intersection Observer** — грузить feed только когда колонка видна.

**Файлы:** `resources/js/Layouts/AppLayout.tsx`, `resources/js/Components/Upgrade/LiveFeed.tsx`.

---

### 3.2 Высокий приоритет (перцепшен и CPU на клиенте)

#### I. Анимации `framer-motion` на каждой навигации

**Факт:** `AppLayout` оборачивает `<main>` в `motion.main` с `key={url}` → анимация на **любой** Inertia переход.

**Эффект:** лишний layout thrash на слабых устройствах; библиотека в бандле.

**Направление:** упроостить transitions (CSS `transition-opacity` точечно) или включать motion только для «тяжёлых» страниц; код-сплит `framer-motion` через `lazy` импорт.

**Файл:** `resources/js/Layouts/AppLayout.tsx`.

#### J. Страница Upgrade: префетч всех видео

**Факт:** `ALL_UPGRADE_VIDEO_SRCS` — **до ~10 разных mp4** (pc/mb умножения), `VideoPreloader` загружает все до скрытия лоадера.

**Эффект:** трафик мобильного интернета, декодинг, главный поток.

**Направление:**

1. префетч только **активных девайс-классов** (один variant `idle` минимально, остальные по первому переходу в `playing`);
2. `preload="metadata"` / отложенное создание второго `<video>`;
3. **Intersection / user gesture** — не начинать декодинг до нахождения в viewport.

**Файлы:** `resources/js/Pages/Upgrade/Index.tsx`, `resources/js/Components/Upgrade/VideoPreloader.tsx`, `resources/js/Components/Upgrade/upgradeVideos.ts`.

---

### 3.3 Средний приоритет

#### K. Маркет: виртуализация списка

Сейчас `SkinGrid` + до 150 карточек — много DOM-нод и ре-рендеров при фильтрах.

**Направление:** `@tanstack/react-virtual` или `react-window` для области скролла; сохранять UX «бесконечный скролл».

#### L. Изображения скинов

Проверить: `loading="lazy"`, корректные размеры, **WebP/AVIF** через storage pipeline или `<picture>` там, где много карточек.

#### M. i18next

При росте словарей — **lazy load** локалей (`i18next-http-backend` / чанки) вместо единственного синхронного бандла, если строк много в одном языке для первого входа.

**Файл:** `resources/js/i18n` (структура на месте после аудита).

---

## 4. План реализации по фазам

### Фаза 1 — Измерение (0.5–1 день)

- [ ] Зафиксировать Lighthouse baseline (3 ключевые страницы).
- [ ] Включить `clockwork`/Telescope локально или APM staging: топ SQL по времени / количество.
- [ ] Проверить `EXPLAIN` для `Upgrade::count()` и online count запросов.

### Фаза 2 — Backend быстрые победы (1–2 дня)

- [ ] Заменить/прогреть стратегию для **total upgrades** в `site_stats` (счётчик Redis или редкий reconcile) + согласовать с `UpgradeStatsUpdated`.
- [ ] Батч `Setting::get` в один кеш-бандл для shared props.
- [ ] Вынести/отложить `last_active_at` из middleware (или триггерить реже).

**Критерий готовности:** на Inertia запрос без прогрева кеша — нет full table scan count в hot path; меньше Redis round-trips на share.

### Фаза 3 — Frontend загрузка и сеть (2–4 дня)

- [ ] Ленивый Echo/realtime bootstrap.
- [ ] Live feed: подписка на событие / кеш запроса / загрузка по видимости.
- [ ] Upgrade videos: загрузка по необходимости, не все src сразу.

**Критерий готовности:** меньший JS initial на «лёгкой» странице; меньше запросов к `/api/live-feed` при навигации; меньше мегабайт на первом заходе в Upgrade на mobile.

### Фаза 4 — UX перф без потери качества (по необходимости)

- [ ] Виртуализация маркета + подстройка `per_page`.
- [ ] Упростить framer-motion в layout или code-split.
- [ ] Оптимизация картинок.

### Фаза 5 — Inertia defer / разделение props (итеративно)

- [ ] Вынести тяжёлые секции профиля/админки в deferred props с skeleton в UI.

---

## 5. Риски и что не ломать

- **Баланс и транзакции** — любые счётчики «всего апгрейдов» должны быть **eventually consistent** или обновляться в той же транзакции что и создание апгрейда, если число используется в юридически значимых местах (обычно нет).
- **Online count** — если уйти от count к приближению, обновить отображение в шапке и правила в CLAUDE.md.
- **SEO/SSR** — проект Inertia; оптимизации не должны ломать мета и первый paint без согласования.

---

## 6. Краткий чеклист «сделано / не сделано»

Используйте как трекер в PR:

| ID | Тема | Статус |
|----|------|--------|
| A | `last_active_at` не в hot path middleware | ✅ `app()->terminating` в HandleInertiaRequests |
| B | Total upgrades без `COUNT(*)` на каждый прогрев | ✅ Redis ключ `stats.upgrades_total`, `php artisan upgrade:stats-reconcile` |
| C | Бандл настроек в одном кеш-ключе | ✅ `Setting::frontendBundle()` |
| D | Page size market / API per_page | ✅ default `72` (Market + API) |
| G | Lazy Echo / split bootstrap | ✅ `resources/js/realtime/*` + узкий bootstrap |
| H | Live feed без REST на каждый mount | ✅ `liveFeedCache` + desktop-only fetch |
| I | Framer-motion scope | ✅ убран `motion` из layout main |
| J | Upgrade video loading strategy | ✅ idle first, затем фон metadata |
| K | Virtualized market grid | ✅ уже было (`SkinGrid` + react-virtual) |

---

*Документ можно обновлять по мере внедрения: даты, фактические цифры до/после, ссылки на PR.*
