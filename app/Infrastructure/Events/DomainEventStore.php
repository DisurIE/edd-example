<?php

namespace App\Infrastructure\Events;

use App\Domain\Shared\DomainEvent;

interface DomainEventStore
{
    public function append(DomainEvent $event): void;

    public function truncate(): void;

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array;
}
