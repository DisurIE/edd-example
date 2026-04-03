<?php

namespace App\Application\Order\Commands;

final readonly class ConfirmOrder
{
    public function __construct(
        public string $orderId,
    ) {
    }
}
