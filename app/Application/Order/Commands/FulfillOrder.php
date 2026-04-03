<?php

namespace App\Application\Order\Commands;

final readonly class FulfillOrder
{
    public function __construct(
        public string $orderId,
    ) {
    }
}
