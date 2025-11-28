#!/bin/bash

# Exit on fail
set -e

# Role setup (jika ada queue worker nanti bisa disesuaikan)
role=${CONTAINER_ROLE:-app}

if [ "$role" = "app" ]; then
    echo "🚀 Caching configuration..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    echo "run migrations..."
    php artisan migrate --force

    echo "📦 Starting PHP-FPM..."
    # Menjalankan PHP-FPM
    php-fpm -D

    # Menjalankan Nginx (di foreground agar container tidak mati)
    echo "🌐 Starting Nginx..."
    nginx -g "daemon off;"
fi
