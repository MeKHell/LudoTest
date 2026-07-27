#!/bin/sh
set -e

cd /var/www/html

# Writable paths (important when bind-mounting from the host in development).
mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database \
    storage/jmespath \
    storage/app/public

if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache database storage/jmespath 2>/dev/null || true
    chmod -R 775 storage bootstrap/cache
fi

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    db_path="${DB_DATABASE:-database/database.sqlite}"
    # Relative paths are resolved from the app root.
    case "$db_path" in
        /*) ;;
        *) db_path="/var/www/html/$db_path" ;;
    esac
    if [ ! -f "$db_path" ]; then
        touch "$db_path"
        chmod 664 "$db_path" 2>/dev/null || true
        if [ "$(id -u)" = "0" ]; then
            chown www-data:www-data "$db_path" 2>/dev/null || true
        fi
    fi
fi

wait_for_database() {
    if [ "$WAIT_FOR_DB" != "true" ]; then
        return 0
    fi

    echo "Waiting for database..."
    i=0
    while [ "$i" -lt 60 ]; do
        if php artisan db:show > /dev/null 2>&1; then
            echo "Database is ready."
            return 0
        fi
        i=$((i + 1))
        sleep 2
    done

    echo "Database did not become ready in time." >&2
    exit 1
}

if [ "$APP_ENV" != "production" ]; then
    php artisan config:clear --no-interaction > /dev/null 2>&1 || true
    php artisan route:clear --no-interaction > /dev/null 2>&1 || true
    php artisan view:clear --no-interaction > /dev/null 2>&1 || true
fi

wait_for_database

if [ ! -L public/storage ]; then
    php artisan storage:link --force > /dev/null 2>&1 || true
fi

if [ "$RUN_MIGRATIONS" = "true" ]; then
    if ! php artisan migrate --force --no-interaction; then
        if [ "$APP_ENV" = "production" ]; then
            echo "Migration failed." >&2
            exit 1
        fi
        echo "Migration failed; continuing in non-production environment." >&2
    elif [ "$RUN_SEEDERS" = "true" ]; then
        # Seed once on an empty catalog (idempotent seeders, but skip if already populated).
        needs_seed="$(php -r '
            require "vendor/autoload.php";
            $app = require "bootstrap/app.php";
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
            $kernel->bootstrap();
            echo (Illuminate\Support\Facades\Schema::hasTable("languages")
                && App\Models\Language::query()->count() === 0) ? "1" : "0";
        ')"
        if [ "$needs_seed" = "1" ]; then
            php artisan db:seed --force --no-interaction
        fi
    fi
fi

if [ "$APP_ENV" = "production" ] && [ "$CACHE_CONFIG" = "true" ]; then
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
fi

if [ "$ENABLE_CRON" = "true" ] && [ "$(id -u)" = "0" ]; then
    cron
fi

exec "$@"
