#!/bin/sh
set -e

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Optimizing application..."
php artisan optimize

echo "==> Starting server..."
exec /init
