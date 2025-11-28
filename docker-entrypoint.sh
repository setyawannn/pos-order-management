#!/bin/bash

# Exit on fail
set -e

# Role setup (jika ada queue worker nanti bisa disesuaikan)
role=${CONTAINER_ROLE:-app}

if [ "$role" = "app" ]; then
    echo "🚀 Caching configuration..."
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    echo "run migrations..."
    # Wait for database connection
    echo "Waiting for database connection..."
    while ! php artisan db:monitor > /dev/null 2>&1; do
        echo "Database is not ready yet. Waiting..."
        sleep 2
    done
    echo "Database is ready!"

    php artisan migrate --force

    echo "📦 Starting PHP-FPM..."
    # Menjalankan PHP-FPM
    php-fpm -D

    # Menjalankan Nginx (di foreground agar container tidak mati)
    echo "🌐 Starting Nginx..."
    nginx -g "daemon off;"
fi
