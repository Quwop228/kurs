#!/bin/sh
set -e

cd /var/www/html

# Если .env отсутствует — создаём из примера
if [ ! -f .env ]; then
    echo "[entrypoint] .env не найден — копирую из .env.example"
    cp .env.example .env
fi

# Генерируем APP_KEY, если он пустой
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "[entrypoint] Генерирую APP_KEY"
    php artisan key:generate --force
fi

# SQLite: гарантируем наличие файла БД
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    if [ ! -f "$DB_FILE" ]; then
        echo "[entrypoint] Создаю файл SQLite: $DB_FILE"
        touch "$DB_FILE"
    fi
fi

# Права на изменяемые каталоги
chown -R www-data:www-data storage bootstrap/cache database || true

# Линкуем storage, если ещё не слинкован
php artisan storage:link 2>/dev/null || true

# Применяем миграции
echo "[entrypoint] Запускаю миграции"
php artisan migrate --force || true

# Первичное наполнение БД (категории, теги, статьи) из DatabaseSeeder.
# Выполняется один раз — маркер лежит в томе database, поэтому при
# перезапусках контейнера сид повторно не запускается.
SEED_MARKER="/var/www/html/database/.seeded"
if [ ! -f "$SEED_MARKER" ]; then
    echo "[entrypoint] Первый запуск — наполняю БД (db:seed)"
    if php artisan db:seed --force; then
        touch "$SEED_MARKER"
        echo "[entrypoint] Сид выполнен"
    else
        echo "[entrypoint] ВНИМАНИЕ: db:seed завершился с ошибкой"
    fi
fi

# Кешируем конфиг/роуты/вью для прода
echo "[entrypoint] Кеширую конфиг приложения"
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
