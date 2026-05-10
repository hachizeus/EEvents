#!/bin/sh

echo "==> Checking required environment variables..."
if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set."
    exit 1
fi

echo "==> Clearing any stale caches from build time..."
php /var/www/html/artisan config:clear
php /var/www/html/artisan cache:clear
php /var/www/html/artisan route:clear
php /var/www/html/artisan view:clear

echo "==> Running database migrations..."
php /var/www/html/artisan migrate --force || echo "WARNING: Migrations failed"

echo "==> Rebuilding caches with live environment..."
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

echo "==> Starting PHP-FPM and Nginx via supervisord..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
