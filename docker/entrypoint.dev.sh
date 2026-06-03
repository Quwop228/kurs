#!/bin/sh
set -e

cd /var/www/html

# Зависимости лежат в анонимных томах — при первом старте их нужно поставить
if [ ! -d vendor ] || [ -z "$(ls -A vendor 2>/dev/null)" ]; then
    echo "[dev] Устанавливаю composer-зависимости..."
    composer install --no-interaction --prefer-dist
fi

if [ ! -d node_modules ] || [ -z "$(ls -A node_modules 2>/dev/null)" ]; then
    echo "[dev] Устанавливаю npm-зависимости..."
    npm install
fi

# .env из примера, если отсутствует
if [ ! -f .env ]; then
    echo "[dev] Создаю .env из .env.example"
    cp .env.example .env
fi

# APP_KEY
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "[dev] Генерирую APP_KEY"
    php artisan key:generate --force
fi

# SQLite-файл
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    [ -f "$DB_FILE" ] || touch "$DB_FILE"
fi

# Миграции
echo "[dev] Применяю миграции"
php artisan migrate --force || true

exec "$@"
