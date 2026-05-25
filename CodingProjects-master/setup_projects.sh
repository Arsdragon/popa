#!/bin/bash
echo "Setting up Big Boys Projects system..."

# Создаем директорию для views если нужно
mkdir -p resources/views/projects

echo "1. Миграции созданы"
echo "2. Модели созданы"
echo "3. Контроллер создан"
echo "4. Представления созданы"
echo "5. Сервис оценки создан"
echo "6. Маршруты добавлены"
echo "7. Ссылка в сайдбар добавлена"
echo ""
echo "Для завершения установки:"
echo "1. Запустите миграции: php artisan migrate"
echo "2. Запустите сидер: php artisan db:seed --class=Database\\Seeds\\ProjectsSeeder"
echo "3. Доступ: /insider/projects"
echo ""
echo "Система Big Boys Projects готова к использованию!"
