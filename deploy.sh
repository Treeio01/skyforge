#!/bin/bash
set -e

# SKYFORGE Deploy Script
# Usage: ./deploy.sh

APP_DIR="/var/www/skyforge"
APP_USER="skyforge"

echo "=== SKYFORGE Deploy ==="

cd "$APP_DIR"

echo "[1/7] Stopping Horizon..."
sudo supervisorctl stop skyforge-horizon

echo "[2/7] Pulling latest code..."
sudo -u $APP_USER git pull origin main

echo "[3/7] Installing dependencies..."
sudo -u $APP_USER composer install --no-dev --optimize-autoloader --no-interaction
sudo -u $APP_USER npm ci
sudo -u $APP_USER npm run build

echo "[4/7] Running migrations..."
sudo -u $APP_USER php artisan migrate --force --no-interaction

echo "[5/7] Caching..."
sudo -u $APP_USER php artisan config:cache
sudo -u $APP_USER php artisan route:cache
sudo -u $APP_USER php artisan view:cache
sudo -u $APP_USER php artisan event:cache
sudo -u $APP_USER php artisan moonshine:optimize

echo "[6/7] Restarting services..."
sudo supervisorctl start skyforge-horizon
sudo supervisorctl restart skyforge-reverb
sudo systemctl reload php8.3-fpm
sudo systemctl reload apache2

echo "[7/7] Syncing rates..."
sudo -u $APP_USER php artisan skyforge:sync-rates

echo ""
echo "=== Deploy complete! ==="
sudo supervisorctl status
