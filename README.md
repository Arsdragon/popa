# Big Boys Projects

Полностью работающая система оценки кода и защиты проектов нейросетью.

## 🚀 Быстрый запуск

### Вариант 1: Автоматический запуск (рекомендуется)
```bash
./start.sh
```

### Вариант 2: Ручной запуск
```bash
cd CodingProjects-master
composer install --ignore-platform-reqs
touch database/database.sqlite
chmod 777 database/database.sqlite storage bootstrap/cache
php artisan migrate
php artisan db:seed --class=Database\\Seeds\\ProjectsSeeder
php artisan serve
```

## 🌐 Доступ

После запуска откройте: http://localhost:8000/insider/projects

## 📋 Функционал

✅ **Полностью работает:**
- Отправка проектов с кодом и защитой
- Оценка нейросетью (код 70% + защита 30%)
- Блокировка за маленькие проекты (<500 символов)
- Разблокировка за 50 монеток
- Детекция ИИ-кода
- Награды: монетки и опыт
- Комментарии к коду
- Тестовые проекты в базе

## 🗂️ Структура кода

- `CodingProjects-master/app/` - Модели, контроллеры, сервисы
- `CodingProjects-master/database/migrations/` - Миграции БД
- `CodingProjects-master/resources/views/projects/` - Шаблоны
- `CodingProjects-master/routes/web.php` - Маршруты

## 🔧 Технологии

- Laravel 12 (PHP)
- SQLite (база данных)
- Bootstrap 5 (интерфейс)
- JavaScript (интерактивность)

## 📞 Поддержка

Система готова к использованию. Все функции реализованы и протестированы.
