# Деплой SKYFORGE на Ubuntu 24.04 + Apache2

## 1. Подготовка сервера

```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Базовые утилиты
sudo apt install -y curl wget git unzip software-properties-common acl
```

## 2. PHP 8.3

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-pgsql php8.3-redis php8.3-curl php8.3-gd \
    php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl \
    php8.3-readline php8.3-opcache php8.3-imagick
```

## 3. Apache2 + модули

```bash
sudo apt install -y apache2 libapache2-mod-fcgid
sudo a2enmod rewrite proxy proxy_fcgi proxy_http proxy_wstunnel headers ssl setenvif
sudo a2enconf php8.3-fpm
sudo systemctl restart apache2
```

## 4. MySQL 8

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Создание БД и юзера
sudo mysql -e "CREATE DATABASE skyforge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'skyforge'@'localhost' IDENTIFIED BY 'fryuinsjgkw3048';"
sudo mysql -e "GRANT ALL PRIVILEGES ON skyforge.* TO 'skyforge'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

## 5. Redis

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

## 6. Node.js 20

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

## 7. Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 8. Деплой проекта

```bash
# Создание юзера
sudo useradd -m -s /bin/bash skyforge
sudo usermod -aG www-data skyforge

# Клонирование
sudo mkdir -p /var/www/skyforge
sudo chown skyforge:www-data /var/www/skyforge
sudo -u skyforge git clone YOUR_REPO_URL /var/www/skyforge
cd /var/www/skyforge

# Права
sudo chown -R skyforge:www-data /var/www/skyforge
sudo chmod -R 775 storage bootstrap/cache
sudo setfacl -R -m u:www-data:rwx storage bootstrap/cache
sudo setfacl -dR -m u:www-data:rwx storage bootstrap/cache
```

## 9. Конфигурация приложения

```bash
cd /var/www/skyforge
sudo -u skyforge cp .env.example .env

# Редактируем .env
sudo -u skyforge nano .env
```

**.env** — ключевые значения:
```env
APP_NAME=SKYFORGE
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_LOCALE=ru

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skyforge
DB_USERNAME=skyforge
DB_PASSWORD=STRONG_PASSWORD_HERE

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REVERB_APP_ID=skyforge
REVERB_APP_KEY=skyforge-reverb-key
REVERB_APP_SECRET=skyforge-reverb-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${APP_URL}"
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

# Steam
STEAM_CLIENT_ID=
STEAM_CLIENT_SECRET=
STEAM_API_KEY=

# Payment
SKYFORGE_PAYMENT_PROVIDER=stub
```

```bash
# Установка зависимостей
sudo -u skyforge composer install --no-dev --optimize-autoloader
sudo -u skyforge npm ci

# Ключ, миграции, сидеры
sudo -u skyforge php artisan key:generate
sudo -u skyforge php artisan migrate --force
sudo -u skyforge php artisan db:seed --force

# Сборка фронтенда
sudo -u skyforge npm run build

# Кэширование
sudo -u skyforge php artisan config:cache
sudo -u skyforge php artisan route:cache
sudo -u skyforge php artisan view:cache
sudo -u skyforge php artisan event:cache
sudo -u skyforge php artisan icons:cache

# Storage link
sudo -u skyforge php artisan storage:link

# MoonShine
sudo -u skyforge php artisan moonshine:optimize

# Первичный синк курсов
sudo -u skyforge php artisan skyforge:sync-rates
```

## 10. Apache Virtual Host

```bash
sudo nano /etc/apache2/sites-available/skyforge.conf
```

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    Redirect permanent / https://your-domain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName your-domain.com
    ServerAlias www.your-domain.com

    DocumentRoot /var/www/skyforge/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/your-domain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/your-domain.com/privkey.pem

    <Directory /var/www/skyforge/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    # PHP-FPM
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.3-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # WebSocket proxy (Reverb)
    ProxyPreserveHost On
    RewriteEngine On
    RewriteCond %{HTTP:Upgrade} =websocket [NC]
    RewriteRule /app/(.*) ws://127.0.0.1:8080/app/$1 [P,L]

    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    ErrorLog ${APACHE_LOG_DIR}/skyforge-error.log
    CustomLog ${APACHE_LOG_DIR}/skyforge-access.log combined
</VirtualHost>
```

```bash
sudo a2ensite skyforge.conf
sudo a2dissite 000-default.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

## 11. SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com -d www.your-domain.com
sudo systemctl reload apache2
```

## 12. Supervisor (Horizon + Reverb + Schedule)

```bash
sudo apt install -y supervisor
```

```bash
sudo nano /etc/supervisor/conf.d/skyforge-horizon.conf
```

```ini
[program:skyforge-horizon]
process_name=%(program_name)s
command=php /var/www/skyforge/artisan horizon
autostart=true
autorestart=true
user=skyforge
redirect_stderr=true
stdout_logfile=/var/www/skyforge/storage/logs/horizon.log
stopwaitsecs=3600
```

```bash
sudo nano /etc/supervisor/conf.d/skyforge-reverb.conf
```

```ini
[program:skyforge-reverb]
process_name=%(program_name)s
command=php /var/www/skyforge/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=skyforge
redirect_stderr=true
stdout_logfile=/var/www/skyforge/storage/logs/reverb.log
```

```bash
sudo nano /etc/supervisor/conf.d/skyforge-scheduler.conf
```

```ini
[program:skyforge-scheduler]
process_name=%(program_name)s
command=/bin/bash -c "while true; do php /var/www/skyforge/artisan schedule:run --no-interaction; sleep 60; done"
autostart=true
autorestart=true
user=skyforge
redirect_stderr=true
stdout_logfile=/var/www/skyforge/storage/logs/scheduler.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

## 13. PHP-FPM настройка

```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

Ключевые параметры:
```ini
user = skyforge
group = www-data
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 10

php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 50M
php_admin_value[post_max_size] = 50M
php_admin_value[max_execution_time] = 60
```

```bash
sudo systemctl restart php8.3-fpm
```

## 14. Cron (backup вариант для schedule)

```bash
sudo crontab -u skyforge -e
```

```cron
* * * * * cd /var/www/skyforge && php artisan schedule:run >> /dev/null 2>&1
```

## 15. Firewall

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 8080/tcp  # Reverb WebSocket
sudo ufw enable
```

## 16. Обновление (деплой новой версии)

```bash
cd /var/www/skyforge

# Остановка
sudo supervisorctl stop skyforge-horizon

# Обновление кода
sudo -u skyforge git pull origin main

# Зависимости
sudo -u skyforge composer install --no-dev --optimize-autoloader
sudo -u skyforge npm ci && sudo -u skyforge npm run build

# Миграции
sudo -u skyforge php artisan migrate --force

# Кэш
sudo -u skyforge php artisan config:cache
sudo -u skyforge php artisan route:cache
sudo -u skyforge php artisan view:cache
sudo -u skyforge php artisan event:cache
sudo -u skyforge php artisan moonshine:optimize

# Перезапуск
sudo supervisorctl start skyforge-horizon
sudo supervisorctl restart skyforge-reverb
sudo systemctl reload php8.3-fpm
sudo systemctl reload apache2

echo "Deploy complete!"
```

## Проверка

```bash
# Статус сервисов
sudo systemctl status apache2
sudo systemctl status php8.3-fpm
sudo systemctl status mysql
sudo systemctl status redis-server
sudo supervisorctl status

# Логи
tail -f /var/www/skyforge/storage/logs/laravel.log
tail -f /var/www/skyforge/storage/logs/horizon.log
tail -f /var/apache2/error.log
```

## Требования к серверу

- **Минимум**: 2 vCPU, 4 GB RAM, 40 GB SSD
- **Рекомендуется**: 4 vCPU, 8 GB RAM, 80 GB SSD
- Ubuntu 24.04 LTS
