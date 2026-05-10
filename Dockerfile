FROM serversideup/php:8.4-fpm-nginx-alpine

ENV PHP_OPCACHE_ENABLE=1

USER root

# Configure PHP-FPM pool
RUN echo "" >> /usr/local/etc/php-fpm.d/docker-php-serversideup-pool.conf && \
    echo "user = www-data" >> /usr/local/etc/php-fpm.d/docker-php-serversideup-pool.conf && \
    echo "group = www-data" >> /usr/local/etc/php-fpm.d/docker-php-serversideup-pool.conf

# Install PHP extensions
RUN install-php-extensions intl imagick

# Copy backend source from the backend/ subdirectory
COPY --chown=www-data:www-data backend/ .

# Create required Laravel directories and set permissions
RUN mkdir -p storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chmod -R 755 storage \
    && chmod -R 775 bootstrap/cache \
    && mkdir -p /var/lib/nginx/tmp \
    && chown -R www-data:www-data /var/lib/nginx \
    && chmod -R 755 /var/lib/nginx

# Install Composer dependencies
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-dev \
    --optimize-autoloader \
    --prefer-dist

# Set up HTMLPurifier cache directory
RUN mkdir -p /var/www/html/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \
    && chmod -R 775 /var/www/html/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \
    && chown -R www-data:www-data /var/www/html/vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer

# Copy startup script and make executable
COPY backend/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
