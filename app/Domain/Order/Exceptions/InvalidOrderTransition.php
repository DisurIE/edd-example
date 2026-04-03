<?php

namespace App\Domain\Order\Exceptions;

use App\Domain\Order\OrderStatus;
use DomainException;

final class InvalidOrderTransition extends DomainException
{
    public static function forAction(string $action, OrderStatus $status): self
    {
        return new self(sprintf(
            'Cannot %s order when status is %s.',
            $action,
            $status->value,
        ));
    }
}
