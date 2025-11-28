#!/bin/sh
set -e

# Setup untuk memastikan database siap
echo "🔄 Checking database connection..."

# Loop sederhana menunggu database siap (max 30 detik)
i=0
while ! php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); } catch (PDOException \$e) { exit(1); }" > /dev/null 2>&1; do
    if [ $i -ge 30 ]; then
        echo "❌ Database connection timed out!"
        exit 1
    fi
    echo "⏳ Waiting for database to be ready..."
    sleep 1
    i=$((i+1))
done

echo "✅ Database connection established!"

# Lanjut ke perintah Laravel
echo "🚀 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🚀 Running migrations..."
php artisan migrate --force

echo "📦 Starting PHP-FPM..."
php-fpm -D

echo "🌐 Starting Nginx..."
nginx -g "daemon off;"