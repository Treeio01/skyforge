#!/usr/bin/env bash
set -Eeuo pipefail

# SKYFORGE one-command deploy.
#
# Just run from anywhere as root or as the app user:
#   bash .deploy.sh
#
# What it does, automatically:
#   - resets stale tracked files (e.g. lockfiles dirtied by previous npm runs)
#   - normalises ownership before any npm/composer/vite step
#   - pulls latest, installs deps, builds, migrates, caches, restarts services
#
# Optional overrides:
#   APP_DIR=/var/www/skyforge APP_USER=skyforge BRANCH=main bash .deploy.sh

APP_DIR="${APP_DIR:-/var/www/skyforge}"
APP_USER="${APP_USER:-skyforge}"
APP_GROUP="${APP_GROUP:-$APP_USER}"
BRANCH="${BRANCH:-main}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.3-fpm}"
WEB_SERVICE="${WEB_SERVICE:-apache2}"
HORIZON_SERVICE="${HORIZON_SERVICE:-skyforge-horizon}"
REVERB_SERVICE="${REVERB_SERVICE:-skyforge-reverb}"

step() { printf '\n=== %s ===\n' "$1"; }

am_root() { [[ "$(id -u)" -eq 0 ]]; }
am_app_user() { [[ "$(id -un)" == "$APP_USER" ]]; }

# Run a command as the app user no matter who's invoking the script.
# - root: prefer `runuser` (no PAM/password), fallback to `sudo -n`.
# - app user: run directly.
# - anyone else: try `sudo -n`.
run_as_app_user() {
    if am_app_user; then
        "$@"
    elif am_root && command -v runuser >/dev/null 2>&1; then
        runuser -u "$APP_USER" -- "$@"
    elif command -v sudo >/dev/null 2>&1; then
        sudo -n -u "$APP_USER" "$@"
    else
        echo "Cannot run as $APP_USER (no runuser/sudo available). Aborting." >&2
        exit 1
    fi
}

# Privileged commands (systemctl/supervisorctl). When invoked as root they
# run directly; otherwise we fall back to passwordless sudo.
run_privileged() {
    if am_root; then
        "$@"
    elif command -v sudo >/dev/null 2>&1; then
        sudo -n "$@"
    else
        echo "Privileged command needed but no sudo available: $*" >&2
        return 1
    fi
}

maybe_supervisor() {
    if command -v supervisorctl >/dev/null 2>&1; then
        run_privileged supervisorctl "$@" || echo "supervisorctl $* failed (skipped)"
    else
        echo "supervisorctl not found, skipped: supervisorctl $*"
    fi
}

maybe_systemctl_reload() {
    local service="$1"

    if command -v systemctl >/dev/null 2>&1 && systemctl list-unit-files "$service.service" >/dev/null 2>&1; then
        run_privileged systemctl reload "$service" || echo "systemctl reload $service failed (skipped)"
    else
        echo "systemctl service not found, skipped reload: $service"
    fi
}

# Reset any tracked files that previous runs (or root invocations) might have
# dirtied so that `git pull --ff-only` doesn't refuse the merge. We only reset
# files that are actually tracked — untracked stuff (e.g. .env) stays put.
fixup_dirty_tracked() {
    if ! git -C "$APP_DIR" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
        return
    fi

    # List files git knows about that have local modifications.
    local dirty
    dirty="$(git -C "$APP_DIR" diff --name-only)"
    if [[ -n "$dirty" ]]; then
        echo "Discarding local edits to tracked files (so the pull can fast-forward):"
        printf '  %s\n' $dirty
        git -C "$APP_DIR" checkout -- $dirty
    fi
}

# Make sure the whole tree is owned by the app user/group. This is the
# single most common source of EACCES from npm/vite when the script gets
# run as root once and as the app user another time.
normalise_ownership() {
    if am_root; then
        chown -R "$APP_USER":"$APP_GROUP" "$APP_DIR"
    fi
}

cd "$APP_DIR"

step "Deploy SKYFORGE"

step "Normalise ownership and reset dirty tracked files"
normalise_ownership
fixup_dirty_tracked

step "Pull latest code"
run_as_app_user git fetch origin "$BRANCH"
run_as_app_user git pull --ff-only origin "$BRANCH"

step "Install PHP dependencies"
run_as_app_user composer install --no-dev --optimize-autoloader --no-interaction

step "Install JS dependencies"
# Re-normalise ownership just before npm — `git pull` may have brought new
# files in as root if the script was launched by root after a manual `git pull`.
normalise_ownership
if [[ -f package-lock.json ]]; then
    run_as_app_user npm ci --no-audit --no-fund
else
    run_as_app_user npm install --no-audit --no-fund
fi

step "Build frontend"
normalise_ownership
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
run_as_app_user php artisan feed:fake --fill=20 --no-interaction || true

step "Status"
maybe_supervisor status || true
echo "Deploy complete."
echo "Make sure the Laravel scheduler is running, otherwise fake live feed batches will not be generated every minute."
