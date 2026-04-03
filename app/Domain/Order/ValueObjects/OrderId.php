<?php

namespace App\Domain\Order\ValueObjects;

final readonly class OrderId
{
    public function __construct(
        private string $value,
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('OrderId cannot be empty.');
        }
    }

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(16)));
    }

    public function value(): string
    {
        return $this->value;
    }
}
