<?php

namespace App\Infrastructure\Projections;

use App\Projections\Order\OrderViewStore;
use Illuminate\Support\Facades\DB;

final class DatabaseOrderViewStore implements OrderViewStore
{
    public function putDetails(array $details): void
    {
        DB::table('order_details_view')->updateOrInsert(
            ['order_id' => $details['order_id']],
            [
                'customer_id' => $details['customer_id'],
                'item_id' => $details['item_id'],
                'quantity' => $details['quantity'],
                'category' => $details['category'],
                'requires_loaders' => $details['requires_loaders'],
                'status' => $details['status'],
                'updated_at' => now(),
            ],
        );
    }

    public function updateStatus(string $orderId, string $status): void
    {
        DB::table('order_details_view')
            ->where('order_id', $orderId)
            ->update([
                'status' => $status,
                'updated_at' => now(),
            ]);

        DB::table('order_list_view')
            ->where('order_id', $orderId)
            ->update([
                'status' => $status,
                'updated_at' => now(),
            ]);
    }

    public function findDetails(string $orderId): ?array
    {
        $row = DB::table('order_details_view')->where('order_id', $orderId)->first();

        return $row === null ? null : [
            'order_id' => $row->order_id,
            'customer_id' => $row->customer_id,
            'item_id' => $row->item_id,
            'quantity' => (int) $row->quantity,
            'category' => $row->category,
            'requires_loaders' => (bool) $row->requires_loaders,
            'status' => $row->status,
        ];
    }

    public function putListItem(array $item): void
    {
        DB::table('order_list_view')->updateOrInsert(
            ['order_id' => $item['order_id']],
            [
                'customer_id' => $item['customer_id'],
                'category' => $item['category'],
                'status' => $item['status'],
                'updated_at' => now(),
            ],
        );
    }

    public function allListItems(): array
    {
        return DB::table('order_list_view')
            ->orderBy('order_id')
            ->get()
            ->map(fn (object $row): array => [
                'order_id' => $row->order_id,
                'customer_id' => $row->customer_id,
                'category' => $row->category,
                'status' => $row->status,
            ])
            ->all();
    }
}
