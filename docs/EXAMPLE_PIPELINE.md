# Pipeline Нашего Примера

## Что описывает этот документ

Этот файл показывает полный flow нашего demo-сценария:

- кто кого вызывает;
- где принимается бизнес-решение;
- где создается доменное событие;
- кто подписан на событие;
- какие таблицы в итоге заполняются.

Основная точка входа:

- [RunOrderDemo.php](../app/Console/Commands/RunOrderDemo.php)

## Общая схема

Верхнеуровневый pipeline выглядит так:

1. `artisan demo:order-flow` запускает demo-команду.
2. Demo-команда вызывает command handlers.
3. Handler обращается к агрегату `Order`.
4. Агрегат валидирует инварианты и записывает доменное событие.
5. Handler сохраняет агрегат в `orders`.
6. Handler публикует recorded events через `EventBus`.
7. `LaravelDomainEventBus` пишет событие в `domain_events`.
8. `LaravelDomainEventBus` вызывает подписанные handlers из `DomainEventHandlerMap`.
9. Projectors обновляют `order_details_view` и `order_list_view`.
10. Интеграционный handler при необходимости пишет запись в `external_service_requests`.

## Flow 1. Создание заказа

### Шаг 1. Demo-команда вызывает CreateOrderHandler

Файл:

- [RunOrderDemo.php](../app/Console/Commands/RunOrderDemo.php)

Внутри вызывается:

- [CreateOrderHandler.php](../app/Application/Order/Handlers/CreateOrderHandler.php)

### Шаг 2. Handler создает агрегат

Handler вызывает:

- `Order::create(...)`

Файл:

- [Order.php](../app/Domain/Order/Order.php)

### Шаг 3. Агрегат записывает OrderCreated

Внутри `Order::create()` агрегат:

- создает новый `Order`
- ставит статус `DRAFT`
- вызывает `record(new OrderCreated(...))`

Событие:

- [OrderCreated.php](../app/Domain/Order/Events/OrderCreated.php)

### Шаг 4. Handler сохраняет агрегат

Через:

- [DatabaseOrderRepository.php](../app/Infrastructure/Order/DatabaseOrderRepository.php)

Таблица:

- `orders`

### Шаг 5. Handler публикует recorded events

Через:

- [LaravelDomainEventBus.php](../app/Infrastructure/Events/LaravelDomainEventBus.php)

Что происходит дальше:

- событие сохраняется в `domain_events`
- вызываются подписчики из [DomainEventHandlerMap.php](../app/Infrastructure/Events/DomainEventHandlerMap.php)

### Шаг 6. Projectors строят read-model

Подписчики на `OrderCreated`:

- [OrderDetailsProjector.php](../app/Projections/Order/OrderDetailsProjector.php)
- [OrderListProjector.php](../app/Projections/Order/OrderListProjector.php)

Таблицы:

- `order_details_view`
- `order_list_view`

## Flow 2. Подтверждение обычного заказа

### Шаг 1. Demo-команда вызывает ConfirmOrderHandler

Файл:

- [ConfirmOrderHandler.php](../app/Application/Order/Handlers/ConfirmOrderHandler.php)

### Шаг 2. Handler загружает агрегат

Через:

- [DatabaseOrderRepository.php](../app/Infrastructure/Order/DatabaseOrderRepository.php)

### Шаг 3. Агрегат выполняет confirm()

Внутри [Order.php](../app/Domain/Order/Order.php):

- проверяется, что статус `DRAFT`
- статус меняется на `CONFIRMED`
- записывается `OrderConfirmed`

Событие:

- [OrderConfirmed.php](../app/Domain/Order/Events/OrderConfirmed.php)

### Шаг 4. Event bus публикует событие

`LaravelDomainEventBus`:

- пишет событие в `domain_events`
- вызывает подписчиков

Для `OrderConfirmed` подписаны:

- [OrderDetailsProjector.php](../app/Projections/Order/OrderDetailsProjector.php)
- [OrderListProjector.php](../app/Projections/Order/OrderListProjector.php)
- [RequestSpecialLogisticsHandler.php](../app/Application/Order/Handlers/RequestSpecialLogisticsHandler.php)

### Шаг 5. Почему для обычного заказа интеграция не срабатывает

В `RequestSpecialLogisticsHandler` есть условие:

- если заказ не `heavy`
- и `requiresLoaders == false`
- тогда handler ничего не делает

Итог:

- read-model обновляется
- внешний сервис не вызывается

## Flow 3. Подтверждение тяжелого заказа

Этот шаг нужен именно для демонстрации условной обработки одного и того же события.

### Исходные данные заказа

Во втором сценарии demo-команда создает заказ:

- `category = heavy`
- `requires_loaders = true`

Это видно в:

- [RunOrderDemo.php](../app/Console/Commands/RunOrderDemo.php)

### Что происходит после ConfirmOrder

После `OrderConfirmed` происходят три независимые реакции:

1. `OrderDetailsProjector` обновляет карточку заказа
2. `OrderListProjector` обновляет список заказов
3. `RequestSpecialLogisticsHandler` создает интеграционный запрос

### Где создается внешняя интеграция

Handler:

- [RequestSpecialLogisticsHandler.php](../app/Application/Order/Handlers/RequestSpecialLogisticsHandler.php)

Store:

- [DatabaseExternalServiceRequestStore.php](../app/Infrastructure/Order/DatabaseExternalServiceRequestStore.php)

Таблица:

- `external_service_requests`

Смысл записи:

- для тяжелого заказа резервируется `gazelle`
- и при необходимости грузчики

Это не обязательно "реальный сервис".
В учебном проекте это имитация внешней интеграции, чтобы показать сам паттерн.

## Flow 4. Отмена тяжелого заказа

После подтверждения demo-команда вызывает `CancelOrder`.

Дальше:

1. [CancelOrderHandler.php](../app/Application/Order/Handlers/CancelOrderHandler.php) загружает агрегат
2. агрегат в [Order.php](../app/Domain/Order/Order.php) выполняет `cancel()`
3. агрегат записывает [OrderCancelled.php](../app/Domain/Order/Events/OrderCancelled.php)
4. `EventBus` публикует событие
5. projectors обновляют read-model

Важно:

- запись во внешний сервис уже существует, потому что она была создана на этапе `OrderConfirmed`
- отмена не удаляет ее автоматически
- это отдельная учебная мысль: события фиксируют историю фактов, а не "откатывают прошлое"

## Полная таблица вызовов

Для понимания можно читать flow так:

1. [RunOrderDemo.php](../app/Console/Commands/RunOrderDemo.php)
2. [CreateOrderHandler.php](../app/Application/Order/Handlers/CreateOrderHandler.php) / [ConfirmOrderHandler.php](../app/Application/Order/Handlers/ConfirmOrderHandler.php) / [CancelOrderHandler.php](../app/Application/Order/Handlers/CancelOrderHandler.php) / [FulfillOrderHandler.php](../app/Application/Order/Handlers/FulfillOrderHandler.php)
3. [Order.php](../app/Domain/Order/Order.php)
4. [DatabaseOrderRepository.php](../app/Infrastructure/Order/DatabaseOrderRepository.php)
5. [LaravelDomainEventBus.php](../app/Infrastructure/Events/LaravelDomainEventBus.php)
6. [DatabaseDomainEventStore.php](../app/Infrastructure/Events/DatabaseDomainEventStore.php)
7. [DomainEventHandlerMap.php](../app/Infrastructure/Events/DomainEventHandlerMap.php)
8. [OrderDetailsProjector.php](../app/Projections/Order/OrderDetailsProjector.php) и [OrderListProjector.php](../app/Projections/Order/OrderListProjector.php)
9. [RequestSpecialLogisticsHandler.php](../app/Application/Order/Handlers/RequestSpecialLogisticsHandler.php)
10. [DatabaseExternalServiceRequestStore.php](../app/Infrastructure/Order/DatabaseExternalServiceRequestStore.php)

## Что показывать ученикам в базе

После `make demo` полезно открыть таблицы в таком порядке:

1. `orders`
2. `domain_events`
3. `order_details_view`
4. `order_list_view`
5. `external_service_requests`

Так хорошо видно:

- write-side состояние агрегата;
- историю событий;
- отдельно построенный read-side;
- дополнительную реакцию на тяжелый заказ.
