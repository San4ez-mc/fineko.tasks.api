Архітектура бекенду
1. Загальна інформація
Технологія: PHP 8.x, Yii2 (API-режим)
Призначення: обробка запитів від фронтенду та інтеграцій, віддача JSON-відповідей, авторизація користувачів, робота з базою даних.
Стиль відповіді: завжди JSON (API).
Сесії: не використовуються для API — авторизація через Bearer токени з рефрешем.

2. Структура папок
csharp
Копіювати
Редагувати
backend/
 ├── config/                # Конфігурація застосунку
 │   ├── web.php            # Основні налаштування бекенду (CORS, компоненти, аутентифікація)
 │   ├── console.php        # Консольні налаштування (cron jobs тощо)
 │   └── params.php         # Глобальні параметри
 │
 ├── controllers/           # Контролери API
 │   ├── AuthController.php # Логін, рефреш токена
 │   ├── TaskController.php # CRUD операції з задачами
 │   └── ...                # Інші модулі
 │
 ├── models/                # Моделі (ActiveRecord + логіка)
 │   ├── User.php           # Модель користувача
 │   ├── Task.php           # Модель задачі
 │   └── ...
 │
 ├── migrations/            # Міграції бази даних
 │
 ├── runtime/               # Тимчасові файли та кеш
 │
 ├── vendor/                # Composer-залежності
 │
 ├── web/                   # Точка входу у застосунок
 │   ├── index.php          # Головний вхідний файл
 │   └── .htaccess          # Налаштування для Apache
 │
 └── composer.json          # Залежності бекенду
3. Основні модулі
1. Авторизація (AuthController)
POST /auth/login — отримання access і refresh токенів

POST /auth/refresh — оновлення access токена

Токени зберігаються у таблиці user з полями:

access_token, access_token_expire

refresh_token, refresh_token_expire

2. Задачі (TaskController)
CRUD операції з задачами

Доступ лише для авторизованих користувачів (HttpBearerAuth)

3. Організаційна структура
Модулі для управління оргструктурою компанії

Використовує спільну модель доступу

4. Telegram інтеграція
Відправка нових заявок/змін у групу

Параметри токена та ID групи — в окремому файлі (telegram_config.json)

4. Авторизація та безпека
Access token — живе 1 годину

Refresh token — живе 30 днів

Авторизація через HttpBearerAuth

CORS налаштований для фронтенд-домену:

php
Копіювати
Редагувати
'as cors' => [
    'class' => \yii\filters\Cors::class,
    'cors' => [
        'Origin' => ['https://tasks.fineko.space'],
        'Access-Control-Request-Method' => ['GET','POST','PUT','PATCH','DELETE','OPTIONS'],
        'Access-Control-Allow-Credentials' => true,
        'Access-Control-Max-Age' => 86400,
    ],
],
5. Правила написання коду
Всі контролери — лише для API (жодних HTML-відповідей).

Відповіді завжди у форматі JSON (Response::FORMAT_JSON).

Аутентифікація додається через behaviors() у кожному контролері, або у ApiController як базовому.

Валідація даних через правила у моделях.

Усі зміни структури БД — тільки через міграції.

6. Bash/Консоль
Консольні команди виконуються через OpenServer Console (Windows) або SSH (на продакшн-хостингу).

Основні команди:

bash
Копіювати
Редагувати
# Міграції
php yii migrate

# Створення міграції
php yii migrate/create create_table_name

# Очистка кешу
php yii cache/flush-all
7. Розгортання
Клонувати репозиторій:

bash
Копіювати
Редагувати
git clone git@github.com:FinekoSpace/backend.git
Встановити залежності:

bash
Копіювати
Редагувати
composer install
Налаштувати /backend/config/db.php для доступу до бази.

Виконати міграції:

bash
Копіювати
Редагувати
php yii migrate
Налаштувати .htaccess або nginx під API-режим.

8. Документація
Основні AI-правила: /AI_RULES.md

Архітектура фронтенду: /frontend/ARCHITECTURE.md

Архітектура бекенду (цей файл): /backend/ARCHITECTURE.md