<?php

namespace App\Domain\Order\Events;

use App\Domain\Order\OrderCategory;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Shared\DomainEvent;
use DateTimeImmutable;

final readonly class OrderConfirmed implements DomainEvent
{
    public function __construct(
        public OrderId $orderId,
        public OrderCategory $category,
        public bool $requiresLoaders,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }

    public function eventName(): string
    {
        return 'order.confirmed';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
