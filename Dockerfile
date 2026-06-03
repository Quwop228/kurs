# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1 — собираем frontend-ассеты (Vite + Vue) через Node
# ---------------------------------------------------------------------------
FROM node:22-alpine AS frontend

WORKDIR /app

# Сначала только манифесты — чтобы кешировать слой npm ci
COPY package.json package-lock.json ./
RUN npm ci

# Исходники, нужные Vite для сборки
COPY vite.config.js postcss.config.js tailwind.config.js jsconfig.json ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2 — PHP-зависимости через Composer
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

# Ставим зависимости без dev и без запуска скриптов
# (artisan-скрипты выполнятся уже в финальном образе, где есть весь код)
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader

# ---------------------------------------------------------------------------
# Stage 3 — финальный образ: PHP-FPM + Nginx под управлением supervisord
# ---------------------------------------------------------------------------
FROM php:8.3-fpm-alpine AS app

# Системные пакеты и расширения PHP
RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        sqlite \
        sqlite-dev \
    && docker-php-ext-install \
        pdo \
        pdo_sqlite \
        intl \
        zip \
        bcmath \
        opcache \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

# Копируем код приложения
COPY . .

# Подкладываем vendor из стадии composer и собранные ассеты из стадии node
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Конфиги
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    # Права для веб-сервера на storage и кеш
    && chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache \
        /var/www/html/database \
    && chmod -R 775 \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
