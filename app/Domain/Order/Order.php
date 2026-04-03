<?php

namespace App\Domain\Order;

use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderConfirmed;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\OrderFulfilled;
use App\Domain\Order\Exceptions\InvalidOrderTransition;
use App\Domain\Order\ValueObjects\CustomerId;
use App\Domain\Order\ValueObjects\ItemId;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\Quantity;
use App\Domain\Shared\AggregateRoot;

final class Order extends AggregateRoot
{
    private function __construct(
        private OrderId $orderId,
        private CustomerId $customerId,
        private ItemId $itemId,
        private Quantity $quantity,
        private OrderCategory $category,
        private bool $requiresLoaders,
        private OrderStatus $status,
    ) {
    }

    public static function create(
        OrderId $orderId,
        CustomerId $customerId,
        ItemId $itemId,
        Quantity $quantity,
        OrderCategory $category,
        bool $requiresLoaders,
    ): self {
        $order = new self(
            orderId: $orderId,
            customerId: $customerId,
            itemId: $itemId,
            quantity: $quantity,
            category: $category,
            requiresLoaders: $requiresLoaders,
            status: OrderStatus::DRAFT,
        );

        $order->record(new OrderCreated(
            orderId: $orderId,
            customerId: $customerId,
            itemId: $itemId,
            quantity: $quantity,
            category: $category,
            requiresLoaders: $requiresLoaders,
        ));

        return $order;
    }

    public static function reconstitute(
        OrderId $orderId,
        CustomerId $customerId,
        ItemId $itemId,
        Quantity $quantity,
        OrderCategory $category,
        bool $requiresLoaders,
        OrderStatus $status,
    ): self {
        return new self(
            orderId: $orderId,
            customerId: $customerId,
            itemId: $itemId,
            quantity: $quantity,
            category: $category,
            requiresLoaders: $requiresLoaders,
            status: $status,
        );
    }

    public function confirm(): void
    {
        if ($this->status !== OrderStatus::DRAFT) {
            throw InvalidOrderTransition::forAction('confirm', $this->status);
        }

        $this->status = OrderStatus::CONFIRMED;
        $this->record(new OrderConfirmed(
            orderId: $this->orderId,
            category: $this->category,
            requiresLoaders: $this->requiresLoaders,
        ));
    }

    public function cancel(): void
    {
        if (! in_array($this->status, [OrderStatus::DRAFT, OrderStatus::CONFIRMED], true)) {
            throw InvalidOrderTransition::forAction('cancel', $this->status);
        }

        $this->status = OrderStatus::CANCELLED;
        $this->record(new OrderCancelled($this->orderId));
    }

    public function fulfill(): void
    {
        if ($this->status !== OrderStatus::CONFIRMED) {
            throw InvalidOrderTransition::forAction('fulfill', $this->status);
        }

        $this->status = OrderStatus::FULFILLED;
        $this->record(new OrderFulfilled($this->orderId));
    }

    public function orderId(): OrderId
    {
        return $this->orderId;
    }

    public function customerId(): CustomerId
    {
        return $this->customerId;
    }

    public function itemId(): ItemId
    {
        return $this->itemId;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function category(): OrderCategory
    {
        return $this->category;
    }

    public function requiresLoaders(): bool
    {
        return $this->requiresLoaders;
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId->value(),
            'customer_id' => $this->customerId->value(),
            'item_id' => $this->itemId->value(),
            'quantity' => $this->quantity->value(),
            'category' => $this->category->value,
            'requires_loaders' => $this->requiresLoaders,
            'status' => $this->status->value,
        ];
    }
}
