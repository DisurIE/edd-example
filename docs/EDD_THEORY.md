# Теория EDD На Примере Проекта

## Что здесь понимается под EDD

В этом проекте под EDD понимается подход, где:

- система принимает команды;
- доменная модель решает, допустимы ли изменения;
- успешное изменение публикуется как доменное событие;
- дальше другие части системы реагируют на уже случившийся факт.

На практике это значит:

- сначала намерение;
- потом проверка инвариантов;
- потом событие;
- потом реакции и проекции.

## Базовые элементы EDD

### 1. Команда

Команда отвечает на вопрос:
"Что система хочет сделать?"

Примеры из проекта:

- [CreateOrder.php](/home/disur/projects/edd-example/app/Application/Order/Commands/CreateOrder.php)
- [ConfirmOrder.php](/home/disur/projects/edd-example/app/Application/Order/Commands/ConfirmOrder.php)
- [CancelOrder.php](/home/disur/projects/edd-example/app/Application/Order/Commands/CancelOrder.php)
- [FulfillOrder.php](/home/disur/projects/edd-example/app/Application/Order/Commands/FulfillOrder.php)

Пример:

- `ConfirmOrder(order-demo-2)` означает "попробуй подтвердить заказ"

Команда не гарантирует, что действие будет выполнено.

### 2. Агрегат

Агрегат отвечает на вопрос:
"Можно ли это сделать по правилам бизнеса?"

В проекте это [Order.php](/home/disur/projects/edd-example/app/Domain/Order/Order.php).

Именно здесь лежат инварианты:

- подтвердить можно только `DRAFT`;
- завершить можно только `CONFIRMED`;
- отменить нельзя после финальных состояний;
- агрегат сам решает, какие события записать.

Важно:

- handler не должен сам решать, можно ли подтверждать заказ;
- это обязанность домена.

### 3. Доменное событие

Доменное событие отвечает на вопрос:
"Что уже произошло?"

Примеры:

- [OrderCreated.php](/home/disur/projects/edd-example/app/Domain/Order/Events/OrderCreated.php)
- [OrderConfirmed.php](/home/disur/projects/edd-example/app/Domain/Order/Events/OrderConfirmed.php)
- [OrderCancelled.php](/home/disur/projects/edd-example/app/Domain/Order/Events/OrderCancelled.php)
- [OrderFulfilled.php](/home/disur/projects/edd-example/app/Domain/Order/Events/OrderFulfilled.php)

Разница между командой и событием:

- команда: "подтверди заказ"
- событие: "заказ подтвержден"

Это принципиально разные вещи.

### 4. Обработчик события

Обработчик события отвечает на вопрос:
"Что еще должно произойти после этого факта?"

В проекте есть 2 вида таких обработчиков:

- проекторы read-model
- интеграционный handler

Проекторы:

- [OrderDetailsProjector.php](/home/disur/projects/edd-example/app/Projections/Order/OrderDetailsProjector.php)
- [OrderListProjector.php](/home/disur/projects/edd-example/app/Projections/Order/OrderListProjector.php)

Интеграционный handler:

- [RequestSpecialLogisticsHandler.php](/home/disur/projects/edd-example/app/Application/Order/Handlers/RequestSpecialLogisticsHandler.php)

## Почему события могут обрабатываться по-разному

Это одна из ключевых идей, которую показывает проект.

Берем событие `OrderConfirmed`.
Оно приходит для всех подтвержденных заказов.
Но обработчики могут интерпретировать его по-разному в зависимости от данных события.

Пример из проекта:

- обычный заказ `standard` просто обновляет read-model;
- тяжелый заказ `heavy` с `requiresLoaders=true` дополнительно создает запись во внешний логистический сервис.

Это реализовано в [RequestSpecialLogisticsHandler.php](/home/disur/projects/edd-example/app/Application/Order/Handlers/RequestSpecialLogisticsHandler.php).

Там логика такая:

- если заказ не тяжелый и грузчики не нужны, ничего не делаем;
- если заказ тяжелый или нужны грузчики, создаем запрос в `external_service_requests`.

То есть:

- событие одно и то же;
- реакции на него могут отличаться;
- решение принимается на основании состояния доменной модели, переданного в событии.

## Почему не надо тащить всю логику в event handlers

Частая ошибка:

- не проверять инварианты в агрегате;
- а переносить правила в listeners/projectors.

Это плохо, потому что тогда:

- правила размазываются по системе;
- сложно понять, где настоящее бизнес-решение;
- появляются гонки и неочевидные зависимости.

В этом проекте правильное разделение такое:

- агрегат [Order.php](/home/disur/projects/edd-example/app/Domain/Order/Order.php) решает, можно ли менять состояние;
- события только фиксируют уже случившийся факт;
- обработчики событий лишь реагируют на факт.

## Write-side и Read-side

### Write-side

Это место, где происходит принятие решения и изменение состояния.

В проекте write-side:

- команды в [app/Application/Order/Commands](/home/disur/projects/edd-example/app/Application/Order/Commands)
- handlers в [app/Application/Order/Handlers](/home/disur/projects/edd-example/app/Application/Order/Handlers)
- агрегат [Order.php](/home/disur/projects/edd-example/app/Domain/Order/Order.php)
- репозиторий [DatabaseOrderRepository.php](/home/disur/projects/edd-example/app/Infrastructure/Order/DatabaseOrderRepository.php)

### Read-side

Это место, где строится удобное представление данных для чтения.

В проекте read-side:

- [OrderDetailsProjector.php](/home/disur/projects/edd-example/app/Projections/Order/OrderDetailsProjector.php)
- [OrderListProjector.php](/home/disur/projects/edd-example/app/Projections/Order/OrderListProjector.php)
- [DatabaseOrderViewStore.php](/home/disur/projects/edd-example/app/Infrastructure/Projections/DatabaseOrderViewStore.php)

Таблицы:

- `order_details_view`
- `order_list_view`

## Event log

В проекте доменные события дополнительно пишутся в таблицу `domain_events`.

Это делает [DatabaseDomainEventStore.php](/home/disur/projects/edd-example/app/Infrastructure/Events/DatabaseDomainEventStore.php).

Это полезно для демонстрации, потому что можно показать:

- какие события реально возникли;
- в каком порядке они произошли;
- какие данные были внутри события.

## Явная регистрация обработчиков

Чтобы архитектура не была "магической", обработчики событий зарегистрированы явно в:

- [DomainEventHandlerMap.php](/home/disur/projects/edd-example/app/Infrastructure/Events/DomainEventHandlerMap.php)

Это важная учебная точка:

- видно, какие события есть;
- видно, кто на них подписан;
- видно, что один event может триггерить несколько разных реакций.

## Короткий теоретический вывод

EDD полезен там, где важно:

- явно отделить намерение от факта;
- держать бизнес-правила в домене;
- строить независимые реакции на события;
- отдельно поддерживать модель чтения;
- расширять систему новыми обработчиками без переписывания агрегата.

Именно это и показывает данный проект на примере `Order`.
