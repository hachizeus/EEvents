<?php

namespace HiEvents\Providers;

use HiEvents\Events\OrderStatusChangedEvent;
use HiEvents\Listeners\Event\UpdateEventStatsListener;
use HiEvents\Listeners\Order\SendOrderDetailsEmailListener;
use HiEvents\Listeners\Webhook\WebhookEventListener;
use HiEvents\Services\Infrastructure\DomainEvents\Events\AttendeeEvent;
use HiEvents\Services\Infrastructure\DomainEvents\Events\CheckinEvent;
use HiEvents\Services\Infrastructure\DomainEvents\Events\OrderEvent;
use HiEvents\Services\Infrastructure\DomainEvents\Events\ProductEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Explicitly registered event listeners (not relying on auto-discovery).
     */
    protected $listen = [
        OrderStatusChangedEvent::class => [
            UpdateEventStatsListener::class,
            SendOrderDetailsEmailListener::class,
        ],
    ];

    /**
     * Map of listeners to the domain events they should handle.
     */
    private static array $domainEventMap = [
        WebhookEventListener::class => [
            ProductEvent::class,
            OrderEvent::class,
            AttendeeEvent::class,
            CheckinEvent::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
        $this->registerDomainEventListeners();
    }

    private function registerDomainEventListeners(): void
    {
        foreach (self::$domainEventMap as $listener => $events) {
            foreach ($events as $event) {
                Event::listen($event, [$listener, 'handle']);
            }
        }
    }

    public function shouldDiscoverEvents(): bool
    {
        return false; // Use explicit registration only
    }
}
