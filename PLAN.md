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

### Апгрейд (основная механика)
- Пользователь ставит скины из инвентаря + опционально добавляет баланс
- Выбирает целевой скин (дороже ставки)
- Шанс = `(betAmount / targetPrice) * (1 - houseEdge/100) * 100`
- Зажимается в рамки `min_chance`...`max_chance` (1%...95%)
- Результат определяется через Provably Fair (HMAC-SHA256)
- WIN → баланс += цена целевого скина
- LOSE → ставка сгорает

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

### Шаг 1.3: Модифицировать миграцию users
Стандартная Laravel миграция `create_users_table` уже есть. Создать новую миграцию `modify_users_table_for_skyforge`:

- Удалить: `email`, `email_verified_at`, `password` (не нужны — Steam auth)
- Добавить: `steam_id` (VARCHAR 20, UNIQUE), `username` (VARCHAR 255), `avatar_url` (VARCHAR 512), `trade_url` (VARCHAR 512, NULLABLE), `balance` (BIGINT UNSIGNED DEFAULT 0), `total_deposited` (BIGINT UNSIGNED DEFAULT 0), `total_withdrawn` (BIGINT UNSIGNED DEFAULT 0), `total_upgraded` (BIGINT UNSIGNED DEFAULT 0), `total_won` (BIGINT UNSIGNED DEFAULT 0), `is_banned` (BOOLEAN DEFAULT false), `ban_reason` (VARCHAR 512, NULLABLE), `is_admin` (BOOLEAN DEFAULT false), `last_active_at` (TIMESTAMP, NULLABLE)

### Шаг 1.4: Создать миграцию skins
```
id, market_hash_name (VARCHAR 255, UNIQUE), weapon_type (VARCHAR 64, NULLABLE),
skin_name (VARCHAR 255, NULLABLE), exterior (ENUM через string), rarity (VARCHAR 32, NULLABLE),
rarity_color (VARCHAR 7, NULLABLE), category (VARCHAR 64, NULLABLE),
image_path (VARCHAR 255), price (BIGINT UNSIGNED DEFAULT 0),
price_updated_at (TIMESTAMP, NULLABLE), is_active (BOOLEAN DEFAULT true),
is_available_for_upgrade (BOOLEAN DEFAULT true), timestamps
```
Индексы: UNIQUE(market_hash_name), INDEX(category, price), INDEX(is_active, is_available_for_upgrade, price), FULLTEXT(market_hash_name)

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
provider_data (JSON, NULLABLE), completed_at (TIMESTAMP, NULLABLE), timestamps
```
Индексы: INDEX(user_id, status), INDEX(provider_id), INDEX(status)

### Шаг 1.8: Создать миграцию withdrawals
```
id, user_id (FK), skin_id (FK), amount (BIGINT UNSIGNED),
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
result (string — enum), roll_value (DECIMAL 20,18),
client_seed (VARCHAR 64), server_seed (VARCHAR 128), server_seed_raw (VARCHAR 128),
nonce (BIGINT UNSIGNED), is_revealed (BOOLEAN DEFAULT false), created_at (TIMESTAMP)
```
Без updated_at (immutable кроме is_revealed). Индексы: INDEX(user_id, created_at), INDEX(result), INDEX(created_at)

### Шаг 1.10: Создать миграцию upgrade_items
```
id, upgrade_id (FK CASCADE), skin_id (FK), price (BIGINT UNSIGNED)
```
Индекс: INDEX(upgrade_id)

### Шаг 1.11: Создать миграцию settings
```
id, key (VARCHAR 64, UNIQUE), value (TEXT), type (string — enum: string/integer/float/boolean/json),
description (VARCHAR 255, NULLABLE), updated_at (TIMESTAMP)
```

### Шаг 1.12: Создать миграции promo_codes + promo_code_usages
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

### Шаг 1.13: Создать миграцию provably_fair_seeds
```
id, user_id (FK), client_seed (VARCHAR 64), server_seed (VARCHAR 128),
server_seed_hash (VARCHAR 128), nonce (BIGINT UNSIGNED DEFAULT 0),
is_active (BOOLEAN DEFAULT true), timestamps
```
Индекс: INDEX(user_id, is_active)

### Шаг 1.14: Создать все Eloquent модели (12 штук)
Каждая модель с:
- `$fillable` / `$guarded`
- `$casts` (enum casts, datetime casts, integer casts)
- Relationships (belongsTo, hasMany, hasOne, morphTo)
- Scopes (где нужны: `scopeActive`, `scopeAvailableForUpgrade`, etc.)

Модели:
1. **User** — модифицировать существующую. Relations: hasMany(Transaction, Deposit, Withdrawal, Upgrade, PromoCodeUsage), hasOne(ProvablyFairSeed, active)
2. **Skin** — relations: hasMany(SkinPrice, UpgradeItem)
3. **SkinPrice** — relations: belongsTo(Skin)
4. **Transaction** — relations: belongsTo(User), morphTo(reference)
5. **Deposit** — relations: belongsTo(User)
6. **Withdrawal** — relations: belongsTo(User, Skin)
7. **Upgrade** — relations: belongsTo(User, targetSkin:Skin), hasMany(UpgradeItem)
8. **UpgradeItem** — relations: belongsTo(Upgrade, Skin)
9. **Setting** — кастомные методы: `static get(key)`, `static set(key, value)`
10. **PromoCode** — relations: hasMany(PromoCodeUsage). Scopes: active, notExpired
11. **PromoCodeUsage** — relations: belongsTo(PromoCode, User)
12. **ProvablyFairSeed** — relations: belongsTo(User). Scope: active

### Шаг 1.15: Создать фабрики
- UserFactory (с steam_id, username, avatar, balance)
- SkinFactory (с market_hash_name, price, category, rarity)
- UpgradeFactory
- DepositFactory
- WithdrawalFactory
- TransactionFactory

### Шаг 1.16: Создать SettingsSeeder
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

### Шаг 1.17: Запустить миграции и проверить
```bash
php artisan migrate
php artisan db:seed --class=SettingsSeeder
php artisan ide-helper:models --nowrite
```

### Шаг 1.18: Коммит
`feat: add database schema — 12 migrations, models, enums, factories, seeders`

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

### Шаг 2.7: Тесты
- Feature test: callback с mock Socialite создаёт пользователя
- Feature test: повторный вход обновляет avatar/username
- Feature test: logout разлогинивает

### Шаг 2.8: Коммит
`feat: Steam OpenID authentication`

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

### Шаг 3.4: Тесты
- Unit test: парсинг market_hash_name
- Feature test: импорт из тестового JSON (5-10 скинов)

### Шаг 3.5: Коммит
`feat: skins import command — parse and import 31K skins from JSON`

---

## Фаза 4: Каталог скинов + Админка

### Шаг 4.1: Создать SkinController
- `index()` — пагинированный список, Inertia::render
- `search()` — FULLTEXT поиск по market_hash_name, возврат JSON

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
- UserResource (список, бан/разбан, изменение баланса)
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

### Шаг 5.6: Создать DepositController + CreateDepositAction
- Страница выбора метода и суммы
- Создание deposit записи
- Webhook endpoint в routes/api.php

### Шаг 5.7: Создать DepositObserver
- При смене status на 'completed': CreditBalanceAction
- Fire DepositCompleted event

### Шаг 5.8: Промокоды
- PromoCodeController, ApplyPromoCodeRequest
- Валидация: активен, не истёк, не использован этим юзером, лимит не превышен

### Шаг 5.9: Тесты (TDD)
- Unit: CreditBalanceAction — корректный баланс, транзакция создана
- Unit: DebitBalanceAction — успех, InsufficientBalanceException
- Unit: Concurrent debit — pessimistic lock предотвращает race condition
- Feature: Deposit flow — создание, webhook, баланс обновлён
- Feature: Промокод — применение, повторное использование rejected

### Шаг 5.10: Коммит
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
- Входные: bet_amount, target_price, house_edge
- Формула: `(betAmount / targetPrice) * (1 - houseEdge/100) * 100`
- Clamp: `max(minChance, min(maxChance, chance))`
- Возвращает DTO с chance, multiplier

### Шаг 6.4: Создать UpgradeService
Оркестрация:
1. Validate (CreateUpgradeRequest)
2. Lock user (lockForUpdate)
3. Calculate total bet (skin prices + balance amount)
4. Calculate chance (CalculateChanceAction)
5. Get active seed pair, increment nonce
6. Generate roll (GenerateRollAction)
7. Determine result: `roll < chance/100` → WIN
8. Debit balance (если balance portion)
9. If WIN: credit target skin price to balance
10. Create Upgrade + UpgradeItems records
11. Create Transaction(s)
12. Fire UpgradeCompleted event
13. Return UpgradeResultDTO

### Шаг 6.5: Создать UpgradeController
- `store()` — принимает CreateUpgradeRequest, вызывает UpgradeService
- `history()` — список апгрейдов пользователя

### Шаг 6.6: Rate limiting
- Middleware: 1 upgrade per 2 seconds per user
- Redis-based через `Cache::lock()`

### Шаг 6.7: Запустить тесты (GREEN)
Все тесты должны пройти.

### Шаг 6.8: Рефакторинг (REFACTOR)
Упрощение, удаление дублей, оптимизация.

### Шаг 6.9: Коммит
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

### Шаг 7.5: Redis feed cache
- `feed:recent` — Redis LIST, LPUSH новый апгрейд, LTRIM до 50
- Используется для initial load live feed при открытии страницы

### Шаг 7.6: Тесты
- Feature: UpgradeCompleted event broadcast при апгрейде
- Feature: BalanceUpdated event при изменении баланса

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
- CreateWithdrawalAction — debit balance, create withdrawal record
- ProcessWithdrawalAction — send trade offer via provider (job)

### Шаг 8.4: Создать ProcessWithdrawalJob
- Queued на `payments` queue
- Отправляет trade offer, обновляет статус
- Retry 3 раза с exponential backoff

### Шаг 8.5: EnsureTradeUrlSet middleware
- Проверяет что у пользователя задан trade_url
- Если нет — редирект на профиль

### Шаг 8.6: WithdrawalObserver
- При status → failed: refund (CreditBalanceAction)
- При status → completed: update total_withdrawn

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
- BetPanel — drop zone для скинов, добавление баланса, итого ставка
- ChanceRing — круговой индикатор шанса (CSS conic-gradient)
- TargetPanel — выбранный целевой скин, потенциальный выигрыш
- MultiplierPills — кнопки x2, x3, x5, x10
- BalanceSlider — слайдер добавления баланса
- SkinSelector — модалка выбора целевого скина (поиск + фильтры)
- UpgradeAnimation — анимация результата (win/lose)

### Шаг 9.5: Инвентарь
- InventorySection — табы "Мой инвентарь" / "Магазин скинов"
- SkinGrid — сетка карточек скинов
- SkinCard — карточка с rarity полоской, картинкой, именем, ценой
- SkinFilters — поиск, фильтр по оружию, сортировка

### Шаг 9.6: Live Feed sidebar
- LiveFeed.tsx — правый сайдбар, список последних апгрейдов
- useLiveFeed hook — подписка на Echo channel `upgrades`

### Шаг 9.7: Остальные страницы
- Login — кнопка "Войти через Steam"
- Profile — avatar, username, trade_url, history
- Deposit — выбор метода, сумма
- Withdrawal — выбор скина для вывода
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
