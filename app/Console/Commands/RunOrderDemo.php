<?php

namespace App\Console\Commands;

use App\Application\Order\Commands\CancelOrder;
use App\Application\Order\Commands\ConfirmOrder;
use App\Application\Order\Commands\CreateOrder;
use App\Application\Order\Commands\FulfillOrder;
use App\Application\Order\ExternalServiceRequestStore;
use App\Application\Order\Handlers\CancelOrderHandler;
use App\Application\Order\Handlers\ConfirmOrderHandler;
use App\Application\Order\Handlers\CreateOrderHandler;
use App\Application\Order\Handlers\FulfillOrderHandler;
use App\Domain\Order\OrderRepository;
use App\Domain\Order\ValueObjects\OrderId;
use App\Infrastructure\Events\DomainEventStore;
use App\Infrastructure\Events\LaravelDomainEventBus;
use App\Projections\Order\OrderViewStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

final class RunOrderDemo extends Command
{
    protected $signature = 'demo:order-flow';

    protected $description = 'Запускает демонстрацию EDD-потока заказа.';

    public function handle(
        CreateOrderHandler $createOrder,
        ConfirmOrderHandler $confirmOrder,
        FulfillOrderHandler $fulfillOrder,
        CancelOrderHandler $cancelOrder,
        OrderRepository $orders,
        OrderViewStore $viewStore,
        DomainEventStore $domainEventStore,
        ExternalServiceRequestStore $externalRequests,
        LaravelDomainEventBus $eventBus,
    ): int {
        DB::table('order_list_view')->truncate();
        DB::table('order_details_view')->truncate();
        DB::table('orders')->truncate();
        $domainEventStore->truncate();
        $externalRequests->truncate();
        $eventBus->flushPublishedEvents();

        $firstOrder = $createOrder->handle(new CreateOrder(
            orderId: 'order-demo-1',
            customerId: 'customer-1',
            itemId: 'item-apple',
            quantity: 2,
            category: 'standard',
            requiresLoaders: false,
        ));

        $confirmOrder->handle(new ConfirmOrder($firstOrder->orderId()->value()));
        $fulfillOrder->handle(new FulfillOrder($firstOrder->orderId()->value()));

        $secondOrder = $createOrder->handle(new CreateOrder(
            orderId: 'order-demo-2',
            customerId: 'customer-2',
            itemId: 'item-orange',
            quantity: 12,
            category: 'heavy',
            requiresLoaders: true,
        ));

        $confirmOrder->handle(new ConfirmOrder($secondOrder->orderId()->value()));
        $cancelOrder->handle(new CancelOrder($secondOrder->orderId()->value()));

        $this->components->info('Выполненные команды');
        $this->line('- Создать заказ: order-demo-1');
        $this->line('- Подтвердить заказ: order-demo-1');
        $this->line('- Завершить заказ: order-demo-1');
        $this->line('- Создать заказ: order-demo-2');
        $this->line('- Подтвердить заказ: order-demo-2');
        $this->line('- Отменить заказ: order-demo-2');
        $this->newLine();

        $this->components->info('Опубликованные доменные события');
        foreach ($eventBus->publishedEvents() as $event) {
            $this->line(sprintf(
                '- %s в %s',
                $event->eventName(),
                $event->occurredAt()->format(DATE_ATOM),
            ));
        }
        $this->newLine();

        $this->components->info('Состояние агрегатов');
        $this->line($this->toJson($orders->getById(new OrderId('order-demo-1'))?->toArray() ?? []));
        $this->line($this->toJson($orders->getById(new OrderId('order-demo-2'))?->toArray() ?? []));
        $this->newLine();

        $this->components->info('Состояние read-model');
        $this->line($this->toJson($viewStore->findDetails('order-demo-1') ?? []));
        $this->line($this->toJson($viewStore->findDetails('order-demo-2') ?? []));
        $this->line($this->toJson($viewStore->allListItems()));
        $this->newLine();

        $this->components->info('Запросы во внешний сервис');
        $this->line($this->toJson($externalRequests->all()));
        $this->newLine();

        $this->components->info('Записи в таблице domain_events');
        $this->line($this->toJson($domainEventStore->all()));

        return self::SUCCESS;
    }

    /**
     * @param array<mixed> $payload
     */
    private function toJson(array $payload): string
    {
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
