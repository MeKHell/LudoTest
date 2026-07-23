# syntax=docker/dockerfile:1

ARG PHP_VERSION=8.4

# -----------------------------------------------------------------------------
# Base PHP image with extensions shared by build and runtime.
# -----------------------------------------------------------------------------
FROM php:${PHP_VERSION}-fpm-bookworm AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        pdo_sqlite \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-ludotest.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# -----------------------------------------------------------------------------
# Composer dependencies
# -----------------------------------------------------------------------------
FROM php-base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --no-autoloader

COPY . .

RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# -----------------------------------------------------------------------------
# Frontend assets (React/Inertia today; Vite-only Livewire build tomorrow).
# Set BUILD_FRONTEND=false after migrating to Livewire if assets are built in CI.
# -----------------------------------------------------------------------------
FROM php-base AS frontend

ARG BUILD_FRONTEND=true

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /var/www/html /var/www/html

ENV APP_KEY=base64:dGhpc2lzYXB1bGRrZXlmb3Jkb2NrZXJidWlsZHByb2Nlc3Nlc2VjdXJlcw==

RUN if [ "$BUILD_FRONTEND" = "true" ]; then \
        php artisan wayfinder:generate \
        && npm ci \
        && npm run build \
        && rm -rf node_modules; \
    fi

# -----------------------------------------------------------------------------
# Production runtime (PHP-FPM only; nginx runs in a separate container)
# -----------------------------------------------------------------------------
FROM php-base AS production

RUN apt-get update \
    && apt-get install -y --no-install-recommends cron \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /var/www/html /var/www/html
COPY --from=frontend /var/www/html/public/build /var/www/html/public/build
COPY docker/cron/laravel /etc/cron.d/laravel

RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/app/public \
        bootstrap/cache \
        database \
        storage/jmespath \
    && chown -R www-data:www-data storage bootstrap/cache database storage/jmespath \
    && chmod -R 775 storage bootstrap/cache \
    && chmod 0644 /etc/cron.d/laravel

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD php-fpm -t || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm", "-F"]

# -----------------------------------------------------------------------------
# Nginx image for production (static assets baked in at build time)
# -----------------------------------------------------------------------------
FROM nginx:1.27-alpine AS web

COPY --from=frontend /var/www/html/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/html/storage/app/public \
    && ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget -q --spider http://127.0.0.1/up || exit 1

# -----------------------------------------------------------------------------
# Development image (includes Node.js for in-container asset builds)
# -----------------------------------------------------------------------------
FROM production AS development

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/php.dev.ini /usr/local/etc/php/conf.d/98-dev.ini
