FROM php:8.4-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libzip-dev \
    zip \
    unzip \
    icu-dev \
    postgresql-dev \
    oniguruma-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Copy PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ---------------------
# Build stage (assets)
# ---------------------
FROM node:22-alpine AS node-builder

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ---------------------
# Production stage
# ---------------------
FROM base AS production

# Install nginx and supervisor
RUN apk add --no-cache nginx supervisor

# Copy Nginx and Supervisor configurations
COPY docker/nginx/container.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Setup directories for logs, run files, and nginx pid
RUN mkdir -p /var/log/supervisor /var/run/supervisor /var/run/nginx \
    && chown -R www-data:www-data /var/log/supervisor /var/run/supervisor /var/lib/nginx /var/log/nginx

# Copy application code
COPY --chown=www-data:www-data . /var/www/html

# Copy built frontend assets from node builder
COPY --from=node-builder --chown=www-data:www-data /var/www/html/public/build /var/www/html/public/build

# Install PHP dependencies (no dev)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose Nginx (80) and Reverb (8080)
EXPOSE 80 8080

# Run supervisor as root (it will drop privileges to www-data for worker/scheduler/reverb)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

