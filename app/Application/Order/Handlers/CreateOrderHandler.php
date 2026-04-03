<?php

namespace App\Application\Order\Handlers;

use App\Application\Order\Commands\CreateOrder;
use App\Application\Order\EventBus;
use App\Domain\Order\Order;
use App\Domain\Order\OrderCategory;
use App\Domain\Order\OrderRepository;
use App\Domain\Order\ValueObjects\CustomerId;
use App\Domain\Order\ValueObjects\ItemId;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\Quantity;

final readonly class CreateOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private EventBus $eventBus,
    ) {
    }

    public function handle(CreateOrder $command): Order
    {
        $order = Order::create(
            orderId: $command->orderId !== null ? new OrderId($command->orderId) : OrderId::generate(),
            customerId: new CustomerId($command->customerId),
            itemId: new ItemId($command->itemId),
            quantity: new Quantity($command->quantity),
            category: OrderCategory::from($command->category),
            requiresLoaders: $command->requiresLoaders,
        );

        $this->orders->save($order);
        $this->eventBus->publish($order->releaseRecordedEvents());

        return $order;
    }
}
