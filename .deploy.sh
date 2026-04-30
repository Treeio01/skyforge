#!/usr/bin/env bash
set -Eeuo pipefail

# SKYFORGE one-command deploy.
# Usage on server:
#   ./.deploy.sh
#
# Optional overrides:
#   APP_DIR=/var/www/skyforge APP_USER=skyforge BRANCH=main ./.deploy.sh

APP_DIR="${APP_DIR:-/var/www/skyforge}"
APP_USER="${APP_USER:-skyforge}"
BRANCH="${BRANCH:-main}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.3-fpm}"
WEB_SERVICE="${WEB_SERVICE:-apache2}"
HORIZON_SERVICE="${HORIZON_SERVICE:-skyforge-horizon}"
REVERB_SERVICE="${REVERB_SERVICE:-skyforge-reverb}"

step() {
    printf '\n=== %s ===\n' "$1"
}

run_as_app_user() {
    if [[ "$(id -un)" == "$APP_USER" ]]; then
        "$@"
    elif command -v sudo >/dev/null 2>&1; then
        sudo -u "$APP_USER" "$@"
    else
        "$@"
    fi
}

maybe_supervisor() {
    if command -v supervisorctl >/dev/null 2>&1; then
        sudo supervisorctl "$@"
    else
        echo "supervisorctl not found, skipped: supervisorctl $*"
    fi
}

maybe_systemctl_reload() {
    local service="$1"

    if command -v systemctl >/dev/null 2>&1 && systemctl list-unit-files "$service.service" >/dev/null 2>&1; then
        sudo systemctl reload "$service"
    else
        echo "systemctl service not found, skipped reload: $service"
    fi
}

step "Deploy SKYFORGE"
cd "$APP_DIR"

step "Pull latest code"
run_as_app_user git fetch origin "$BRANCH"
run_as_app_user git pull --ff-only origin "$BRANCH"

step "Install PHP dependencies"
run_as_app_user composer install --no-dev --optimize-autoloader --no-interaction

step "Install JS dependencies"
if [[ -f package-lock.json ]]; then
    run_as_app_user npm ci --no-audit --no-fund
else
    run_as_app_user npm install --no-audit --no-fund
fi

step "Build frontend"
run_as_app_user npm run build

step "Run migrations"
run_as_app_user php artisan migrate --force --no-interaction

step "Refresh Laravel caches"
run_as_app_user php artisan optimize:clear --no-interaction
run_as_app_user php artisan optimize --no-interaction
run_as_app_user php artisan view:cache --no-interaction
run_as_app_user php artisan event:cache --no-interaction
run_as_app_user php artisan moonshine:optimize --no-interaction

step "Restart runtime services"
run_as_app_user php artisan queue:restart --no-interaction
run_as_app_user php artisan schedule:interrupt --no-interaction || true
run_as_app_user php artisan reverb:restart --no-interaction || true
maybe_supervisor restart "$HORIZON_SERVICE"
maybe_supervisor restart "$REVERB_SERVICE"
maybe_systemctl_reload "$PHP_FPM_SERVICE"
maybe_systemctl_reload "$WEB_SERVICE"

step "Warm application data"
run_as_app_user php artisan skyforge:sync-rates --no-interaction || true
run_as_app_user php artisan online:boot --no-interaction || true

step "Status"
maybe_supervisor status || true
echo "Deploy complete."
echo "Make sure the Laravel scheduler is running, otherwise fake live feed batches will not be generated every minute."
