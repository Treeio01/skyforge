# SKYFORGE — Полный план реализации

## О проекте

CS2 skin upgrade platform. Пользователи авторизуются через Steam, пополняют виртуальный баланс (скины/СБП/крипта), играют в апгрейд скинов, выводят выигранные скины.

- **Стек**: Laravel 13 + Inertia.js + React (TypeScript) монолит
- **БД**: MySQL 8.4, Redis 7.2
- **Админка**: MoonShine
- **Очереди**: Laravel Horizon (Redis)
- **WebSocket**: Laravel Reverb
- **Auth**: Steam OpenID (Socialite)
- **Принципы**: SOLID, KISS, DRY, TDD

## Что уже сделано (Фаза 0)

- [x] Laravel 13 проект создан в `/Users/danil/Desktop/projects/skyforge`
- [x] Установлены: MoonShine, Horizon, Reverb, Socialite, IDE Helper, Larastan, Pest, Pint
- [x] Laravel Boost (MCP сервер с 15 инструментами) + SuperPowers (Claude Code plugin)
- [x] Структура папок: `app/{Actions,Services,Contracts,DTOs,Enums,Events,Listeners,Observers,Jobs}`
- [x] Docker (PHP-FPM, Nginx, MySQL, Redis, Horizon, Reverb)
- [x] CI/CD (GitHub Actions: lint, analyse, test, build)
- [x] `config/skyforge.php` — конфиг приложения
- [x] `.env.example` со всеми переменными
- [x] CLAUDE.md, README.md, Makefile
- [x] Git init + 2 коммита
- [x] Breeze email/password auth удалён (Steam-only)

## Референсные файлы

Макет и скины находятся в **другой папке** — `/Users/danil/Desktop/projects/upgrade/`:

- `index.html` — HTML макет (навбар, апгрейд-зона, инвентарь, live feed, футер)
- `style.css` — Дизайн-токены: `--black: #0a0a0a`, `--accent: #a3e635`, шрифт Inter, dark theme
- `parser/skins_index.json` — JSON с 31084 скинами: `{ "market_hash_name": { "file": "name.webp", "price": 1.23 } }`
- `parser/skins/` — 31084 WebP изображения скинов

## Архитектурные решения

### Валюта
- Виртуальный баланс 1:1 с рублём
- **Все цены хранятся в копейках** (BIGINT) — избегаем float
- `1250.50 RUB` = `125050` в БД

### Баланс
- Pessimistic locking: `User::lockForUpdate()->find($id)` в DB::transaction
- Каждое изменение баланса → запись в `transactions` (immutable ledger)
- CHECK constraint: `balance >= 0`

### Инвентарь пользователя (user_skins)
- Депозит скинами → скин попадает в `user_skins` (инвентарь юзера)
- Депозит рублями → `balance` (рубли на балансе)
- У юзера есть И скины, И баланс
- Скины в инвентаре привязаны к конкретному юзеру и имеют цену на момент получения

### Апгрейд (основная механика)
- Пользователь выбирает скины из своего инвентаря (`user_skins`) + опционально добавляет баланс
- Выбирает целевой скин из каталога (`skins`) — должен быть дороже ставки
- `bet_amount = sum(user_skins[].price) + balance_amount`
- Шанс = `(betAmount / targetPrice) * (1 - houseEdge/100) * 100`
- Зажимается в рамки `min_chance`...`max_chance` (1%...95%)
- Результат определяется через Provably Fair (HMAC-SHA256)
- WIN → целевой скин добавляется в инвентарь юзера (`user_skins`), поставленные скины сгорают
- LOSE → поставленные скины сгорают, баланс дебитится
- Скины из инвентаря можно вывести через trade offer

### Provably Fair
- `roll = HMAC-SHA256(serverSeed, clientSeed + "-" + nonce)`
- Первые 8 hex → int → делим на 0xFFFFFFFF → float [0, 1)
- `roll < chance/100` = WIN

### Паттерны кода
- **Actions** — единичные операции, один public `execute()` метод
- **Services** — оркестрация, stateless, координируют несколько Actions
- **Contracts** — интерфейсы для внешних зависимостей
- **DTOs** — immutable value objects
- **Enums** — PHP 8.1+ backed enums
- **Events/Listeners** — для real-time и side effects
- **Observers** — для model lifecycle hooks

### Защита от дублей (Idempotency)
- Deposits: `idempotency_key` (UNIQUE) — webhook от платёжки может прийти дважды, без idempotency баланс зачислится дважды
- Withdrawals: проверка `status != completed` перед обработкой
- Upgrades: Redis lock `upgrade:{userId}` на 2 сек — предотвращает double-click

### Soft Deletes
- Модели `User` и `Skin` используют `SoftDeletes` — нельзя удалять записи с FK связями на финансовые таблицы
- Для деактивации: `is_active = false` (скины), `is_banned = true` (юзеры)
- Hard delete запрещён на production

### Audit Log
- `spatie/laravel-activitylog` для логирования действий админа (бан, изменение баланса, настроек)
- Все изменения через MoonShine автоматически логируются

### Anti-abuse
- Rate limiting: 1 upgrade / 2 сек, max 5 pending deposits, max 3 pending withdrawals на юзера
- Мониторинг паттернов: юзеры с аномально высоким win rate при high-chance ставках
- Min bet amount (1 RUB) предотвращает micro-grinding

### Режим стримера (Streamer Mode)
Подкрутка результатов для промо-аккаунтов. **Не ломает Provably Fair** — roll остаётся верифицируемым, но шанс рассчитывается с персональным house edge.

**Реализация:**
- В таблице `users` добавить: `house_edge_override` (DECIMAL 5,2, NULLABLE) и `chance_modifier` (DECIMAL 5,3, DEFAULT 1.000)
- Если `house_edge_override` задан → используется вместо глобального house_edge
- `chance_modifier` умножает финальный шанс: `finalChance = baseChance * chance_modifier`
- Управление через MoonShine: админ выставляет на конкретного юзера
- **Audit log**: каждое изменение модификатора логируется (spatie/activitylog)
- В `upgrades` таблице уже есть `house_edge` snapshot — фиксируется применённое значение

**Формула с модификатором:**
```
baseChance = (betAmount / targetPrice) * (1 - effectiveHouseEdge/100) * 100
finalChance = clamp(baseChance * user.chance_modifier, minChance, maxChance)
```

**Типичные профили:**
- Обычный юзер: `house_edge_override = NULL, chance_modifier = 1.000`
- Стример: `house_edge_override = 0.00, chance_modifier = 1.100`
- VIP стример: `house_edge_override = -3.00, chance_modifier = 1.200`
- Подозрительный: `house_edge_override = 8.00, chance_modifier = 0.900`

**Безопасность:**
- Roll верифицируем: `HMAC-SHA256(seed, clientSeed-nonce)` не меняется
- Зритель проверяет roll → совпадает
- Шанс отображался на экране стримера → совпадает (шанс считается серверно)
- Никто кроме админа не видит модификаторы

### Устойчивость к сбоям
- Steam API downtime: длинные сессии (120+ мин), graceful error "Steam временно недоступен"
- Цена изменилась во время апгрейда: цена берётся из БД в момент транзакции, фиксируется в `upgrade.target_price`
- 31K скинов в UI: серверная пагинация (50 шт), FULLTEXT поиск, фильтры. Никогда не грузить всё разом

---

## Фаза 1: База данных и модели

### Шаг 1.1: Настроить .env и подключение к MySQL
1. Скопировать `.env.example` → `.env`
2. Настроить `DB_DATABASE=skyforge`, `DB_USERNAME`, `DB_PASSWORD`
3. Создать БД: `mysql -u root -e "CREATE DATABASE skyforge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"`
4. Проверить: `php artisan db:show`

### Шаг 1.2: Создать Enum'ы (8 штук)
Все в `app/Enums/`:

1. **TransactionType** — `deposit`, `withdrawal`, `upgrade_bet`, `upgrade_win`, `refund`, `bonus`, `admin_adjustment`
2. **DepositMethod** — `skins`, `sbp`, `crypto`
3. **DepositStatus** — `pending`, `processing`, `completed`, `failed`, `cancelled`
4. **WithdrawalStatus** — `pending`, `processing`, `sent`, `completed`, `failed`, `cancelled`
5. **UpgradeResult** — `win`, `lose`
6. **SkinExterior** — `FN`, `MW`, `FT`, `WW`, `BS` (с labels: Factory New, etc.)
7. **SkinCategory** — `weapon`, `knife`, `gloves`, `sticker`, `graffiti`, `charm`, `agent`, `container`, `other`
8. **SkinRarity** — `consumer`, `industrial`, `mil_spec`, `restricted`, `classified`, `covert`, `extraordinary`, `contraband` (с hex цветами)
9. **UserSkinStatus** — `available`, `in_upgrade`, `withdrawn`, `burned`
10. **UserSkinSource** — `deposit`, `upgrade_win`, `admin`

### Шаг 1.3: Модифицировать миграцию users
Стандартная Laravel миграция `create_users_table` уже есть. Создать новую миграцию `modify_users_table_for_skyforge`:

- Удалить: `email`, `email_verified_at`, `password` (не нужны — Steam auth)
- Добавить: `steam_id` (VARCHAR 20, UNIQUE), `username` (VARCHAR 255), `avatar_url` (VARCHAR 512), `trade_url` (VARCHAR 512, NULLABLE), `balance` (BIGINT UNSIGNED DEFAULT 0), `total_deposited` (BIGINT UNSIGNED DEFAULT 0), `total_withdrawn` (BIGINT UNSIGNED DEFAULT 0), `total_upgraded` (BIGINT UNSIGNED DEFAULT 0), `total_won` (BIGINT UNSIGNED DEFAULT 0), `is_banned` (BOOLEAN DEFAULT false), `ban_reason` (VARCHAR 512, NULLABLE), `is_admin` (BOOLEAN DEFAULT false), `house_edge_override` (DECIMAL 5,2, NULLABLE — персональный house edge), `chance_modifier` (DECIMAL 5,3, DEFAULT 1.000 — множитель шанса для стримеров), `last_active_at` (TIMESTAMP, NULLABLE), `deleted_at` (TIMESTAMP, NULLABLE — SoftDeletes)

### Шаг 1.4: Создать миграцию skins
```
id, market_hash_name (VARCHAR 255, UNIQUE), weapon_type (VARCHAR 64, NULLABLE),
skin_name (VARCHAR 255, NULLABLE), exterior (ENUM через string), rarity (VARCHAR 32, NULLABLE),
rarity_color (VARCHAR 7, NULLABLE), category (VARCHAR 64, NULLABLE),
image_path (VARCHAR 255), price (BIGINT UNSIGNED DEFAULT 0),
price_updated_at (TIMESTAMP, NULLABLE), is_active (BOOLEAN DEFAULT true),
is_available_for_upgrade (BOOLEAN DEFAULT true), timestamps, deleted_at (SoftDeletes)
```
Индексы: UNIQUE(market_hash_name), INDEX(category, price), INDEX(is_active, is_available_for_upgrade, price), FULLTEXT(market_hash_name)

> **SoftDeletes**: Скины нельзя hard-delete — на них ссылаются upgrade_items, withdrawals. Деактивация через `is_active = false`.

### Шаг 1.5: Создать миграцию skin_prices
```
id, skin_id (FK CASCADE), price (BIGINT UNSIGNED), source (VARCHAR 32 DEFAULT 'market_csgo'),
fetched_at (TIMESTAMP)
```
Индексы: INDEX(skin_id, fetched_at). Без updated_at — append-only.

### Шаг 1.6: Создать миграцию transactions
```
id, user_id (FK), type (string — enum), amount (BIGINT — signed!),
balance_before (BIGINT UNSIGNED), balance_after (BIGINT UNSIGNED),
reference_type (VARCHAR 64, NULLABLE), reference_id (BIGINT UNSIGNED, NULLABLE),
description (VARCHAR 512, NULLABLE), created_at (TIMESTAMP)
```
Без updated_at — immutable. Индексы: INDEX(user_id, created_at), INDEX(type), INDEX(reference_type, reference_id)

### Шаг 1.7: Создать миграцию deposits
```
id, user_id (FK), method (string — enum), amount (BIGINT UNSIGNED),
status (string — enum, DEFAULT 'pending'), provider_id (VARCHAR 255, NULLABLE),
idempotency_key (VARCHAR 64, UNIQUE, NULLABLE), provider_data (JSON, NULLABLE),
completed_at (TIMESTAMP, NULLABLE), timestamps
```
Индексы: INDEX(user_id, status), INDEX(provider_id), UNIQUE(idempotency_key), INDEX(status)

> **Idempotency**: Платёжка может отправить webhook дважды. `idempotency_key` = provider_id или UUID. Перед зачислением проверяем: если deposit с таким ключом уже `completed` — игнорируем.

### Шаг 1.8: Создать миграцию withdrawals
```
id, user_id (FK), user_skin_id (FK — скин из инвентаря), skin_id (FK — каталог, для истории),
amount (BIGINT UNSIGNED — цена скина на момент вывода),
status (string — enum, DEFAULT 'pending'), trade_offer_id (VARCHAR 64, NULLABLE),
trade_offer_status (VARCHAR 32, NULLABLE), failure_reason (VARCHAR 512, NULLABLE),
completed_at (TIMESTAMP, NULLABLE), timestamps
```
Индексы: INDEX(user_id, status), INDEX(trade_offer_id), INDEX(status)

### Шаг 1.9: Создать миграцию upgrades
```
id, user_id (FK), target_skin_id (FK), bet_amount (BIGINT UNSIGNED),
balance_amount (BIGINT UNSIGNED DEFAULT 0), target_price (BIGINT UNSIGNED),
chance (DECIMAL 8,5), multiplier (DECIMAL 8,2), house_edge (DECIMAL 5,2),
chance_modifier (DECIMAL 5,3, DEFAULT 1.000 — snapshot модификатора юзера),
result (string — enum), roll_value (DOUBLE), roll_hex (VARCHAR 16),
client_seed (VARCHAR 64), server_seed (VARCHAR 128), server_seed_raw (VARCHAR 128),
nonce (BIGINT UNSIGNED), is_revealed (BOOLEAN DEFAULT false), created_at (TIMESTAMP)
```
Без updated_at (immutable кроме is_revealed). Индексы: INDEX(user_id, created_at), INDEX(result), INDEX(created_at)

### Шаг 1.10: Создать миграцию user_skins
```
id, user_id (FK), skin_id (FK), price_at_acquisition (BIGINT UNSIGNED — цена в момент получения),
source (string — enum: deposit/upgrade_win/admin), source_id (BIGINT UNSIGNED, NULLABLE — deposit_id или upgrade_id),
status (string — enum: available/in_upgrade/withdrawn/burned),
withdrawn_at (TIMESTAMP, NULLABLE), timestamps
```
Индексы: INDEX(user_id, status), INDEX(skin_id), INDEX(status)

> Это инвентарь юзера. Скин попадает сюда при: депозите скинами, выигрыше апгрейда, ручном начислении админом. Скин уходит при: проигрыше (burned), выводе (withdrawn). `status = in_upgrade` блокирует скин от параллельного использования.

### Шаг 1.11: Создать миграцию upgrade_items
```
id, upgrade_id (FK CASCADE), user_skin_id (FK — ссылка на user_skins, не на skins!),
skin_id (FK — ссылка на skins каталог, для истории), price (BIGINT UNSIGNED — цена на момент ставки)
```
Индекс: INDEX(upgrade_id)

> `user_skin_id` → конкретный скин из инвентаря юзера. `skin_id` → каталогный скин (для отображения в истории после burn). `price` → snapshot цены.

### Шаг 1.12: Создать миграцию settings
```
id, key (VARCHAR 64, UNIQUE), value (TEXT), type (string — enum: string/integer/float/boolean/json),
description (VARCHAR 255, NULLABLE), updated_at (TIMESTAMP)
```

### Шаг 1.13: Создать миграции promo_codes + promo_code_usages
**promo_codes:**
```
id, code (VARCHAR 32, UNIQUE), type (ENUM: fixed/percent), amount (BIGINT UNSIGNED),
max_uses (INT UNSIGNED, NULLABLE), times_used (INT UNSIGNED DEFAULT 0),
min_deposit (BIGINT UNSIGNED DEFAULT 0), expires_at (TIMESTAMP, NULLABLE),
is_active (BOOLEAN DEFAULT true), timestamps
```

**promo_code_usages:**
```
id, promo_code_id (FK), user_id (FK), amount (BIGINT UNSIGNED), created_at (TIMESTAMP)
```
Индексы: UNIQUE(promo_code_id, user_id), INDEX(user_id)

### Шаг 1.14: Создать миграцию provably_fair_seeds
```
id, user_id (FK), client_seed (VARCHAR 64), server_seed (VARCHAR 128),
server_seed_hash (VARCHAR 128), nonce (BIGINT UNSIGNED DEFAULT 0),
is_active (BOOLEAN DEFAULT true), timestamps
```
Индекс: INDEX(user_id, is_active)

### Шаг 1.15: Установить spatie/laravel-activitylog
```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
```
- Логирование действий админа: бан юзера, ручное изменение баланса, изменение настроек
- На моделях User, Setting, PromoCode: trait `LogsActivity`
- Просмотр логов в MoonShine Dashboard

### Шаг 1.16: Создать все Eloquent модели (13 штук)
Каждая модель с:
- `$fillable` / `$guarded`
- `$casts` (enum casts, datetime casts, integer casts)
- Relationships (belongsTo, hasMany, hasOne, morphTo)
- Scopes (где нужны: `scopeActive`, `scopeAvailableForUpgrade`, etc.)
- `SoftDeletes` на User и Skin
- `LogsActivity` (spatie) на User, Setting, PromoCode

Модели:
1. **User** — модифицировать существующую. Traits: `SoftDeletes`, `LogsActivity`. Relations: hasMany(Transaction, Deposit, Withdrawal, Upgrade, PromoCodeUsage, UserSkin), hasOne(ProvablyFairSeed, active)
2. **Skin** — trait: `SoftDeletes`. Relations: hasMany(SkinPrice, UserSkin, UpgradeItem)
3. **UserSkin** — relations: belongsTo(User, Skin). Scopes: available, inUpgrade. Это инвентарь юзера.
4. **SkinPrice** — relations: belongsTo(Skin)
5. **Transaction** — relations: belongsTo(User), morphTo(reference)
6. **Deposit** — relations: belongsTo(User)
7. **Withdrawal** — relations: belongsTo(User, UserSkin). UserSkin → Skin для отображения
8. **Upgrade** — relations: belongsTo(User, targetSkin:Skin), hasMany(UpgradeItem)
9. **UpgradeItem** — relations: belongsTo(Upgrade, UserSkin, Skin)
10. **Setting** — кастомные методы: `static get(key)`, `static set(key, value)`
11. **PromoCode** — relations: hasMany(PromoCodeUsage). Scopes: active, notExpired
12. **PromoCodeUsage** — relations: belongsTo(PromoCode, User)
13. **ProvablyFairSeed** — relations: belongsTo(User). Scope: active

### Шаг 1.17: Создать фабрики
- UserFactory (с steam_id, username, avatar, balance)
- SkinFactory (с market_hash_name, price, category, rarity)
- UserSkinFactory (с user_id, skin_id, price_at_acquisition, status)
- UpgradeFactory
- DepositFactory
- WithdrawalFactory
- TransactionFactory

### Шаг 1.18: Создать SettingsSeeder
Дефолтные значения:
- `house_edge` = 5.00
- `min_bet_amount` = 100 (1 RUB)
- `max_bet_amount` = 5000000 (50K RUB)
- `min_upgrade_chance` = 1.00
- `max_upgrade_chance` = 95.00
- `upgrade_cooldown` = 2
- `site_enabled` = true
- `withdrawals_enabled` = true
- `maintenance_message` = ""

### Шаг 1.19: Запустить миграции и проверить
```bash
php artisan migrate
php artisan db:seed --class=SettingsSeeder
php artisan ide-helper:models --nowrite
```

### Шаг 1.20: Коммит
`feat: add database schema — 13 migrations, 13 models, 10 enums, factories, seeders`

---

## Фаза 2: Steam Auth

### Шаг 2.1: Установить Steam Socialite провайдер
```bash
composer require socialiteproviders/steam
```

### Шаг 2.2: Настроить EventServiceProvider
Зарегистрировать `SocialiteWasCalled` listener для Steam провайдера.

### Шаг 2.3: Создать SteamAuthController
- `redirect()` — редирект на Steam OpenID
- `callback()` — получить Steam данные, найти/создать User, залогинить
- `logout()` — разлогинить

### Шаг 2.4: Создать AuthenticateViaSteamAction
- Принимает SteamUser от Socialite
- Ищет User по steam_id
- Создаёт нового если не найден (username, avatar_url, steam_id)
- Обновляет username/avatar если изменились
- Обновляет last_active_at
- Генерирует ProvablyFairSeed для нового пользователя

### Шаг 2.5: Прописать routes в auth.php
```php
Route::get('/auth/steam', [SteamAuthController::class, 'redirect'])->name('auth.steam');
Route::get('/auth/steam/callback', [SteamAuthController::class, 'callback']);
Route::post('/auth/logout', [SteamAuthController::class, 'logout'])->name('logout')->middleware('auth');
```

### Шаг 2.6: Настроить HandleInertiaRequests middleware
Shared data: `auth.user` (id, username, avatar_url, balance, trade_url, steam_id)

### Шаг 2.7: Создать UserController + профиль
- `GET /profile` → UserController@show → Inertia page (avatar, username, steam_id, trade_url, balance, stats)
- `PUT /profile/trade-url` → UserController@updateTradeUrl → UpdateTradeUrlRequest
  - Валидация: regex для Steam trade URL (`https://steamcommunity.com/tradeoffer/new/?partner=...&token=...`)
- `GET /profile/history` → UserController@history → пагинированный список транзакций + апгрейдов
  - Фильтры: тип (all/deposits/withdrawals/upgrades), дата
- Routes в web.php, middleware `auth`

### Шаг 2.8: Steam API resilience (автоматически сдвинуто)
- Обернуть Socialite callback в try/catch — при ошибке Steam показать "Steam временно недоступен"
- Сессия 120 минут — юзер не перелогинивается при каждом действии
- Retry middleware для Steam API calls (exponential backoff)

### Шаг 2.9: Тесты
- Feature test: callback с mock Socialite создаёт пользователя
- Feature test: повторный вход обновляет avatar/username
- Feature test: logout разлогинивает
- Feature test: Steam API down — graceful error, не 500
- Feature test: обновление trade_url — валидный URL принимается, невалидный — rejected
- Feature test: история транзакций — пагинация, фильтры

### Шаг 2.10: Коммит
`feat: Steam OpenID authentication + user profile`

---

## Фаза 3: Импорт скинов

### Шаг 3.1: Создать artisan command `skins:import`
- Принимает путь к `skins_index.json` как аргумент
- Читает JSON, парсит каждый скин
- Из `market_hash_name` извлекает: weapon_type, skin_name, exterior
- Конвертирует price (float RUB → int kopecks): `(int) round($price * 100)`
- Определяет category (weapon, sticker, knife, gloves, etc.) по имени
- Определяет rarity по категории/типу (если возможно)
- Batch upsert по 500 записей
- Прогресс-бар в консоли

### Шаг 3.2: Парсинг market_hash_name
Примеры:
- `"AK-47 | Redline (Field-Tested)"` → weapon_type: "AK-47", skin_name: "Redline", exterior: "FT"
- `"★ Karambit | Doppler (Factory New)"` → category: "knife", weapon_type: "Karambit", skin_name: "Doppler", exterior: "FN"
- `"Sticker | karrigan | Paris 2023"` → category: "sticker", skin_name: "karrigan | Paris 2023"

### Шаг 3.3: Копирование WebP файлов
- Скопировать из `/Users/danil/Desktop/projects/upgrade/parser/skins/` в `storage/app/public/skins/`
- `php artisan storage:link` (уже сделан)
- image_path в БД: `skins/{filename}.webp`

### Шаг 3.4: Создать `skins:dump` — SQL дамп из импортированных данных
После успешного импорта — сгенерировать SQL дамп для переносимости:

```bash
php artisan skins:dump
# Генерирует: database/dumps/skins.sql (INSERT INTO skins ...)
```

**Что делает:**
- Экспортирует все записи из таблицы `skins` в SQL файл
- Батчевые INSERT по 500 записей (MySQL max_allowed_packet safe)
- Файл коммитится в репо — любой разработчик может поднять БД без JSON и WebP
- Альтернатива: `php artisan skins:dump --format=seeder` → генерирует `SkinSeeder.php`

**Зачем:**
- Не зависим от `/upgrade/parser/skins_index.json` (другая папка, может пропасть)
- CI/CD может поднять тестовую БД без 31K картинок
- Новый разработчик делает `mysql skyforge < database/dumps/skins.sql` и работает

### Шаг 3.5: Тесты
- Unit test: парсинг market_hash_name
- Feature test: импорт из тестового JSON (5-10 скинов)
- Feature test: dump генерирует валидный SQL

### Шаг 3.6: Коммит
`feat: skins import + dump commands`

---

## Фаза 4: Каталог скинов + Админка

### Шаг 4.1: Создать SkinController
- `index()` — пагинированный список, **JSON response** (React страница будет в Фазе 9)
- `search()` — FULLTEXT поиск по market_hash_name, JSON response
- Оба endpoint'а будут использоваться как API внутри Inertia-страниц (через `axios` / `fetch`)

### Шаг 4.2: Создать SkinResource / SkinBriefResource
- SkinResource: все поля + image_url (полный URL через Storage::url)
- SkinBriefResource: id, market_hash_name, image_url, price, rarity_color (для карточек)

### Шаг 4.3: Создать SyncSkinPricesCommand
- `php artisan skins:sync-prices`
- Fetch `market.csgo.com/api/v2/prices/RUB.json`
- Upsert цен по market_hash_name
- Логировать в skin_prices только если цена изменилась > 5%
- Schedule: every 15 minutes

### Шаг 4.4: Настроить MoonShine ресурсы
- UserResource (список, бан/разбан, просмотр баланса и инвентаря; ручное изменение баланса — после Фазы 5)
- SkinResource (список, фильтры, цены)
- TransactionResource (только чтение)
- DepositResource, WithdrawalResource
- UpgradeResource (только чтение)
- SettingResource (редактирование)
- PromoCodeResource (CRUD)

### Шаг 4.5: MoonShine Dashboard
- Статистика: всего пользователей, активных сегодня
- Финансы: депозиты/выводы за день/неделю/месяц
- Игры: количество апгрейдов, win rate, прибыль

### Шаг 4.6: Кэширование
- Redis кэш для каталога скинов (15 мин TTL)
- Инвалидация после sync-prices

### Шаг 4.7: Тесты
- Feature: SkinController возвращает пагинированный список
- Feature: Поиск находит скины по имени
- Unit: SyncSkinPricesCommand (mock HTTP)

### Шаг 4.8: Коммит
`feat: skin catalog, price sync, MoonShine admin panel`

---

## Фаза 5: Баланс и депозиты

### Шаг 5.1: Создать CreditBalanceAction
- Принимает: User, amount (kopecks), TransactionType, reference
- DB::transaction + lockForUpdate
- Обновляет balance, total_deposited (если deposit)
- Создаёт Transaction запись
- Возвращает Transaction

### Шаг 5.2: Создать DebitBalanceAction
- Аналогично, но проверяет `balance >= amount`
- Кидает InsufficientBalanceException если не хватает
- Обновляет balance, total_withdrawn / total_upgraded

### Шаг 5.3: Создать CreateTransactionAction
- Создаёт запись в transactions с balance_before/balance_after snapshot

### Шаг 5.4: Создать PaymentProviderInterface
```php
interface PaymentProviderInterface {
    public function createPayment(int $amount, string $method, array $meta = []): PaymentDTO;
    public function verifyWebhook(Request $request): WebhookDTO;
    public function getPaymentStatus(string $providerId): string;
}
```

### Шаг 5.5: Создать StubPaymentProvider
- Имплементирует PaymentProviderInterface
- Сразу возвращает "completed" для dev-окружения
- Bind в AppServiceProvider

### Шаг 5.6: Создать routes/api.php + DepositController + CreateDepositAction
- `php artisan install:api` (создаёт routes/api.php в Laravel 13)
- Webhook endpoint: `POST /api/webhooks/payment` (без auth, с signature verification)
- Страница выбора метода и суммы (Inertia route)
- Создание deposit записи
- **Max pending deposits**: не более 5 pending deposits на юзера (защита от спама)

### Шаг 5.7: Создать CompleteDepositAction (с idempotency)
- Проверить `idempotency_key` — если deposit с таким ключом уже completed, вернуть 200 OK без повторного зачисления
- Внутри DB::transaction: обновить status → completed, вызвать CreditBalanceAction
- Это критическая защита от двойного зачисления при retry webhook'ов

### Шаг 5.8: Создать DepositObserver
- При смене status на 'completed': fire DepositCompleted event
- Баланс зачисляется через CompleteDepositAction, не через Observer (контроль транзакции)

### Шаг 5.9: Подготовка к реальному платёжному провайдеру
- Создать `PaymentDTO` (amount, method, redirect_url, provider_id)
- Создать `WebhookDTO` (provider_id, status, amount, signature_valid, raw_data)
- Документировать интерфейс: какие методы должен реализовать реальный провайдер
- В `config/skyforge.php` уже есть `payment.provider` — bind через AppServiceProvider
- Реальный провайдер подключается заменой одного класса:
  ```php
  // AppServiceProvider
  $this->app->bind(PaymentProviderInterface::class, match(config('skyforge.payment.provider')) {
      'stub' => StubPaymentProvider::class,
      'freekassa' => FreeKassaProvider::class,  // пример
      default => StubPaymentProvider::class,
  });
  ```
- UI страница депозита: выбор метода (СБП/крипта/скины), ввод суммы, редирект на платёжку

### Шаг 5.10: Промокоды
- PromoCodeController, ApplyPromoCodeRequest
- Валидация: активен, не истёк, не использован этим юзером, лимит не превышен

### Шаг 5.11: Тесты (TDD)
- Unit: CreditBalanceAction — корректный баланс, транзакция создана
- Unit: DebitBalanceAction — успех, InsufficientBalanceException
- Unit: Concurrent debit — pessimistic lock предотвращает race condition
- Feature: Deposit flow — создание, webhook, баланс обновлён
- **Feature: Idempotency — повторный webhook с тем же ключом НЕ зачисляет дважды**
- **Feature: Max pending deposits — 6-й pending deposit rejected**
- Feature: Промокод — применение, повторное использование rejected

### Шаг 5.12: Коммит
`feat: balance system, deposits, promo codes`

---

## Фаза 6: Апгрейд (TDD)

### Шаг 6.1: Тесты FIRST (RED)
Написать тесты ДО реализации:
- `CalculateChanceActionTest` — корректные шансы при разных bet/target соотношениях, house edge, min/max clamp
- `GenerateRollActionTest` — детерминированный результат с известными seeds
- `ProvablyFairServiceTest` — генерация seeds, HMAC verification
- `UpgradeServiceTest` — полный flow: validation → debit → roll → result → credit → events
- Edge cases: недостаточно баланса, цена скина изменилась, concurrent upgrades, min/max chance

### Шаг 6.2: Создать ProvablyFairService + Actions
- GenerateSeedPairAction — создаёт server_seed, server_seed_hash, default client_seed
- GenerateRollAction — HMAC-SHA256(serverSeed, clientSeed-nonce) → float [0,1)
- RevealSeedAction — показывает raw server_seed после ротации

### Шаг 6.3: Создать CalculateChanceAction
- Входные: bet_amount, target_price, User (для house_edge_override и chance_modifier)
- Определить effectiveHouseEdge: `user.house_edge_override ?? Setting::get('house_edge')`
- Формула: `baseChance = (betAmount / targetPrice) * (1 - effectiveHouseEdge/100) * 100`
- Применить модификатор: `finalChance = baseChance * user.chance_modifier`
- Clamp: `max(minChance, min(maxChance, finalChance))`
- Возвращает DTO с chance, multiplier, applied house_edge

> **Тесты**: проверить обычного юзера, стримера (override=0, modifier=1.2), подозрительного (override=8, modifier=0.9). Все три сценария.

### Шаг 6.4: Создать UpgradeService
Оркестрация (всё внутри одной DB::transaction):
1. Validate (CreateUpgradeRequest: `user_skin_ids[]`, `balance_amount`, `target_skin_id`)
2. Lock user (lockForUpdate)
3. **Lock user_skins** (lockForUpdate WHERE id IN user_skin_ids AND status = 'available')
   - Если какой-то скин не available → abort (кто-то уже использовал)
4. **Fetch fresh target skin price from DB** (не из фронта!)
5. Calculate total bet: `sum(user_skins[].price_at_acquisition) + balance_amount`
6. **Проверить: если цена цели изменилась > 10% от того что видел юзер → abort с новой ценой**
7. Calculate chance (CalculateChanceAction с user modifiers)
8. Get active seed pair, increment nonce
9. Generate roll (GenerateRollAction)
10. Determine result: `roll < chance/100` → WIN
11. **Mark bet skins**: `user_skins.status = 'burned'` (и при WIN, и при LOSE — ставка сгорает)
12. Debit balance (если balance_amount > 0)
13. If WIN: создать новый `UserSkin` с целевым скином (source = 'upgrade_win')
14. Create Upgrade + UpgradeItems records (с snapshot цен и user_skin_id)
15. Create Transaction(s) для balance portion
16. Fire UpgradeCompleted event
17. Return UpgradeResultDTO

> **Важно**: Фронтенд передаёт только `user_skin_ids[]` + `balance_amount` + `target_skin_id`. Все цены из БД.
> **Скины сгорают всегда** — и при WIN, и при LOSE. При WIN юзер получает новый скин (целевой).

### Шаг 6.5: Создать UpgradeController
- `store()` — принимает CreateUpgradeRequest, вызывает UpgradeService
- `history()` — список апгрейдов пользователя

### Шаг 6.6: Создать ProvablyFairController + routes
- `GET /provably-fair` — страница верификации (Inertia)
- `POST /provably-fair/client-seed` — смена client seed (ротирует seed pair, reveal старый)
- `POST /provably-fair/reveal` — показать raw server_seed текущей пары
- `GET /provably-fair/verify/{upgrade}` — проверить конкретный апгрейд
- UpdateClientSeedRequest — валидация (string, max 64)

### Шаг 6.7: Rate limiting
- Middleware: 1 upgrade per 2 seconds per user
- Redis-based через `Cache::lock()`

### Шаг 6.8: Запустить тесты (GREEN)
Все тесты должны пройти.

### Шаг 6.9: Рефакторинг (REFACTOR)
Упрощение, удаление дублей, оптимизация.

### Шаг 6.10: Коммит
`feat: upgrade game logic with provably fair (TDD)`

---

## Фаза 7: Real-time (WebSocket)

### Шаг 7.1: Настроить Laravel Reverb для production
- `config/reverb.php` — проверить настройки
- Broadcast driver = reverb в .env

### Шаг 7.2: Создать UpgradeCompleted event
- Implements ShouldBroadcastNow
- Channel: public `upgrades`
- Payload: UpgradeFeedResource (username, avatar, target_skin, chance, result)

### Шаг 7.3: Создать BalanceUpdated event
- Implements ShouldBroadcastNow
- Channel: private `user.{id}`
- Payload: `{ balance: int }`

### Шаг 7.4: Создать DepositCompleted + WithdrawalStatusChanged events
- Implements ShouldBroadcast (queued)
- Private user channel

### Шаг 7.5: Redis feed cache + LiveFeedController
- `feed:recent` — Redis LIST, LPUSH новый апгрейд, LTRIM до 50
- **LiveFeedController**:
  - `GET /api/live-feed` → последние 50 апгрейдов из Redis (JSON, без auth)
  - `GET /api/live-feed/history` → пагинированная история из БД (необязательно)
- **UpgradeFeedResource** — формат для feed: `{ id, username, avatar_url, target_skin_name, target_skin_image, chance, result, created_at }`
- **Listener `PushToLiveFeed`** — при UpgradeCompleted: LPUSH в Redis + LTRIM 50
- На фронте: initial load из `/api/live-feed`, потом real-time через Echo

### Шаг 7.6: Тесты
- Feature: UpgradeCompleted event broadcast при апгрейде
- Feature: BalanceUpdated event при изменении баланса
- Feature: LiveFeedController возвращает последние апгрейды
- Feature: Redis feed обновляется при новом апгрейде

### Шаг 7.7: Коммит
`feat: real-time events — live feed, balance updates via Reverb`

---

## Фаза 8: Вывод скинов

### Шаг 8.1: Создать TradeProviderInterface
```php
interface TradeProviderInterface {
    public function sendTradeOffer(string $tradeUrl, string $skinMarketHashName): TradeOfferDTO;
    public function checkTradeStatus(string $tradeOfferId): string;
}
```

### Шаг 8.2: Создать StubTradeProvider
- Для разработки — сразу "completed"

### Шаг 8.3: Создать WithdrawalController + Actions
- CreateWithdrawalAction — юзер выбирает `user_skin_id` из инвентаря (status = 'available')
  - Lock user_skin (lockForUpdate), проверить status = 'available'
  - Установить `user_skin.status = 'withdrawn'`
  - Создать withdrawal record с `user_skin_id`, `skin_id`, `amount = user_skin.price_at_acquisition`
  - **Баланс НЕ дебитится** — юзер отдаёт скин, а не деньги
- ProcessWithdrawalAction — send trade offer via provider (job)
- **Max pending withdrawals**: не более 3 pending/processing withdrawals на юзера
- **При failed**: вернуть скин → `user_skin.status = 'available'`

### Шаг 8.4: Создать ProcessWithdrawalJob
- Queued на `payments` queue
- Отправляет trade offer, обновляет статус
- Retry 3 раза с exponential backoff

### Шаг 8.5: EnsureTradeUrlSet middleware
- Проверяет что у пользователя задан trade_url
- Если нет — редирект на профиль

### Шаг 8.6: WithdrawalObserver
- При status → failed: вернуть скин в инвентарь (`user_skin.status = 'available'`)
- При status → completed: update `user.total_withdrawn`, `user_skin` остаётся `withdrawn`

### Шаг 8.7: Тесты
- Feature: withdrawal flow — debit, send, complete
- Feature: failed withdrawal — refund
- Feature: no trade_url — middleware redirect

### Шаг 8.8: Коммит
`feat: skin withdrawal system with trade offers`

---

## Фаза 9: Frontend (React + Inertia)

### Шаг 9.1: Портировать дизайн-токены из style.css
- CSS variables → Tailwind config или CSS modules
- Цвета: --black (#0a0a0a), --accent (#a3e635), --card (#111111), etc.
- Шрифт: Inter
- Rarity цвета: #b0c3d9, #4b69ff, #8847ff, #d32ce6, #eb4b4b

### Шаг 9.2: Создать Layout компоненты
- AppLayout.tsx — обёртка с Navbar + LiveFeed sidebar + Footer
- Navbar.tsx — логотип, навигация, баланс, депозит, аватар
- Footer.tsx
- Ticker.tsx — бегущая строка последних апгрейдов

### Шаг 9.3: TypeScript типы
- `types/models.ts` — User, Skin, Upgrade, Transaction, etc.
- `types/inertia.d.ts` — PageProps с auth.user

### Шаг 9.4: Страница Upgrade (главная)
- BetPanel — выбор скинов из СВОЕГО инвентаря (user_skins), добавление баланса слайдером, итого ставка
- ChanceRing — круговой индикатор шанса (CSS conic-gradient)
- TargetPanel — выбранный целевой скин, потенциальный выигрыш
- MultiplierPills — кнопки x2, x3, x5, x10
- BalanceSlider — слайдер добавления баланса
- SkinSelector — модалка выбора целевого скина (поиск + фильтры)
- UpgradeAnimation — анимация результата (win/lose)

### Шаг 9.5: Инвентарь
- InventorySection — табы "Мой инвентарь" (user_skins, status=available) / "Каталог скинов" (skins, для выбора цели)
- SkinGrid — сетка карточек скинов
- SkinCard — карточка с rarity полоской, картинкой, именем, ценой
- SkinFilters — поиск, фильтр по оружию, сортировка

### Шаг 9.6: Live Feed sidebar
- LiveFeed.tsx — правый сайдбар, список последних апгрейдов
- useLiveFeed hook — подписка на Echo channel `upgrades`

### Шаг 9.7: Остальные страницы
- Login — кнопка "Войти через Steam"
- Profile — avatar, username, Steam ID, trade_url (editable), balance, статистика (всего депозитов/выводов/апгрейдов/выиграно), инвентарь скинов
- Profile/History — табы: все транзакции / депозиты / выводы / апгрейды, пагинация, фильтр по дате
- Deposit — выбор метода (СБП/крипта/скины), ввод суммы, промокод, redirect на платёжку, статус pending
- Withdrawal — выбор скина из инвентаря (user_skins) для вывода через trade offer
- ProvablyFair — верификация, смена client seed
- Rules, Privacy — статические страницы

### Шаг 9.8: Hooks
- useUpgrade — state machine апгрейда (idle→selecting→confirming→animating→result)
- useBalance — Echo подписка на private-user.{id}, обновление баланса
- useLiveFeed — Echo подписка на public upgrades
- useSkinSearch — debounced поиск скинов
- useSound — звуки win/lose

### Шаг 9.9: Коммит
`feat: React frontend — upgrade page, layout, live feed`

---

## Фаза 10: Polish и Production

### Шаг 10.1: Responsive design
- Mobile/tablet breakpoints
- Сайдбар live feed — скрыть на мобильных

### Шаг 10.2: Error handling
- Custom error pages (403, 404, 500, 503)
- Global error boundary в React

### Шаг 10.3: Security
- Rate limiting на все endpoints
- CSRF protection verified
- XSS prevention audit
- SQL injection check (all queries via Eloquent)

### Шаг 10.4: Performance
- N+1 query audit (Laravel Debugbar)
- Eager loading review
- Database indexes verified
- Redis caching layer

### Шаг 10.5: Logging
- Structured JSON logs
- Separate channels: payments, games, errors
- Sentry integration (optional)

### Шаг 10.6: Deployment
- Nginx config (SSL, WebSocket proxy, static files)
- Supervisor config (Horizon, Reverb)
- Zero-downtime deploy script
- Backup strategy

### Шаг 10.7: Final testing pass
- Full feature test suite green
- Manual smoke test
- Load test (optional)

### Шаг 10.8: Коммит
`chore: production readiness — security, performance, deployment`

---

## Порядок работы

Строго последовательно:
1. **Фаза 1** → БД и модели (фундамент всего)
2. **Фаза 2** → Steam Auth (нужен для тестирования всего остального)
3. **Фаза 3** → Импорт скинов (нужны для апгрейда и каталога)
4. **Фаза 4** → Каталог + Админка (нужен для управления)
5. **Фаза 5** → Баланс + Депозиты (нужен для апгрейда)
6. **Фаза 6** → Апгрейд — TDD (ядро приложения)
7. **Фаза 7** → Real-time (зависит от апгрейда)
8. **Фаза 8** → Вывод скинов (зависит от баланса)
9. **Фаза 9** → Frontend (зависит от всего backend)
10. **Фаза 10** → Polish (финал)

Каждая фаза заканчивается коммитом и прохождением тестов.

---

## Известные риски и решения

| Риск | Вероятность | Решение |
|------|-------------|---------|
| **Двойное зачисление депозита** | Высокая (webhook retry) | `idempotency_key` UNIQUE в deposits, проверка перед зачислением |
| **Race condition баланса** | Высокая (concurrent requests) | `lockForUpdate()` в DB::transaction, CHECK constraint >= 0 |
| **Steam API downtime** | Средняя (каждый вторник) | Длинные сессии (120 мин), graceful error page, retry с backoff |
| **Цена изменилась во время апгрейда** | Средняя | Цены из БД в транзакции, snapshot в upgrade record, abort при > 10% drift |
| **Hard delete финансовых данных** | Низкая (админ ошибка) | SoftDeletes на User/Skin, audit log всех действий админа |
| **Бот-абьюз (micro-grinding)** | Средняя | Min bet 1 RUB, rate limit 1/2сек, house edge математически защищает |
| **31K скинов тормозят UI** | Высокая (без пагинации) | Серверная пагинация 50 шт, FULLTEXT search, react-virtuoso если надо |
| **WebSocket не тянет нагрузку** | Низкая (на старте) | Reverb ок до ~1000 conn, потом переезд на Soketi/Centrifugo |
| **Потеря 31K картинок при деплое** | Средняя | Local disk → S3/MinIO позже, `SKYFORGE_SKINS_DISK` уже абстрагирован |
| **Невалидная Provably Fair** | Низкая (баг) | Детерминированные тесты с фикс. seeds, пользователь может проверить |

---

## Фаза 11: Backend Refactor (SOLID/KISS/DRY)

**Дата старта:** 2026-04-30. **Спека:** `docs/superpowers/specs/2026-04-30-backend-refactor-design.md`.

Цель: тонкие контроллеры (≤3 строки тела), `spatie/laravel-data` вместо FormRequest, атомарные Actions, Services-оркестраторы, 7 Observers, throttling на все public endpoints. Подход — вертикальные срезы (1 PR = 1 домен).

### Шаг 11.1: Setup (PR #1)

- [ ] `composer require spatie/laravel-data`
- [ ] `AppServiceProvider::boot()`: named `RateLimiter::for()` для `promo` (5/min + 30/hour), `deposit` (10/min), `withdraw` (5/min), `tradeUrl` (5/min), `sellSkins` (30/min), `auth` (10/min IP), `seed` (10/min), `api` (60/min IP), `feed` (30/min IP)
- [ ] `TransactionObserver` зарегистрирован — immutability ledger (`updating`/`deleting` → throw)
- [ ] Характеризационные тесты на финансы в `tests/Feature/Characterization/`: concurrent_upgrade, concurrent_withdrawal, duplicate_deposit_webhook_idempotency, provably_fair_determinism

### Шаг 11.2: User domain (PR #2)

- [ ] Actions: `UpdateTradeUrlAction`, `SellSkinsAction`, `CalculateSellPriceAction`, `RemoveSkinFromInventoryAction`, `CreditBalanceAction` (если нет), `ValidatePromoCodeAction`, `ApplyPromoBonusAction`, `RecordPromoUsageAction`
- [ ] Services: `MarketService::sellSkins()`, `PromoCodeService::redeem()`
- [ ] Data: `UpdateTradeUrlData` (regex Steam), `SellSkinsData`, `RedeemPromoData`
- [ ] `UserObserver`: `creating` (capture IP/UTM), `updating` (ban → invalidate sessions)
- [ ] `UserController` тонкий: `show()`, `updateTradeUrl()`, `sellSkins()`, `deposits()`, `redeemPromo()`, `history()`
- [ ] Throttle: `tradeUrl`, `sellSkins`, `promo` middleware на route

### Шаг 11.3: Skin/Market domain (PR #3)

- [ ] Actions: `BuySkinAction`, `AddSkinToInventoryAction`, `DebitBalanceAction` (если нет)
- [ ] Service: `MarketService::buy()`
- [ ] Data: `IndexSkinsData`, `SearchSkinsData`, `BuySkinData`
- [ ] `SkinPriceObserver`: `created` → invalidate market cache
- [ ] `UserSkinObserver`: `created` → activity log
- [ ] `SkinController` тонкий
- [ ] Throttle: `api` на index/search/buy

### Шаг 11.4: Deposit domain (PR #4)

- [ ] Actions: `VerifyWebhookSignatureAction` (новый); `CreateDepositAction`, `CompleteDepositAction` (есть, проверить ответственность)
- [ ] Service: `DepositService::initiate()`, `DepositService::handleWebhook()`
- [ ] Data: `CreateDepositData` (с лимитами per-day), `WebhookData`
- [ ] `DepositObserver`: `creating` (block if user banned), `updated` (pending→completed bumps `total_deposited`)
- [ ] `DepositController` тонкий
- [ ] Throttle: `deposit` на store, без throttle на webhook

### Шаг 11.5: Upgrade domain (PR #5)

- [ ] `UpgradeService` разбить на: `CalculateChanceAction`, `RollUpgradeAction`, `ApplyUpgradeResultAction`
- [ ] Data: `CreateUpgradeData`
- [ ] `UpgradeObserver`: `created` → bump `total_upgraded` + (win → `total_won`)
- [ ] `UpgradeController` тонкий
- [ ] Throttle: текущий `upgrade` лимит сохраняется

### Шаг 11.6: Withdrawal domain (PR #6)

- [ ] `CreateWithdrawalAction` разбить на: `ValidateWithdrawableAmountAction`, `LockSkinFromInventoryAction`, `CreateWithdrawalRecordAction`, `DispatchTradeOfferJob`
- [ ] Service: `WithdrawalService::create()`
- [ ] Data: `CreateWithdrawalData`
- [ ] `WithdrawalObserver`: `updated` (processing→completed bumps `total_withdrawn`)
- [ ] `WithdrawalController` тонкий
- [ ] Throttle: `withdraw`

### Шаг 11.7: ProvablyFair + LiveFeed + Steam Auth (PR #7)

- [ ] Actions: `RotateClientSeedAction`; `AuthenticateViaSteamAction` разбить на `FindOrCreateUserAction` + `CaptureUtmAction` + `IssueSessionAction`
- [ ] Data: `LoginCallbackData`
- [ ] `ProvablyFairController`, `SteamAuthController`, `LiveFeedController` тонкие
- [ ] Throttle: `seed`, `auth`, `feed`

### Definition of Done (на каждом PR)

- Все Actions/Data/Observers/Service созданы по спеке.
- Контроллер ≤3 строки тела метода.
- `vendor/bin/pint --dirty --format agent` → `pass`.
- `php artisan test` → зелёный.
- Throttle-middleware на всех указанных endpoints.
- Smoke через браузер: happy-path работает.

### Out of scope

2FA для admin, login history, soft-delete restore UI, real payment/trade providers, реструктуризация моделей.
