<?php

namespace App\Domain\Order;

use App\Domain\Order\ValueObjects\OrderId;

interface OrderRepository
{
    public function getById(OrderId $orderId): ?Order;

    public function save(Order $order): void;
}
