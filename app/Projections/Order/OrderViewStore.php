<?php

namespace App\Projections\Order;

interface OrderViewStore
{
    /**
     * @param array<string, int|string> $details
     */
    public function putDetails(array $details): void;

    public function updateStatus(string $orderId, string $status): void;

    /**
     * @return array<string, int|string>|null
     */
    public function findDetails(string $orderId): ?array;

    /**
     * @param array<string, int|string> $item
     */
    public function putListItem(array $item): void;

    /**
     * @return list<array<string, int|string>>
     */
    public function allListItems(): array;
}
