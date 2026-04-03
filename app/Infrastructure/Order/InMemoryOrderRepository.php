<?php

namespace App\Infrastructure\Order;

use App\Domain\Order\Order;
use App\Domain\Order\OrderRepository;
use App\Domain\Order\ValueObjects\OrderId;

final class InMemoryOrderRepository implements OrderRepository
{
    /**
     * @var array<string, Order>
     */
    private array $orders = [];

    public function getById(OrderId $orderId): ?Order
    {
        return $this->orders[$orderId->value()] ?? null;
    }

    public function save(Order $order): void
    {
        $this->orders[$order->orderId()->value()] = $order;
    }
}
