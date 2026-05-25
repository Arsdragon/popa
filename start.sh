#!/bin/bash
echo "🚀 Запуск Big Boys Projects..."

cd CodingProjects-master

echo "1. Проверка зависимостей..."
if [ ! -f vendor/autoload.php ]; then
    echo "Установка Composer зависимостей..."
    composer install --ignore-platform-reqs
fi

echo "2. Настройка базы данных..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "DB_CONNECTION=sqlite" >> .env
    echo "DB_DATABASE=$(pwd)/database/database.sqlite" >> .env
fi

echo "3. Создание SQLite базы..."
touch database/database.sqlite
chmod 777 database/database.sqlite storage bootstrap/cache

echo "4. Запуск миграций..."
php artisan migrate --force

echo "5. Загрузка тестовых данных..."
php artisan db:seed --class=Database\\Seeds\\ProjectsSeeder --force

echo "6. Запуск сервера..."
echo "📡 Сервер запущен: http://localhost:8000"
echo "🌐 Откройте в браузере: http://localhost:8000/insider/projects"
echo ""
echo "Для остановки: Ctrl+C"

php artisan serve
