# GROWSKINS

CS2 skin upgrade platform. Laravel 13 + Inertia.js + React (TypeScript).

## Quick Start (Local)

### Prerequisites

- PHP 8.3+ with extensions: pdo_mysql, redis, bcmath, pcntl, sockets, gd
- Composer 2.x
- Node.js 22+ / npm 10+
- MySQL 8.4+
- Redis 7.x

### Installation

```bash
git clone <repo-url> skyforge
cd skyforge

# Install dependencies
composer install
npm install --legacy-peer-deps

# Environment
cp .env.example .env
php artisan key:generate

# Edit .env: set DB_DATABASE, DB_USERNAME, DB_PASSWORD
# Create MySQL database:
# mysql -u root -e "CREATE DATABASE skyforge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Database
php artisan migrate
php artisan db:seed

# Storage link (serves skin images)
php artisan storage:link

# IDE helper (optional, for PhpStorm/VSCode)
composer ide-helper
```

### Run Development Server

```bash
# All-in-one: app + horizon + reverb + vite + logs
make dev
# or
composer dev
```

| Service | URL |
|---------|-----|
| App | http://localhost:8000 |
| Horizon | http://localhost:8000/horizon |
| Admin (MoonShine) | http://localhost:8000/admin |
| Reverb WS | ws://localhost:8080 |

## Quick Start (Docker)

```bash
cp .env.docker .env
docker compose up -d --build

# Inside container
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link

# Frontend (run on host)
npm install --legacy-peer-deps
npm run dev
```

## Available Commands

### Make commands

```bash
make dev            # Start all dev services
make test           # Run tests
make test-parallel  # Run tests in parallel
make lint           # Fix code style (Pint)
make analyse        # Static analysis (Larastan)
make quality        # lint + analyse + test
make fresh          # Fresh migration + seed
make ide-helper     # Regenerate IDE helpers
make docker-up      # Start Docker
make docker-down    # Stop Docker
```

### Composer scripts

```bash
composer dev        # All-in-one dev server
composer test       # Run tests
composer lint       # Fix code style
composer lint:check # Check code style (CI)
composer analyse    # PHPStan level 6
composer quality    # lint:check + analyse + test
composer ide-helper # Generate IDE helpers
```

## AI Development Tools

### Laravel Boost (MCP)

Provides 15 tools for AI-assisted development: database schema, routes, artisan, tinker, docs search, etc.

```bash
composer require laravel/boost --dev
php artisan boost:install
```

### SuperPowers (Claude Code plugin)

Structured development workflow: brainstorm, plan, TDD, code review.

```bash
claude plugins install superpowers@claude-plugins-official
```

## Tech Stack

| Layer | Tech |
|-------|------|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | React 18, TypeScript, Inertia.js, Tailwind CSS |
| Admin | MoonShine |
| Database | MySQL 8.4 |
| Cache/Queue | Redis 7.2, Laravel Horizon |
| WebSocket | Laravel Reverb |
| Auth | Steam OpenID (Socialite) |
| Testing | Pest |
| Code Quality | Pint, Larastan (level 6) |
| CI/CD | GitHub Actions |

## Project Structure

```
app/
├── Actions/          # Single-responsibility actions (SOLID SRP)
│   ├── Auth/         # Steam authentication
│   ├── Balance/      # Credit/debit with pessimistic locking
│   ├── Upgrade/      # Game logic
│   ├── Deposit/      # Payment processing
│   ├── Withdrawal/   # Skin withdrawal
│   ├── Skin/         # Price sync
│   └── ProvablyFair/ # Seed generation, roll, verify
├── Services/         # Orchestration (stateless)
├── Contracts/        # Interfaces
├── DTOs/             # Data transfer objects
├── Enums/            # Backed enums
├── Events/           # Broadcast events
├── Listeners/        # Event handlers
├── Observers/        # Model observers
├── Jobs/             # Queue jobs
├── Http/
│   ├── Controllers/
│   ├── Requests/     # Form validation
│   ├── Resources/    # API resources
│   └── Middleware/
├── Models/
├── MoonShine/        # Admin panel resources
└── Providers/
```
