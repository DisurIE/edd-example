<?php

namespace App\Domain\Order\Events;

use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Shared\DomainEvent;
use DateTimeImmutable;

final readonly class OrderFulfilled implements DomainEvent
{
    public function __construct(
        public OrderId $orderId,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }

    public function eventName(): string
    {
        return 'order.fulfilled';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
