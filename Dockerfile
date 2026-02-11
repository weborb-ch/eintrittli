# Stage 1: PHP dependencies & extensions
FROM php:8.5-cli AS builder

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    libpq-dev \
    libonig-dev \
    libicu-dev \
    libzip-dev \
    libpng-dev \
  && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    intl \
    zip \
    gd \
  && apt-get autoremove -y && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
  && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts \
  && rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
  && php artisan package:discover

# Stage 2: Frontend assets
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --audit false

COPY --from=builder /app/vendor ./vendor
COPY vite.config.js ./
COPY resources ./resources
COPY app ./app

RUN npm run build

# Stage 3: Production image
FROM php:8.5-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq5 \
    libicu76 \
    libzip5 \
    libpng16-16t64 \
    libonig5 \
  && apt-get autoremove -y && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /app

COPY . .
COPY --from=builder /app/vendor ./vendor
COPY --from=builder /app/bootstrap/cache ./bootstrap/cache
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/app/public \
    bootstrap/cache \
  && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
