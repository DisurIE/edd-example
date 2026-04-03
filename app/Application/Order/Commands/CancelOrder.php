<?php

namespace App\Application\Order\Commands;

final readonly class CancelOrder
{
    public function __construct(
        public string $orderId,
    ) {
    }
}
