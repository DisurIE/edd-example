<?php

namespace App\Domain\Order\ValueObjects;

final readonly class Quantity
{
    public function __construct(
        private int $value,
    ) {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}
