#!/bin/sh

echo "==> Checking required environment variables..."
if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set."
    exit 1
fi

echo "==> Clearing ALL caches to ensure fresh start..."
rm -f /var/www/html/bootstrap/cache/config.php
rm -f /var/www/html/bootstrap/cache/routes-v7.php
rm -f /var/www/html/bootstrap/cache/services.php
rm -f /var/www/html/bootstrap/cache/packages.php
rm -rf /var/www/html/storage/framework/cache/data/*
rm -rf /var/www/html/storage/framework/views/*

echo "==> Running database migrations..."
php /var/www/html/artisan migrate --force || echo "WARNING: Migrations failed"

echo "==> Verifying routes are registered..."
php /var/www/html/artisan route:list --path=api/public/system-info 2>&1 | head -5

echo "==> Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
