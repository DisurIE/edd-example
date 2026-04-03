<?php

namespace App\Application\Order\Handlers;

use App\Application\Order\ExternalServiceRequestStore;
use App\Domain\Order\Events\OrderConfirmed;

final readonly class RequestSpecialLogisticsHandler
{
    public function __construct(
        private ExternalServiceRequestStore $requests,
    ) {
    }

    public function __invoke(OrderConfirmed $event): void
    {
        if ($event->category->value !== 'heavy' && $event->requiresLoaders === false) {
            return;
        }

        $this->requests->create(
            orderId: $event->orderId->value(),
            serviceName: 'logistics-provider',
            requestType: 'reserve-truck-and-loaders',
            payload: [
                'order_id' => $event->orderId->value(),
                'category' => $event->category->value,
                'requires_loaders' => $event->requiresLoaders,
                'truck_type' => 'gazelle',
                'loaders_needed' => $event->requiresLoaders ? 2 : 0,
            ],
        );
    }
}
