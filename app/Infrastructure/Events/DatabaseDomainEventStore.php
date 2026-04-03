<?php

namespace App\Infrastructure\Events;

use App\Domain\Shared\DomainEvent;
use Illuminate\Support\Facades\DB;

final readonly class DatabaseDomainEventStore implements DomainEventStore
{
    public function __construct(
        private DomainEventPayloadSerializer $serializer,
    ) {
    }

    public function append(DomainEvent $event): void
    {
        DB::table('domain_events')->insert([
            'aggregate_id' => $this->serializer->aggregateId($event),
            'event_name' => $event->eventName(),
            'event_class' => $event::class,
            'payload' => json_encode($this->serializer->serialize($event), JSON_THROW_ON_ERROR),
            'occurred_at' => $event->occurredAt(),
            'created_at' => now(),
        ]);
    }

    public function truncate(): void
    {
        DB::table('domain_events')->truncate();
    }

    public function all(): array
    {
        return DB::table('domain_events')
            ->orderBy('id')
            ->get()
            ->map(fn (object $row): array => [
                'id' => $row->id,
                'aggregate_id' => $row->aggregate_id,
                'event_name' => $row->event_name,
                'event_class' => $row->event_class,
                'payload' => json_decode($row->payload, true, 512, JSON_THROW_ON_ERROR),
                'occurred_at' => $row->occurred_at,
            ])
            ->all();
    }
}
