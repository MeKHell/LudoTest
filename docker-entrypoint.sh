#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database

if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache
chmod 775 database
chmod 664 database/database.sqlite

php artisan migrate --force

exec "$@"
