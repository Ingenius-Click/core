<?php

namespace Ingenius\Core\Listeners;

use Ingenius\Core\Services\EventRegistryService;
use Ingenius\Core\Services\NotificationDispatcherService;
use Illuminate\Support\Facades\Log;

class NotificationEventListener
{
    protected EventRegistryService $eventRegistry;
    protected NotificationDispatcherService $dispatcher;

    /**
     * Create the event listener.
     */
    public function __construct(
        EventRegistryService $eventRegistry,
        NotificationDispatcherService $dispatcher
    ) {
        $this->eventRegistry = $eventRegistry;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handle wildcard events.
     *
     * @param string $eventName
     * @param array $data
     * @return void
     */
    public function handle(string $eventName, array $data): void
    {
        // Get the actual event object from the data array
        $event = $data[0] ?? null;

        if (!is_object($event)) {
            return;
        }

        $eventClass = get_class($event);

        // Check if this event is registered as notifiable
        if (!$this->eventRegistry->isRegistered($eventClass)) {
            return;
        }

        // Get event metadata
        $metadata = $this->eventRegistry->get($eventClass);

        // Skip if event is not marked as notifiable
        if (!($metadata['notifiable'] ?? false)) {
            return;
        }

        try {
            // Dispatch notifications for this event
            $this->dispatcher->dispatch($event);
        } catch (\Exception $e) {
            // Log error but don't break the application flow
            Log::error('Failed to dispatch notification for event', [
                'event' => $eventClass,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
