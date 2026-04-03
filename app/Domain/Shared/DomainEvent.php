<?php

namespace App\Domain\Shared;

use DateTimeImmutable;

interface DomainEvent
{
    public function eventName(): string;

    public function occurredAt(): DateTimeImmutable;
}
