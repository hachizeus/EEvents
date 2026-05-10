#!/bin/sh

echo "==> Checking required environment variables..."
if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set."
    echo "Generate with: php -r \"echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;\""
    exit 1
fi

echo "==> Clearing all stale caches..."
php /var/www/html/artisan config:clear 2>/dev/null || true
php /var/www/html/artisan cache:clear 2>/dev/null || true
php /var/www/html/artisan route:clear 2>/dev/null || true
php /var/www/html/artisan view:clear 2>/dev/null || true

echo "==> Running database migrations..."
php /var/www/html/artisan migrate --force
if [ $? -ne 0 ]; then
    echo "WARNING: Migrations failed - check DATABASE_URL environment variable"
fi

echo "==> Caching config (skipping route cache - routes use closures)..."
php /var/www/html/artisan config:cache 2>/dev/null || echo "WARNING: Config cache failed"
php /var/www/html/artisan view:cache 2>/dev/null || echo "WARNING: View cache failed"

echo "==> Starting services via supervisord..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
