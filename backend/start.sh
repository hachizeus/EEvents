#!/bin/sh

echo "==> Checking required environment variables..."
if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set."
    exit 1
fi

echo "==> Clearing config cache..."
php /var/www/html/artisan config:clear 2>/dev/null || true

echo "==> Clearing file-based caches..."
rm -rf /var/www/html/bootstrap/cache/config.php 2>/dev/null || true
rm -rf /var/www/html/bootstrap/cache/routes*.php 2>/dev/null || true
rm -rf /var/www/html/storage/framework/cache/data/* 2>/dev/null || true
rm -rf /var/www/html/storage/framework/views/* 2>/dev/null || true

echo "==> Running database migrations..."
php /var/www/html/artisan migrate --force || echo "WARNING: Migrations failed"

echo "==> Caching config..."
php /var/www/html/artisan config:cache 2>/dev/null || echo "WARNING: Config cache failed"

echo "==> Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
