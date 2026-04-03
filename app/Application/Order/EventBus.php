<?php

namespace App\Application\Order;

use App\Domain\Shared\DomainEvent;

interface EventBus
{
    /**
     * @param list<DomainEvent> $events
     */
    public function publish(array $events): void;
}
