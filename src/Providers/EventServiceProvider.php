<?php

namespace Ingenius\Core\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Ingenius\Core\Listeners\NotificationEventListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        // Regular event listeners can be registered here
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Register wildcard listener for all events
        // This will intercept all events and check if they should trigger notifications
        Event::listen('*', function($eventName, $data) {
            app(NotificationEventListener::class)->handle($eventName, $data);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false; // We don't need event discovery, using wildcard listener
    }
}
