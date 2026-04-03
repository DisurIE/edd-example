<?php

namespace App\Infrastructure\Projections;

use App\Projections\Order\OrderViewStore;

final class InMemoryOrderViewStore implements OrderViewStore
{
    /**
     * @var array<string, array<string, int|string>>
     */
    private array $details = [];

    /**
     * @var array<string, array<string, int|string>>
     */
    private array $listItems = [];

    public function putDetails(array $details): void
    {
        $this->details[$details['order_id']] = $details;
    }

    public function updateStatus(string $orderId, string $status): void
    {
        if (isset($this->details[$orderId])) {
            $this->details[$orderId]['status'] = $status;
        }

        if (isset($this->listItems[$orderId])) {
            $this->listItems[$orderId]['status'] = $status;
        }
    }

    public function findDetails(string $orderId): ?array
    {
        return $this->details[$orderId] ?? null;
    }

    public function putListItem(array $item): void
    {
        $this->listItems[$item['order_id']] = $item;
    }

    public function allListItems(): array
    {
        return array_values($this->listItems);
    }
}
