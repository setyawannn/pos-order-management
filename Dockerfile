# --- Stage 1: Build Frontend Assets (Vue/Inertia) ---
FROM node:20-alpine as frontend

WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- Stage 2: Build Backend & Serve ---
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    curl \
    zip \
    unzip \
    icu-dev

# Install PHP Extensions (MariaDB Compatible)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup Working Directory
WORKDIR /var/www/html

# --- CONFIG NGINX (PERMANEN) ---
# Kita masukkan settingan Buffer & Upload Size langsung di sini
# Agar tidak kena error 502 atau 413 lagi saat redeploy
RUN echo 'server { \
    listen 80; \
    index index.php index.html; \
    server_name localhost; \
    error_log  /var/log/nginx/error.log; \
    access_log /var/log/nginx/access.log; \
    root /var/www/html/public; \
    \
    client_max_body_size 100M; \
    fastcgi_buffers 16 16k; \
    fastcgi_buffer_size 32k; \
    proxy_buffer_size 128k; \
    proxy_buffers 4 256k; \
    proxy_busy_buffers_size 256k; \
    \
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

# Fix Permissions Awal
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/entrypoint.sh

# --- FIX WINDOWS LINE ENDINGS ---
# Ini mencegah error "no such file" atau "exec format error"
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]