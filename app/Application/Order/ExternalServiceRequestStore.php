<?php

namespace App\Application\Order;

interface ExternalServiceRequestStore
{
    /**
     * @param array<string, mixed> $payload
     */
    public function create(
        string $orderId,
        string $serviceName,
        string $requestType,
        array $payload,
    ): void;

    public function truncate(): void;

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array;
}
