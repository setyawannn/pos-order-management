# --- Stage 1: Build Frontend Assets (Vue/Inertia) ---
FROM node:20-alpine as frontend

WORKDIR /app

COPY package*.json ./
# Install dependencies
RUN npm ci

COPY . .
# Build assets (Vite build)
RUN npm run build

# --- Stage 2: Build Backend & Serve ---
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    curl \
    zip \
    unzip

# Install PHP Extensions (sesuaikan database driver, contoh: pgsql untuk Postgres, mysql pdo_mysql untuk MySQL)
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup Working Directory
WORKDIR /var/www/html

# Setup Nginx Configuration
# Kita buat konfigurasi nginx sederhana langsung di sini
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

# Copy Frontend Assets dari Stage 1
COPY --from=frontend /app/public/build public/build
COPY --from=frontend /app/public/build/manifest.json public/build/manifest.json

# Install PHP Dependencies (Optimized for Prod)
RUN composer install --no-dev --optimize-autoloader

# Fix Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose Port
EXPOSE 80

# Run Entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
