<?php

namespace App\Projections\Order;

use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderConfirmed;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\OrderFulfilled;

final readonly class OrderDetailsProjector
{
    public function __construct(
        private OrderViewStore $store,
    ) {
    }

    public function __invoke(object $event): void
    {
        match (true) {
            $event instanceof OrderCreated => $this->onOrderCreated($event),
            $event instanceof OrderConfirmed => $this->store->updateStatus($event->orderId->value(), 'confirmed'),
            $event instanceof OrderCancelled => $this->store->updateStatus($event->orderId->value(), 'cancelled'),
            $event instanceof OrderFulfilled => $this->store->updateStatus($event->orderId->value(), 'fulfilled'),
            default => null,
        };
    }

    private function onOrderCreated(OrderCreated $event): void
    {
        $this->store->putDetails([
            'order_id' => $event->orderId->value(),
            'customer_id' => $event->customerId->value(),
            'item_id' => $event->itemId->value(),
            'quantity' => $event->quantity->value(),
            'category' => $event->category->value,
            'requires_loaders' => $event->requiresLoaders,
            'status' => 'draft',
        ]);
    }
}
