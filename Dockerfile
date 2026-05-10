FROM php:8.2-fpm-alpine

# Install ALL required system dependencies in one layer
RUN apk add --no-cache \
    nginx \
    supervisor \
    # PHP extension build dependencies
    autoconf \
    g++ \
    make \
    pkgconfig \
    musl-dev \
    # Required libraries
    icu-dev \
    imagemagick-dev \
    imagemagick \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    postgresql-dev \
    # Utilities
    zip \
    unzip \
    git \
    curl

# Install PHP extensions (all in one RUN to use shared build tools)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        opcache \
        xml \
        zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    # Clean up build dependencies to reduce image size
    && apk del autoconf g++ make pkgconfig musl-dev

# Configure PHP
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini

# Configure PHP-FPM to listen on TCP port 9000
RUN sed -i 's|listen = /var/run/php/php-fpm.sock|listen = 127.0.0.1:9000|g' \
    /usr/local/etc/php-fpm.d/www.conf \
    || echo "listen = 127.0.0.1:9000" >> /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create required directories
RUN mkdir -p /run/nginx /var/log/nginx /var/lib/nginx/tmp /run/supervisor

# Copy Nginx and Supervisor configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Copy backend application
COPY --chown=www-data:www-data backend/ .

# Create required Laravel directories
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chmod -R 755 storage \
    && chmod -R 775 bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Install Composer dependencies
RUN composer install \
    --no-interaction \
    --no-dev \
    --optimize-autoloader \
    --prefer-dist

# Set up HTMLPurifier cache
RUN mkdir -p vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \
    && chmod -R 775 vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer \
    && chown -R www-data:www-data vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer

# Copy startup script
COPY backend/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
