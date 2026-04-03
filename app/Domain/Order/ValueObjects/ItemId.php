<?php

namespace App\Domain\Order\ValueObjects;

final readonly class ItemId
{
    public function __construct(
        private string $value,
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('ItemId cannot be empty.');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
