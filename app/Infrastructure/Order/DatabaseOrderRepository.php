<?php

namespace App\Infrastructure\Order;

use App\Domain\Order\Order;
use App\Domain\Order\OrderCategory;
use App\Domain\Order\OrderRepository;
use App\Domain\Order\OrderStatus;
use App\Domain\Order\ValueObjects\CustomerId;
use App\Domain\Order\ValueObjects\ItemId;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\Quantity;
use Illuminate\Support\Facades\DB;

final class DatabaseOrderRepository implements OrderRepository
{
    public function getById(OrderId $orderId): ?Order
    {
        $row = DB::table('orders')->where('order_id', $orderId->value())->first();

        if ($row === null) {
            return null;
        }

        return Order::reconstitute(
            orderId: new OrderId($row->order_id),
            customerId: new CustomerId($row->customer_id),
            itemId: new ItemId($row->item_id),
            quantity: new Quantity((int) $row->quantity),
            category: OrderCategory::from($row->category),
            requiresLoaders: (bool) $row->requires_loaders,
            status: OrderStatus::from($row->status),
        );
    }

    public function save(Order $order): void
    {
        DB::table('orders')->updateOrInsert(
            ['order_id' => $order->orderId()->value()],
            [
                'customer_id' => $order->customerId()->value(),
                'item_id' => $order->itemId()->value(),
                'quantity' => $order->quantity()->value(),
                'category' => $order->category()->value,
                'requires_loaders' => $order->requiresLoaders(),
                'status' => $order->status()->value,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }
}
