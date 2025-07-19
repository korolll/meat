# Инструкция по запуску Meat API

## 1. Требования
- PHP >= 7.4
- Composer
- SQLite

## 2. Установка зависимостей
```
composer install
```

## 3. Настройка окружения
- Скопировать файл окружения:
  ```
  cp .env.example .env
  ```
- Указать настройки подключения к бд в `.env`:
  ```
  DB_CONNECTION=sqlite
  DB_DATABASE=/абсолютный/путь/к/meat/database/database.sqlite
  ```
- Генерация ключа приложения:
  ```
  php artisan key:generate
  ```
- Генерация секрет для JWT:
  ```
  php artisan jwt:secret
  ```

## 4. Миграции и сидеры
- Выполнить миграции и наполнить бд тестовыми товарами:
  ```
  php artisan migrate --seed
  ```

## 5. Запуск сервера
- Запуск сервера Laravel:
  ```
  php artisan serve
  ```
- API доступно по адресу: http://localhost:8000

## 6. Swagger-документация
- Генерация документации:
  ```
  php artisan l5-swagger:generate
  ```
- http://localhost:8000/api/documentation

## 7. Запуск тестов
  ```
  php artisan test --testsuite=Feature
  ```

## 8. Эндпоинты
- POST `/api/register` — регистрация пользователя
- POST `/api/login` — авторизация (получение JWT)
- GET `/api/products` — список товаров
- POST `/api/orders` — оформление заказа (требует JWT)
- GET `/api/orders` — история заказов пользователя (требует JWT)

---