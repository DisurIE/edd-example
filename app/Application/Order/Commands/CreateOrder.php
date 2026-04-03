<?php

namespace App\Application\Order\Commands;

final readonly class CreateOrder
{
    public function __construct(
        public ?string $orderId,
        public string $customerId,
        public string $itemId,
        public int $quantity,
        public string $category,
        public bool $requiresLoaders,
    ) {
    }
}
