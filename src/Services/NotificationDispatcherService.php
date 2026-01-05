<?php

namespace Ingenius\Core\Services;

use Ingenius\Core\Models\NotificationConfiguration;
use Ingenius\Core\Interfaces\RecipientResolverInterface;
use Ingenius\Core\Jobs\SendNotificationJob;
use Ingenius\Core\Support\Recipient;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class NotificationDispatcherService
{
    public function __construct(
        protected EventRegistryService $eventRegistry,
        protected ChannelRegistryService $channelRegistry
    ) {}

    /**
     * Dispatch notifications for an event (alias for handle)
     *
     * @param object $event The event instance
     * @return void
     */
    public function dispatch(object $event): void
    {
        $this->handle($event);
    }

    /**
     * Handle an event and dispatch notifications
     *
     * @param object $event The event instance
     * @return void
     */
    public function handle(object $event): void
    {
        $eventClass = get_class($event);

        // Check if event is registered
        if (!$this->eventRegistry->isRegistered($eventClass)) {
            return; // Silent return for unregistered events
        }

        $eventMetadata = $this->eventRegistry->get($eventClass);

        // Get enabled configurations for this event
        $configurations = NotificationConfiguration::where('event_key', $eventMetadata['key'])
            ->where('is_enabled', true)
            ->get();

        if ($configurations->isEmpty()) {
            return; // No enabled configurations
        }

        // Get recipients from resolver
        $recipients = $this->resolveRecipients($event, $eventMetadata);

        if (empty($recipients)) {
            Log::warning("No recipients resolved for event: {$eventClass}");
            return;
        }

        // Get notification data from resolver (cached to avoid duplicate processing)
        $notificationData = $this->getNotificationData($event, $eventMetadata);

        // Dispatch notifications for each configuration
        foreach ($configurations as $config) {
            // Validate channel is registered
            if (!$this->channelRegistry->isRegistered($config->channel)) {
                Log::warning("Channel [{$config->channel}] not registered, skipping notification for event: {$eventClass}");
                continue;
            }

            $recipientsToNotify = [];

            // Add customer recipients if enabled
            if ($config->notify_customer) {
                foreach ($recipients as $recipient) {
                    if ($recipient->hasChannelRecipient($config->channel)) {
                        $recipientsToNotify[] = $recipient;
                    }
                }
            }

            // Add admin recipients if configured
            if (!empty($config->admin_recipients)) {
                foreach ($config->admin_recipients as $adminRecipientValue) {
                    // Create admin Recipient objects for each configured admin
                    $recipientsToNotify[] = new Recipient(
                        name: null, // Will use default "Administrator"
                        email: $config->channel === 'email' ? $adminRecipientValue : '',
                        isCustomer: false,
                        phone: $config->channel === 'sms' ? $adminRecipientValue : null,
                        data: []
                    );
                }
            }

            // Dispatch job for each recipient
            foreach ($recipientsToNotify as $recipient) {
                $this->dispatchNotification(
                    $event,
                    $config->channel,
                    $recipient,
                    $config,
                    $notificationData
                );
            }
        }
    }

    /**
     * Resolve customer recipients using the event's resolver
     *
     * Admin recipients are handled separately via NotificationConfiguration.
     *
     * @param object $event
     * @param array $eventMetadata
     * @return array Array of customer Recipient objects
     */
    protected function resolveRecipients(object $event, array $eventMetadata): array
    {
        $resolverClass = $eventMetadata['recipient_resolver'] ?? null;

        if (!$resolverClass) {
            Log::warning("No recipient resolver configured for event: " . get_class($event));
            return [];
        }

        try {
            /** @var RecipientResolverInterface $resolver */
            $resolver = app()->make($resolverClass);
            return $resolver->resolve($event);
        } catch (\Exception $e) {
            Log::error("Failed to resolve recipients for event: " . get_class($event), [
                'resolver' => $resolverClass,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get notification data from the event's resolver
     *
     * @param object $event
     * @param array $eventMetadata
     * @return array Notification data for templates
     */
    protected function getNotificationData(object $event, array $eventMetadata): array
    {
        $resolverClass = $eventMetadata['recipient_resolver'] ?? null;

        if (!$resolverClass) {
            return [];
        }

        try {
            /** @var RecipientResolverInterface $resolver */
            $resolver = app()->make($resolverClass);
            return $resolver->getNotificationData($event);
        } catch (\Exception $e) {
            Log::error("Failed to get notification data for event: " . get_class($event), [
                'resolver' => $resolverClass,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Dispatch a notification job
     *
     * @param object $event
     * @param string $channel
     * @param Recipient $recipient
     * @param NotificationConfiguration|null $configuration
     * @param array $notificationData
     * @return void
     */
    protected function dispatchNotification(
        object $event,
        string $channel,
        Recipient $recipient,
        ?NotificationConfiguration $configuration = null,
        array $notificationData = []
    ): void {
        $recipientValue = $recipient->getRecipientForChannel($channel);

        if (!$recipientValue) {
            Log::warning("No recipient value for channel [{$channel}]");
            return;
        }

        // Validate recipient format
        $channelInstance = $this->channelRegistry->getChannel($channel);


        if (!$channelInstance || !$channelInstance->validateRecipient($recipientValue)) {
            Log::warning("Invalid recipient [{$recipientValue}] for channel [{$channel}]");
            return;
        }

        // Dispatch the notification job with tenant context preservation
        SendNotificationJob::dispatch(
            get_class($event),
            $channel,
            $recipient,
            $event,
            $configuration,
            $notificationData
        );
    }

    /**
     * Send a notification synchronously (for testing or immediate delivery)
     *
     * @param object $event
     * @param string $channel
     * @param Recipient $recipient
     * @return array Result from channel send method
     */
    public function sendNow(
        object $event,
        string $channel,
        Recipient $recipient
    ): array {
        $recipientValue = $recipient->getRecipientForChannel($channel);

        if (!$recipientValue) {
            throw new InvalidArgumentException("No recipient value for channel [{$channel}]");
        }

        $channelInstance = $this->channelRegistry->getChannel($channel);

        if (!$channelInstance) {
            throw new InvalidArgumentException("Channel [{$channel}] not registered");
        }

        if (!$channelInstance->validateRecipient($recipientValue)) {
            throw new InvalidArgumentException("Invalid recipient [{$recipientValue}] for channel [{$channel}]");
        }

        return $channelInstance->send($recipientValue, $event, [
            'recipient' => $recipient,
        ]);
    }
}
