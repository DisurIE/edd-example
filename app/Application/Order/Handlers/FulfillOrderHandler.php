<?php

namespace App\Application\Order\Handlers;

use App\Application\Order\Commands\FulfillOrder;
use App\Application\Order\EventBus;
use App\Domain\Order\Exceptions\OrderNotFound;
use App\Domain\Order\Order;
use App\Domain\Order\OrderRepository;
use App\Domain\Order\ValueObjects\OrderId;

final readonly class FulfillOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private EventBus $eventBus,
    ) {
    }

    public function handle(FulfillOrder $command): Order
    {
        $order = $this->orders->getById(new OrderId($command->orderId));

        if ($order === null) {
            throw OrderNotFound::withId($command->orderId);
        }

        $order->fulfill();
        $this->orders->save($order);
        $this->eventBus->publish($order->releaseRecordedEvents());

        return $order;
    }
}
