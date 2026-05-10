#!/bin/sh

echo "==> Checking environment..."

# Validate required variables
if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set. Please set it in Railway environment variables."
    echo "Generate one with: php -r \"echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;\""
    exit 1
fi

echo "==> Running database migrations..."
php artisan migrate --force || echo "WARNING: Migrations failed - check DATABASE_URL is set correctly"

echo "==> Optimizing application..."
php artisan optimize || echo "WARNING: Optimize failed"

echo "==> Starting server..."
exec /init
