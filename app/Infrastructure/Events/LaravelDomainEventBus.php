<?php

namespace App\Infrastructure\Events;

use App\Application\Order\EventBus;
use App\Domain\Shared\DomainEvent;
use Illuminate\Contracts\Container\Container;

final class LaravelDomainEventBus implements EventBus
{
    /**
     * @var list<DomainEvent>
     */
    private array $publishedEvents = [];

    /**
     * @param array<class-string, list<class-string>> $listeners
     */
    public function __construct(
        private readonly Container $container,
        private readonly DomainEventStore $eventStore,
        private readonly array $listeners = [],
    ) {
    }

    public function publish(array $events): void
    {
        foreach ($events as $event) {
            $this->publishedEvents[] = $event;
            $this->eventStore->append($event);

            foreach ($this->listeners[$event::class] ?? [] as $listenerClass) {
                $listener = $this->container->make($listenerClass);
                $listener($event);
            }
        }
    }

    /**
     * @return list<DomainEvent>
     */
    public function publishedEvents(): array
    {
        return $this->publishedEvents;
    }

    public function flushPublishedEvents(): void
    {
        $this->publishedEvents = [];
    }
}
