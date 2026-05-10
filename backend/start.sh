#!/bin/sh

echo "==> Checking environment..."
if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set."
    exit 1
fi

echo "==> Running database migrations..."
php /var/www/html/artisan migrate --force || echo "WARNING: Migrations failed"

echo "==> Optimizing application..."
php /var/www/html/artisan optimize || echo "WARNING: Optimize failed"

echo "==> Starting PHP-FPM and Nginx via supervisord..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
