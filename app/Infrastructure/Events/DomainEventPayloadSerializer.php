<?php

namespace App\Infrastructure\Events;

use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderConfirmed;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\OrderFulfilled;
use App\Domain\Shared\DomainEvent;

final class DomainEventPayloadSerializer
{
    /**
     * @return array<string, int|string>
     */
    public function serialize(DomainEvent $event): array
    {
        return match (true) {
            $event instanceof OrderCreated => [
                'order_id' => $event->orderId->value(),
                'customer_id' => $event->customerId->value(),
                'item_id' => $event->itemId->value(),
                'quantity' => $event->quantity->value(),
                'category' => $event->category->value,
                'requires_loaders' => $event->requiresLoaders,
            ],
            $event instanceof OrderConfirmed => [
                'order_id' => $event->orderId->value(),
                'category' => $event->category->value,
                'requires_loaders' => $event->requiresLoaders,
            ],
            $event instanceof OrderCancelled => [
                'order_id' => $event->orderId->value(),
            ],
            $event instanceof OrderFulfilled => [
                'order_id' => $event->orderId->value(),
            ],
            default => [],
        };
    }

    public function aggregateId(DomainEvent $event): string
    {
        return match (true) {
            $event instanceof OrderCreated => $event->orderId->value(),
            $event instanceof OrderConfirmed => $event->orderId->value(),
            $event instanceof OrderCancelled => $event->orderId->value(),
            $event instanceof OrderFulfilled => $event->orderId->value(),
            default => 'unknown',
        };
    }
}
