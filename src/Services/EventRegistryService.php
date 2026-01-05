<?php

namespace Ingenius\Core\Services;

use Ingenius\Core\Interfaces\RecipientResolverInterface;
use InvalidArgumentException;

class EventRegistryService
{
    /**
     * Registered notifiable events (indexed by event class)
     *
     * Structure: [
     *   'Ingenius\Orders\Events\OrderCreatedEvent' => [
     *     'key' => 'order.created',
     *     'label' => 'Order Created',
     *     'description' => 'Fired when a new order is created',
     *     'package' => 'orders',
     *     'view_name' => 'order-created',
     *     'default_channels' => ['email'],
     *     'recipient_resolver' => 'Ingenius\Orders\Notifications\Resolvers\OrderCreatedRecipientResolver',
     *     'notifiable' => true,
     *   ],
     * ]
     */
    protected array $events = [];

    /**
     * Event key to class mapping (for quick lookups)
     *
     * Structure: [
     *   'order.created' => 'Ingenius\Orders\Events\OrderCreatedEvent',
     * ]
     */
    protected array $keyToClassMap = [];

    /**
     * Register a notifiable event
     *
     * @param string $eventClass Fully qualified event class name
     * @param string $key Unique event key (e.g., 'order.created')
     * @param string $label Human-readable event name
     * @param string $viewName View name for email templates (e.g., 'order-created')
     * @param string|null $recipientResolver Fully qualified resolver class name
     * @param bool $notifiable Whether this event can trigger notifications
     * @param string|null $description Event description
     * @param string|null $package Package that owns this event
     * @param array $defaultChannels Available channels for this event
     * @return void
     * @throws InvalidArgumentException
     */
    public function register(
        string $eventClass,
        string $key,
        string $label,
        string $viewName,
        ?string $recipientResolver = null,
        bool $notifiable = true,
        ?string $description = null,
        ?string $package = null,
        array $defaultChannels = ['email']
    ): void {
        // Validate event class exists
        if (!class_exists($eventClass)) {
            throw new InvalidArgumentException(
                "Event class [{$eventClass}] does not exist."
            );
        }

        // Validate key is unique
        if (isset($this->keyToClassMap[$key])) {
            throw new InvalidArgumentException(
                "Event key [{$key}] is already registered for class [{$this->keyToClassMap[$key]}]."
            );
        }

        // Validate recipient resolver if provided
        if ($recipientResolver !== null) {
            if (!class_exists($recipientResolver)) {
                throw new InvalidArgumentException(
                    "Recipient resolver class [{$recipientResolver}] does not exist."
                );
            }

            if (!is_subclass_of($recipientResolver, RecipientResolverInterface::class)) {
                throw new InvalidArgumentException(
                    "Recipient resolver [{$recipientResolver}] must implement RecipientResolverInterface."
                );
            }
        }

        $this->events[$eventClass] = [
            'key' => $key,
            'label' => $label,
            'description' => $description,
            'package' => $package,
            'view_name' => $viewName,
            'default_channels' => $defaultChannels,
            'recipient_resolver' => $recipientResolver,
            'notifiable' => $notifiable,
        ];

        // Store reverse mapping
        $this->keyToClassMap[$key] = $eventClass;
    }

    /**
     * Get all registered events
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->events;
    }

    /**
     * Alias for getAll()
     *
     * @return array
     */
    public function all(): array
    {
        return $this->getAll();
    }

    /**
     * Get event class by key
     *
     * @param string $key
     * @return string|null
     */
    public function getClassByKey(string $key): ?string
    {
        return $this->keyToClassMap[$key] ?? null;
    }

    /**
     * Get event metadata by key
     *
     * @param string $key
     * @return array|null
     */
    public function getByKey(string $key): ?array
    {
        $eventClass = $this->getClassByKey($key);

        if (!$eventClass) {
            return null;
        }

        return array_merge(
            ['event_class' => $eventClass],
            $this->events[$eventClass] ?? []
        );
    }

    /**
     * Check if an event key is registered
     *
     * @param string $key
     * @return bool
     */
    public function hasKey(string $key): bool
    {
        return isset($this->keyToClassMap[$key]);
    }

    /**
     * Get all registered events grouped by package
     *
     * @return array
     */
    public function getAllGrouped(): array
    {
        $grouped = [];

        foreach ($this->events as $eventClass => $config) {
            $package = $config['package'];

            if (!isset($grouped[$package])) {
                $grouped[$package] = [];
            }

            $grouped[$package][] = array_merge(
                ['event_class' => $eventClass],
                $config
            );
        }

        return $grouped;
    }

    /**
     * Check if an event is registered by its event class
     *
     * @param string $eventClass
     * @return bool
     */
    public function isRegistered(string $eventClass): bool
    {
        return isset($this->events[$eventClass]);
    }

    /**
     * Check if an event is registered by its event key
     *
     * @param string $eventKey
     * @return bool
     */
    public function isRegisteredByKey(string $eventKey): bool
    {
        return isset($this->keyToClassMap[$eventKey]);
    }

    /**
     * Get event metadata by event class
     *
     * @param string $eventClass
     * @return array|null
     */
    public function get(string $eventClass): ?array
    {
        return $this->events[$eventClass] ?? null;
    }

    /**
     * Get events by package
     *
     * @param string $package
     * @return array
     */
    public function getByPackage(string $package): array
    {
        return array_filter(
            $this->events,
            fn($config) => $config['package'] === $package
        );
    }

    /**
     * Get total count of registered events
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->events);
    }
}
