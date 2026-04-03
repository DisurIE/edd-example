<?php

namespace App\Infrastructure\Order;

use App\Application\Order\ExternalServiceRequestStore;
use Illuminate\Support\Facades\DB;

final class DatabaseExternalServiceRequestStore implements ExternalServiceRequestStore
{
    public function create(
        string $orderId,
        string $serviceName,
        string $requestType,
        array $payload,
    ): void {
        DB::table('external_service_requests')->insert([
            'order_id' => $orderId,
            'service_name' => $serviceName,
            'request_type' => $requestType,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }

    public function truncate(): void
    {
        DB::table('external_service_requests')->truncate();
    }

    public function all(): array
    {
        return DB::table('external_service_requests')
            ->orderBy('id')
            ->get()
            ->map(fn (object $row): array => [
                'id' => $row->id,
                'order_id' => $row->order_id,
                'service_name' => $row->service_name,
                'request_type' => $row->request_type,
                'payload' => json_decode($row->payload, true, 512, JSON_THROW_ON_ERROR),
                'created_at' => $row->created_at,
            ])
            ->all();
    }
}
