<?php

namespace App\Infrastructure\Events;

use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderConfirmed;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\OrderFulfilled;
use App\Application\Order\Handlers\RequestSpecialLogisticsHandler;
use App\Projections\Order\OrderDetailsProjector;
use App\Projections\Order\OrderListProjector;

final class DomainEventHandlerMap
{
    /**
     * @return array<class-string, list<class-string>>
     */
    public static function handlers(): array
    {
        return [
            OrderCreated::class => [
                OrderDetailsProjector::class,
                OrderListProjector::class,
            ],
            OrderConfirmed::class => [
                OrderDetailsProjector::class,
                OrderListProjector::class,
                RequestSpecialLogisticsHandler::class,
            ],
            OrderCancelled::class => [
                OrderDetailsProjector::class,
                OrderListProjector::class,
            ],
            OrderFulfilled::class => [
                OrderDetailsProjector::class,
                OrderListProjector::class,
            ],
        ];
    }
}
