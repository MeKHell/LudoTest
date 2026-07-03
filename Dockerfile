# syntax=docker/dockerfile:1

FROM php:8.4-fpm-alpine AS build

RUN apk add --no-cache \
    zip \
    libzip-dev \
    freetype \
    libjpeg-turbo \
    libpng \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    curl-dev \
    nodejs \
    npm

RUN docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_sqlite \
    && docker-php-ext-install curl

RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j"$(nproc)" gd \
    && docker-php-ext-enable gd

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
    && npm ci \
    && npm run build

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    zip \
    libzip-dev \
    freetype \
    libjpeg-turbo \
    libpng \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    oniguruma-dev \
    gettext-dev \
    curl-dev \
    nginx

RUN docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_sqlite bcmath exif gettext opcache curl \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j"$(nproc)" gd \
    && docker-php-ext-enable gd bcmath exif gettext opcache curl \
    && rm -rf /var/cache/apk/*

COPY --from=build /var/www/html /var/www/html
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

WORKDIR /var/www/html

# vendor stays root-owned; writable dirs are fixed at runtime by entrypoint
RUN chown -R root:root /var/www/html/vendor \
    && chmod -R 755 /var/www/html/vendor

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

VOLUME ["/var/www/html/storage/app", "/var/www/html/database"]

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["sh", "-c", "nginx && php-fpm"]
