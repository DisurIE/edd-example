<?php

namespace App\Providers;

use App\Application\Order\EventBus;
use App\Application\Order\ExternalServiceRequestStore;
use App\Infrastructure\Events\DatabaseDomainEventStore;
use App\Infrastructure\Events\DomainEventHandlerMap;
use App\Infrastructure\Events\DomainEventPayloadSerializer;
use App\Infrastructure\Events\DomainEventStore;
use App\Domain\Order\OrderRepository;
use App\Infrastructure\Events\LaravelDomainEventBus;
use App\Infrastructure\Order\DatabaseExternalServiceRequestStore;
use App\Infrastructure\Order\DatabaseOrderRepository;
use App\Infrastructure\Projections\DatabaseOrderViewStore;
use App\Projections\Order\OrderViewStore;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OrderRepository::class, DatabaseOrderRepository::class);
        $this->app->singleton(OrderViewStore::class, DatabaseOrderViewStore::class);
        $this->app->singleton(ExternalServiceRequestStore::class, DatabaseExternalServiceRequestStore::class);
        $this->app->singleton(DomainEventPayloadSerializer::class);
        $this->app->singleton(DomainEventStore::class, DatabaseDomainEventStore::class);

        $this->app->singleton(LaravelDomainEventBus::class, function ($app) {
            return new LaravelDomainEventBus(
                $app,
                $app->make(DomainEventStore::class),
                DomainEventHandlerMap::handlers(),
            );
        });

        $this->app->singleton(EventBus::class, LaravelDomainEventBus::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
