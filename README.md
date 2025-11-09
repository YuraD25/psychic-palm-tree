# API для заявок на кредит

RESTful API для обработки заявок на кредит, построенный на фреймворке Yii2 с использованием принципов чистой архитектуры.

## Быстрый старт

### 1. Клонирование репозитория

```bash
git clone <repository-url>
cd psychic-palm-tree
```

### 2. Запуск с помощью Docker Compose

```bash
# Сборка и запуск контейнеров
make build

# Или напрямую через docker-compose
docker-compose up --build -d
```

### 3. Проверка работоспособности

Приложение будет доступно по адресу `http://localhost`

```bash
# Проверка health endpoint
curl http://localhost/health

# Ожидаемый ответ
{
  "status": "ok",
  "timestamp": "2024-11-05T10:30:00+00:00",
  "services": {
    "database": "ok",
    "migrations": "ok"
  }
}
```

## API документация

### Swagger UI

Интерактивная документация API доступна через Swagger UI:

```
http://localhost/swagger
```


### Доступные команды

Проект использует Makefile для упрощения работы:

```bash
# Сборка и запуск контейнеров
make build

# Запуск существующих контейнеров
make up

# Остановка контейнеров
make down

# Просмотр логов
make logs

# Доступ к shell PHP контейнера
make shell

# Запуск миграций
make migrate

# Запуск тестов
make test

# Полная очистка (контейнеры и volumes)
make clean
```


### Автоматические миграции

Миграции базы данных автоматически выполняются при запуске контейнера через entrypoint скрипт:

1. Ожидание готовности PostgreSQL
2. Проверка подключения к БД
3. Выполнение `php yii migrate --interactive=0`
4. Проверка настройки приложения

### Ручной запуск миграций

```bash
# Через Makefile
make migrate

# Через docker-compose
docker-compose exec php-fpm php yii migrate

```


## Тестирование

### Запуск тестов

```bash
# Все тесты
make test

# Или напрямую через docker-compose
docker-compose exec php-fpm vendor/bin/phpunit

```

### Схема базы данных

```sql
CREATE TABLE loan_requests (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    amount INTEGER NOT NULL,
    term INTEGER NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_loan_requests_user_id ON loan_requests(user_id);
CREATE INDEX idx_loan_requests_status ON loan_requests(status);
```

## Учет времени разработки

**Приблизительное время**: 6-8 часов
