#!/bin/sh

echo "==> Checking required environment variables..."
if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set. Generate with: php -r \"echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;\""
    exit 1
fi

if [ -z "$DATABASE_URL" ] && [ -z "$DB_HOST" ]; then
    echo "WARNING: No database connection configured (DATABASE_URL or DB_HOST not set)"
fi

echo "==> Running database migrations..."
php /var/www/html/artisan migrate --force
if [ $? -ne 0 ]; then
    echo "WARNING: Migrations failed - the app will start but may not work correctly"
fi

echo "==> Caching configuration..."
php /var/www/html/artisan config:cache || echo "WARNING: Config cache failed"
php /var/www/html/artisan route:cache || echo "WARNING: Route cache failed"
php /var/www/html/artisan view:cache || echo "WARNING: View cache failed"

echo "==> Starting PHP-FPM and Nginx via supervisord..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
