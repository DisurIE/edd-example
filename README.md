# EDD Example On Laravel

Это демонстрационный проект на `PHP` и `Laravel`, который показывает базовый EDD-поток через доменные события.

В качестве учебного домена используется `Order`.

## Документация

Подробные документы вынесены отдельно:

- архитектура проекта: [docs/ARCHITECTURE.md](/home/disur/projects/edd-example/docs/ARCHITECTURE.md)
- теория EDD на примерах из кода: [docs/EDD_THEORY.md](/home/disur/projects/edd-example/docs/EDD_THEORY.md)
- полный pipeline нашего demo-flow: [docs/EXAMPLE_PIPELINE.md](/home/disur/projects/edd-example/docs/EXAMPLE_PIPELINE.md)

## Что показывает пример

- команда выражает намерение;
- aggregate root проверяет инварианты;
- агрегат записывает доменные события;
- application layer сохраняет агрегат и публикует события;
- projectors обновляют read-model;
- Laravel используется как runtime и DI-контейнер, а не как место для бизнес-правил.

## Доменный поток

Команды:

- `CreateOrder`
- `ConfirmOrder`
- `CancelOrder`
- `FulfillOrder`

События:

- `OrderCreated`
- `OrderConfirmed`
- `OrderCancelled`
- `OrderFulfilled`

Статусы:

- `DRAFT`
- `CONFIRMED`
- `CANCELLED`
- `FULFILLED`

Инварианты:

- подтвердить можно только заказ в `DRAFT`;
- отменить можно заказ в `DRAFT` или `CONFIRMED`;
- завершить можно только заказ в `CONFIRMED`;
- после `CANCELLED` и `FULFILLED` заказ больше не меняется.

## Структура

```text
app/
  Domain/
    Shared/
    Order/
  Application/
    Order/
  Projections/
    Order/
  Infrastructure/
    Events/
    Order/
    Projections/
  Console/
    Commands/
```

Слои:

- `Domain` содержит агрегат, value objects, события и инварианты.
- `Application` содержит команды, handlers и orchestration.
- `Projections` содержит read-side обработчики и контракт хранилища представлений.
- `Infrastructure` содержит in-memory реализации репозитория, event bus и projection store.
- `Console` содержит демонстрационную artisan-команду.

## Запуск демо в Docker

Поднять окружение:

```bash
make up
```

Прогнать демонстрационный сценарий:

```bash
make demo
```

Остановить окружение:

```bash
make down
```

Удалить окружение вместе с данными MySQL:

```bash
make destroy
```

## Что доступно после запуска

- приложение Laravel: `http://localhost:8000`
- MySQL для подключения из PhpStorm: `localhost:3307`

Параметры подключения из PhpStorm:

- Host: `127.0.0.1`
- Port: `3307`
- Database: `edd_example`
- User: `edd_user`
- Password: `edd_password`

Быстро вывести эти креды в терминал:

```bash
make db-creds
```

## Запуск демо

```bash
php artisan demo:order-flow
```

Команда выводит:

- выполненные команды;
- опубликованные доменные события;
- итоговое состояние агрегатов;
- состояние read-model.
- записи из таблицы `domain_events`.

## Где смотреть код

- агрегат: [app/Domain/Order/Order.php](/home/disur/projects/edd-example/app/Domain/Order/Order.php)
- события: [app/Domain/Order/Events](/home/disur/projects/edd-example/app/Domain/Order/Events)
- handlers: [app/Application/Order/Handlers](/home/disur/projects/edd-example/app/Application/Order/Handlers)
- event bus: [app/Infrastructure/Events/LaravelDomainEventBus.php](/home/disur/projects/edd-example/app/Infrastructure/Events/LaravelDomainEventBus.php)
- projectors: [app/Projections/Order](/home/disur/projects/edd-example/app/Projections/Order)
- демо-команда: [app/Console/Commands/RunOrderDemo.php](/home/disur/projects/edd-example/app/Console/Commands/RunOrderDemo.php)

## Ограничения примера

В проекте специально нет:

- HTTP API;
- базы данных;
- Eloquent persistence;
- message broker;
- очередей;
- outbox;
- saga/process manager;
- event sourcing как основной модели хранения.

Это сделано специально, чтобы было проще увидеть сам принцип EDD без инфраструктурного шума.

## Какие таблицы появятся в базе

После `php artisan migrate` и запуска `demo:order-flow` в MySQL можно увидеть:

- `orders` — текущее состояние агрегатов;
- `order_details_view` — read-model карточки заказа;
- `order_list_view` — read-model списка заказов;
- `domain_events` — журнал опубликованных доменных событий.
