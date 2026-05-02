# Backend Refactor — Design Spec

**Дата:** 2026-04-30
**Статус:** approved
**Скоуп:** полный рефакторинг бэкенда: тонкие контроллеры (≤3 строки тела метода), Data вместо FormRequest, атомарные Actions, Services как оркестраторы, Observers, throttling, security hardening.

---

## 1. Цель

Привести бэкенд в соответствие с SOLID/KISS/DRY:
- Каждый файл — одна ответственность.
- Контроллеры — оркестратор HTTP-уровня, не более 3 строк тела метода.
- Валидация и форма данных — единый источник правды (`spatie/laravel-data`).
- Финансовые операции — модульные атомарные Actions, объединённые в Services с явными транзакциями.
- Все эндпоинты под throttling, чувствительные — двойной (per-minute + per-hour).
- Финансовые модели защищены через Observer'ы (immutable ledger, автокаунтеры, инвалидация кэшей).

## 2. Архитектура

### 2.1 Слои

```
Route ─→ Controller (≤3 строки)
         ├─ Принимает Data (валидация автоматом через spatie/laravel-data)
         ├─ Вызывает Action или Service
         └─ Возвращает Inertia/Redirect/JSON

Action (атомарная операция)
   ├─ ОДИН public execute()
   ├─ Принимает Data или примитивы, возвращает DTO/модель/null
   ├─ Не лочит сам — лочит Service-оркестратор
   └─ Stateless

Service (оркестратор сложных flow)
   ├─ Координирует несколько Actions
   ├─ Открывает DB::transaction() + lockForUpdate()
   ├─ Вызывается только из Controllers
   └─ Stateless

Observer (реакция на события моделей)
   ├─ Только хуки модели (creating, updated и т.д.)
   ├─ Не вызывается напрямую из кода
   └─ 1-3 метода на observer
```

### 2.2 Жёсткие правила

1. **Контроллер не делает SQL.** Никаких `Model::find(...)`, `where(...)` в контроллере. Только route-model binding и Action call.
2. **Action не вызывает другие Actions** (это работа Service). Action в Action — запрет.
3. **DB::transaction()** только в Service. Action работает в транзакции родительского Service.
4. **Data → Action** ← одностороннее. Action никогда не возвращает Data, только DTO/модель/null.
5. **Observer не вызывает Service** — иначе цикл `save() → observer → service → save()`. Только: bump counters, validate, dispatch event.
6. **Все throttling-имена** в одном месте — `app/Providers/AppServiceProvider.php::boot()`. На route — `throttle:promo`, `throttle:withdraw`. Никаких голых чисел.

### 2.3 Структура директорий после рефакторинга

```
app/
├── Actions/
│   ├── Auth/        AuthenticateViaSteamAction, FindOrCreateUserAction, IssueSessionAction
│   ├── Balance/     CreditBalanceAction, DebitBalanceAction
│   ├── Deposit/     CreateDepositAction, CompleteDepositAction, VerifyWebhookSignatureAction
│   ├── Inventory/   AddSkinToInventoryAction, RemoveSkinFromInventoryAction, LockSkinFromInventoryAction
│   ├── Promo/       ValidatePromoCodeAction, ApplyPromoBonusAction, RecordPromoUsageAction
│   ├── ProvablyFair/ GenerateSeedPairAction, RotateClientSeedAction
│   ├── Skin/        BuySkinAction, CalculateSellPriceAction
│   ├── Transaction/ CreateTransactionAction
│   ├── Upgrade/     CalculateChanceAction, RollUpgradeAction, ApplyUpgradeResultAction
│   ├── User/        UpdateTradeUrlAction, UpdateLastActiveAction, CaptureUtmAction
│   └── Withdrawal/  CreateWithdrawalAction, ValidateWithdrawableAmountAction,
│                    ApproveWithdrawalAction, RejectWithdrawalAction
├── Data/                          ← spatie/laravel-data
│   ├── Auth/        LoginCallbackData
│   ├── Deposit/     CreateDepositData, WebhookData
│   ├── Promo/       RedeemPromoData
│   ├── Profile/     UpdateTradeUrlData, SellSkinsData
│   ├── Skin/        IndexSkinsData, SearchSkinsData, BuySkinData
│   ├── Upgrade/     CreateUpgradeData
│   └── Withdrawal/  CreateWithdrawalData
├── DTOs/                          ← результаты Action/Service (как сейчас)
├── Observers/
│   ├── UserObserver.php
│   ├── TransactionObserver.php
│   ├── UpgradeObserver.php
│   ├── DepositObserver.php
│   ├── WithdrawalObserver.php
│   ├── SkinPriceObserver.php
│   └── UserSkinObserver.php
├── Services/
│   ├── UpgradeService.php
│   ├── DepositService.php          (новый)
│   ├── WithdrawalService.php       (новый)
│   ├── PromoCodeService.php        (новый)
│   ├── MarketService.php           (новый)
│   └── ... (Stub providers, Admin services)
└── Http/
    ├── Controllers/                (тонкие, ≤3 строки)
    ├── Middleware/
    └── Resources/                  (Eloquent API resources, опционально)
```

## 3. Throttling — пороги

Определены в `app/Providers/AppServiceProvider.php::boot()` через `RateLimiter::for()`:

| Имя | Лимит | Endpoint(ы) |
|---|---|---|
| `promo` | **5/min + 30/hour per user** | `POST /profile/promo` |
| `deposit` | 10/min per user | `POST /deposit` |
| `withdraw` | 5/min per user | `POST /withdrawal` |
| `tradeUrl` | 5/min per user | `PUT /profile/trade-url` |
| `sellSkins` | 30/min per user | `POST /profile/sell-skins` |
| `auth` | 10/min per IP | `POST /auth/steam/callback` |
| `seed` | 10/min per user | `POST /provably-fair/client-seed` |
| `api` | 60/min per IP | `GET /api/skins`, `GET /api/skins/search`, `POST /market/buy` |
| `feed` | 30/min per IP | `GET /api/live-feed` |
| `upgrade` | (текущий лимит сохраняется) | `POST /upgrade` |

**Без throttling**: `POST /api/webhooks/payment` — защита через verify подписи провайдера.

## 4. Per-PR breakdown

### PR #1 — Setup & Infrastructure

- `composer require spatie/laravel-data`.
- `AppServiceProvider::boot()`: все `RateLimiter::for()` определены.
- `app/Observers/TransactionObserver.php` зарегистрирован — immutability ledger:
  - `creating`: validate fields.
  - `updating`, `deleting`: `throw new RuntimeException('Transactions are immutable')`.
- Базовый `app/Data/BaseData.php` (опц.) с общими атрибутами.
- Характеризационные тесты на финансы (`tests/Feature/Characterization/`):
  - `concurrent_upgrade_does_not_double_spend()`
  - `concurrent_withdrawal_respects_lockForUpdate()`
  - `duplicate_deposit_webhook_is_idempotent()`
  - `provably_fair_same_seed_pair_yields_same_result()`

### PR #2 — User domain

| Method | Actions | Data | Throttle |
|---|---|---|---|
| `show()` | (read-only) | — | — |
| `updateTradeUrl()` | `UpdateTradeUrlAction` | `UpdateTradeUrlData` (regex Steam: `^https://steamcommunity\.com/tradeoffer/new/\?partner=\d+&token=\w{8}$`) | `tradeUrl` |
| `sellSkins()` | `SellSkinsAction` (в `MarketService`): `CalculateSellPriceAction` + `RemoveSkinFromInventoryAction`+ `CreditBalanceAction` + `CreateTransactionAction` | `SellSkinsData` (validated user_skin_ids принадлежат юзеру) | `sellSkins` |
| `deposits()` | (read JSON) | — | — |
| `redeemPromo()` | `PromoCodeService::redeem()`: `ValidatePromoCodeAction` + `ApplyPromoBonusAction` + `RecordPromoUsageAction` + `CreditBalanceAction` (если bonus type=balance) | `RedeemPromoData` | `promo` |
| `history()` | (read-only) | — | — |

`UserObserver`:
- `creating`: захват `registration_ip`, `utm_*` cookies, привязка `utm_mark_id`.
- `updating`: если `is_banned` стало `true` → invalidate sessions, обнулить `last_active_at`.

### PR #3 — Skin/Market domain

| Method | Actions | Data | Throttle |
|---|---|---|---|
| `market()` | (read) | — | — |
| `index()` | (read JSON) | `IndexSkinsData` (фильтры) | `api` |
| `search()` | (read JSON) | `SearchSkinsData` | `api` |
| `buy()` | `MarketService::buy()`: `BuySkinAction` (внутри: `DebitBalanceAction` + `AddSkinToInventoryAction` + `CreateTransactionAction`) | `BuySkinData` | `api` |

`SkinPriceObserver`: `created` → invalidate `Cache::tags(['market'])`.
`UserSkinObserver`: `created` → activity log entry «получил скин X из источника Y».

### PR #4 — Deposit domain

| Method | Actions | Data | Throttle |
|---|---|---|---|
| `create()` | (read) | — | — |
| `config()` | (read JSON) | — | — |
| `store()` | `DepositService::initiate()`: `CreateDepositAction` (есть) + payment provider call | `CreateDepositData` (метод, сумма, валидация min/max, лимиты per-day) | `deposit` |
| `webhook()` | `DepositService::handleWebhook()`: `VerifyWebhookSignatureAction` + `CompleteDepositAction` (есть) | `WebhookData` | без throttle |

`DepositObserver`:
- `creating`: запрет если `User.is_banned`.
- `updated`: при `pending → completed` бампит `User.total_deposited`.

### PR #5 — Upgrade domain

`UpgradeService` (есть) → разбить на:
- `CalculateChanceAction` — расчёт шанса с учётом `chance_modifier`/`house_edge_override`.
- `RollUpgradeAction` — provably-fair roll.
- `ApplyUpgradeResultAction` — write `Upgrade` row + `Transaction` + (win → `AddSkinToInventoryAction`).

| Method | Actions | Data | Throttle |
|---|---|---|---|
| `index()` | (read) | — | — |
| `store()` | `UpgradeService::execute(CreateUpgradeData)` | `CreateUpgradeData` | `upgrade` (текущий) |

`UpgradeObserver`: `created` → bump `User.total_upgraded`; при `result=win` → bump `User.total_won`.

### PR #6 — Withdrawal domain

`CreateWithdrawalAction` (есть) → разбить на:
- `ValidateWithdrawableAmountAction` — баланс, лимиты, статус юзера.
- `LockSkinFromInventoryAction` — пометить скин как `in_withdrawal`.
- `CreateWithdrawalRecordAction` — insert row.
- `DispatchTradeOfferJob` — отдельный Job, шлёт offer через `TradeProviderInterface`.

`WithdrawalService::create()` оркестрирует.

| Method | Actions | Data | Throttle |
|---|---|---|---|
| `store()` | `WithdrawalService::create(CreateWithdrawalData)` | `CreateWithdrawalData` | `withdraw` |

`WithdrawalObserver`: `updated` → при `processing → completed` бампит `User.total_withdrawn`.

### PR #7 — ProvablyFair + LiveFeed + Steam Auth

ProvablyFair:
- `index()` — read-only.
- `updateClientSeed()` → `RotateClientSeedAction` (внутри: `GenerateSeedPairAction` + reveal old). Throttle `seed`.
- `verify(Upgrade)` — read-only.

Steam Auth:
- `redirect()` — read-only.
- `callback()` → `AuthenticateViaSteamAction` разбить на: `FindOrCreateUserAction` + `CaptureUtmAction` + `IssueSessionAction`. Throttle `auth`.
- `logout()` — read-only.

LiveFeed:
- `index()` — read-only. Throttle `feed`.

## 5. Тесты

Стратегия: **D + B на финансах**.
- Существующие тесты в `tests/Feature/` оставляем как есть. После каждого PR — `php artisan test` зелёный.
- Перед PR #5 (upgrade), PR #4 (deposit), PR #6 (withdrawal) пишем характеризационные тесты в `tests/Feature/Characterization/` (см. PR #1).
- Новые тесты пишем точечно для security-критичного: throttling-rules, validation regex, observer-immutability ledger.

## 6. Риски и митигация

| Риск | Митигация |
|---|---|
| Регрессия в финансовых операциях | Характеризационные тесты до рефакторинга, фаза-by-фаза с зелёным CI на каждом merge |
| Сломали Inertia-payload (фронт ждёт specific shape) | Eloquent API Resources где надо; вертикальные срезы — каждый PR проверяется визуально |
| Observer'ы создают рекурсию | Жёсткое правило #5: Observer не вызывает Service. Юнит-тесты на каждый observer |
| Throttle сломает легитимный трафик | Лимиты подобраны с запасом, мониторим через Horizon failed jobs первую неделю |
| spatie/laravel-data конфликт с MoonShine | MoonShine использует свои поля, `Data` нужны только в `app/Http/Controllers/`. Конфликта быть не должно |

## 7. Out of scope

- 2FA для admin.
- Login history.
- Soft-delete restoration UI.
- Реальные payment/trade providers (остаются Stub).
- Реструктуризация моделей и миграций.

## 8. Definition of Done (на каждом PR)

- [ ] Все указанные Actions/Data/Observers/Service созданы.
- [ ] Контроллер ≤3 строки тела метода.
- [ ] `vendor/bin/pint --dirty --format agent` → `pass`.
- [ ] `php artisan test` → зелёный.
- [ ] Throttle-middleware на всех указанных endpoints.
- [ ] Smoke-проверка через браузер: happy-path работает.
