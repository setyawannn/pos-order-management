# --- Stage 1: Build Frontend Assets (Vue/Inertia) ---
FROM node:20-alpine as frontend

WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- Stage 2: Build Backend & Serve (MariaDB Optimized) ---
FROM php:8.3-fpm-alpine

# Install system dependencies
# Kita ganti postgresql-dev dengan client mysql (opsional untuk debug)
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    curl \
    zip \
    unzip

# Install PHP Extensions
# HANYA install pdo_mysql (kompatibel penuh dengan MariaDB)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup Working Directory
WORKDIR /var/www/html

# Setup Nginx Configuration (Sama seperti sebelumnya)
RUN echo 'server { \
    listen 80; \
    index index.php index.html; \
    server_name localhost; \
    error_log  /var/log/nginx/error.log; \
    access_log /var/log/nginx/access.log; \
    root /var/www/html/public; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        try_files $uri =404; \
        fastcgi_split_path_info ^(.+\.php)(/.+)$; \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        include fastcgi_params; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        fastcgi_param PATH_INFO $fastcgi_path_info; \
    } \
}' > /etc/nginx/http.d/default.conf

# Copy Project Files
COPY . .

# Copy Frontend Assets
COPY --from=frontend /app/public/build public/build
COPY --from=frontend /app/public/build/manifest.json public/build/manifest.json

# Install PHP Dependencies
RUN composer install --no-dev --optimize-autoloader

# Fix Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Entrypoint (Pastikan file entrypoint.sh sudah ada seperti panduan sebelumnya)
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
