# Архитектура Проекта

## Цель архитектуры

Этот проект показывает EDD-подход на простом домене `Order`, но при этом сохраняет явные архитектурные границы.

Главная идея:

- бизнес-правила живут в домене;
- application layer оркестрирует выполнение;
- обработчики событий обновляют read-model и запускают интеграции;
- Laravel используется как runtime, DI-контейнер и инфраструктурная оболочка.

## Слои

### 1. Domain

Находится в [app/Domain](../app/Domain).

Содержит:

- базовые доменные абстракции [AggregateRoot.php](../app/Domain/Shared/AggregateRoot.php) и [DomainEvent.php](../app/Domain/Shared/DomainEvent.php)
- агрегат [Order.php](../app/Domain/Order/Order.php)
- enum-ы [OrderStatus.php](../app/Domain/Order/OrderStatus.php) и [OrderCategory.php](../app/Domain/Order/OrderCategory.php)
- value objects в [app/Domain/Order/ValueObjects](../app/Domain/Order/ValueObjects)
- доменные события в [app/Domain/Order/Events](../app/Domain/Order/Events)
- доменные исключения в [app/Domain/Order/Exceptions](../app/Domain/Order/Exceptions)
- контракт репозитория [OrderRepository.php](../app/Domain/Order/OrderRepository.php)

Что важно:

- домен не знает про Laravel facades;
- домен не зависит от БД;
- домен не знает, как именно доставляются события.

### 2. Application

Находится в [app/Application](../app/Application).

Содержит:

- command objects в [app/Application/Order/Commands](../app/Application/Order/Commands)
- command handlers в [app/Application/Order/Handlers](../app/Application/Order/Handlers)
- контракт event bus [EventBus.php](../app/Application/Order/EventBus.php)
- контракт внешнего интеграционного store [ExternalServiceRequestStore.php](../app/Application/Order/ExternalServiceRequestStore.php)

Роль слоя:

- принять команду;
- загрузить агрегат или создать его;
- вызвать доменный метод;
- сохранить агрегат;
- опубликовать накопленные доменные события.

Command handlers:

- [CreateOrderHandler.php](../app/Application/Order/Handlers/CreateOrderHandler.php)
- [ConfirmOrderHandler.php](../app/Application/Order/Handlers/ConfirmOrderHandler.php)
- [CancelOrderHandler.php](../app/Application/Order/Handlers/CancelOrderHandler.php)
- [FulfillOrderHandler.php](../app/Application/Order/Handlers/FulfillOrderHandler.php)

Отдельный пример event-driven реакции:

- [RequestSpecialLogisticsHandler.php](../app/Application/Order/Handlers/RequestSpecialLogisticsHandler.php)

Этот handler не меняет агрегат напрямую. Он реагирует на уже случившийся `OrderConfirmed`.

### 3. Projections / Read Model

Находится в [app/Projections](../app/Projections).

Содержит:

- контракт read-store [OrderViewStore.php](../app/Projections/Order/OrderViewStore.php)
- проектор карточки [OrderDetailsProjector.php](../app/Projections/Order/OrderDetailsProjector.php)
- проектор списка [OrderListProjector.php](../app/Projections/Order/OrderListProjector.php)

Роль слоя:

- не решать бизнес-правила;
- не менять write-side;
- строить read-side по факту публикации доменных событий.

### 4. Infrastructure

Находится в [app/Infrastructure](../app/Infrastructure).

Содержит:

- DB-репозиторий агрегата [DatabaseOrderRepository.php](../app/Infrastructure/Order/DatabaseOrderRepository.php)
- DB-store внешних интеграционных запросов [DatabaseExternalServiceRequestStore.php](../app/Infrastructure/Order/DatabaseExternalServiceRequestStore.php)
- event bus [LaravelDomainEventBus.php](../app/Infrastructure/Events/LaravelDomainEventBus.php)
- event store [DatabaseDomainEventStore.php](../app/Infrastructure/Events/DatabaseDomainEventStore.php)
- сериализатор payload событий [DomainEventPayloadSerializer.php](../app/Infrastructure/Events/DomainEventPayloadSerializer.php)
- явную карту обработчиков [DomainEventHandlerMap.php](../app/Infrastructure/Events/DomainEventHandlerMap.php)
- DB-store для проекций [DatabaseOrderViewStore.php](../app/Infrastructure/Projections/DatabaseOrderViewStore.php)

Здесь находится техническая реализация того, что домен описывает через контракты.

## Где происходит регистрация

### Регистрация зависимостей

Находится в [AppServiceProvider.php](../app/Providers/AppServiceProvider.php).

Там связываются:

- `OrderRepository -> DatabaseOrderRepository`
- `OrderViewStore -> DatabaseOrderViewStore`
- `ExternalServiceRequestStore -> DatabaseExternalServiceRequestStore`
- `DomainEventStore -> DatabaseDomainEventStore`
- `EventBus -> LaravelDomainEventBus`

### Регистрация event handlers

Находится в [DomainEventHandlerMap.php](../app/Infrastructure/Events/DomainEventHandlerMap.php).

Это отдельная явная точка архитектуры, где видно:

- какой доменный event существует;
- какие обработчики на него подписаны;
- какие реакции у события есть помимо изменения read-model.

## Какие данные лежат в БД

Миграции находятся в [database/migrations/2026_04_02_160000_create_edd_order_tables.php](../database/migrations/2026_04_02_160000_create_edd_order_tables.php).

Основные таблицы:

- `orders` — текущее состояние агрегатов
- `order_details_view` — read-model карточки заказа
- `order_list_view` — read-model списка заказов
- `domain_events` — журнал доменных событий
- `external_service_requests` — имитация вызовов внешнего сервиса

## Архитектурная мысль проекта

Этот пример специально показывает не просто "ивенты в Laravel", а именно разделение ответственности:

- агрегат решает, можно ли сделать действие;
- handler команды координирует сценарий;
- event bus доставляет факты;
- projectors строят модель чтения;
- интеграционные handlers запускают побочные процессы;
- база хранит и write-side, и read-side, и event log отдельно.
