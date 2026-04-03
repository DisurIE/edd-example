<?php

namespace App\Domain\Order\Exceptions;

use DomainException;

final class OrderNotFound extends DomainException
{
    public static function withId(string $orderId): self
    {
        return new self(sprintf('Order with id %s was not found.', $orderId));
    }
}
