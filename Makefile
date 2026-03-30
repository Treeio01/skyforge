.PHONY: help install dev test lint analyse quality docker-up docker-down docker-build fresh ide-helper

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ── Local Development ──────────────────────────

install: ## Install all dependencies
	composer install
	npm install --legacy-peer-deps
	cp -n .env.example .env || true
	php artisan key:generate --ansi
	php artisan storage:link
	php artisan migrate
	@echo "\n✅ Project installed. Run 'make dev' to start."

dev: ## Start local dev server (app + horizon + reverb + vite + logs)
	composer dev

fresh: ## Fresh migration + seed
	php artisan migrate:fresh --seed

test: ## Run tests
	php artisan test

test-parallel: ## Run tests in parallel
	php artisan test --parallel

lint: ## Fix code style
	vendor/bin/pint

analyse: ## Run static analysis
	vendor/bin/phpstan analyse --memory-limit=512M

quality: ## Run lint + analyse + test
	composer quality

ide-helper: ## Regenerate IDE helper files
	composer ide-helper

# ── Docker ─────────────────────────────────────

docker-build: ## Build Docker containers
	docker compose build

docker-up: ## Start Docker containers
	docker compose up -d

docker-down: ## Stop Docker containers
	docker compose down

docker-fresh: ## Fresh install in Docker
	docker compose exec app php artisan migrate:fresh --seed

docker-shell: ## Shell into app container
	docker compose exec app sh

docker-logs: ## Tail all container logs
	docker compose logs -f
