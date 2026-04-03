<?php

namespace App\Domain\Order\Events;

use App\Domain\Order\ValueObjects\CustomerId;
use App\Domain\Order\ValueObjects\ItemId;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\Quantity;
use App\Domain\Order\OrderCategory;
use App\Domain\Shared\DomainEvent;
use DateTimeImmutable;

final readonly class OrderCreated implements DomainEvent
{
    public function __construct(
        public OrderId $orderId,
        public CustomerId $customerId,
        public ItemId $itemId,
        public Quantity $quantity,
        public OrderCategory $category,
        public bool $requiresLoaders,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }

    public function eventName(): string
    {
        return 'order.created';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
