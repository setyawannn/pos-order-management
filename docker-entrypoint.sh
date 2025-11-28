#!/bin/sh
set -e

echo "🔄 Checking database connection to host: $DB_HOST port: $DB_PORT..."

# Loop tunggu database
i=0
while ! php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); } catch (PDOException \$e) { fwrite(STDERR, '❌ ' . \$e->getMessage() . PHP_EOL); exit(1); }"; do
    if [ $i -ge 30 ]; then
        echo "❌ Database connection timed out!"
        exit 1
    fi
    echo "⏳ Waiting for database..."
    sleep 2
    i=$((i+1))
done

echo "✅ Database connection established!"

# --- OTOMATISASI PERBAIKAN (Agar tidak manual lagi) ---

echo "🔗 Checking Storage Link..."
# Hapus symlink lama (takutnya broken) dan buat baru
if [ -L public/storage ]; then
    rm public/storage
fi
php artisan storage:link
echo "✅ Storage linked."

echo "🔧 Fixing Permissions..."
# Pastikan folder storage bisa ditulis oleh Nginx/PHP
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/public
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/public/storage
chmod -R 755 /var/www/html/public
echo "✅ Permissions fixed."

# -----------------------------------------------------

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