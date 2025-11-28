#!/bin/sh
set -e

echo "🔄 Checking database connection to host: $DB_HOST port: $DB_PORT user: $DB_USERNAME"

i=0
while ! php -r "
    try { 
        new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); 
    } catch (PDOException \$e) { 
        fwrite(STDERR, '❌ Error: ' . \$e->getMessage() . PHP_EOL); # Tampilkan error asli
        exit(1); 
    }
"; do
    if [ $i -ge 30 ]; then
        echo "❌ Database connection timed out! Check your credentials and host."
        exit 1
    fi
    echo "⏳ Waiting for database..."
    sleep 2
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