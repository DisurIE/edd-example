<?php

namespace App\Domain\Order\ValueObjects;

final readonly class CustomerId
{
    public function __construct(
        private string $value,
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('CustomerId cannot be empty.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
