<?php

namespace Ingenius\Core\Services;

use Ingenius\Core\Interfaces\NotificationChannelInterface;
use InvalidArgumentException;

class ChannelRegistryService
{
    /**
     * Registered notification channels
     *
     * Structure: [
     *   'email' => EmailNotificationService::class,
     *   'sms' => SmsNotificationService::class,
     * ]
     */
    protected array $channels = [];

    /**
     * Register a notification channel
     *
     * @param string $identifier Channel identifier (e.g., 'email', 'sms')
     * @param string $channelClass Fully qualified channel class name
     * @return void
     * @throws InvalidArgumentException
     */
    public function register(string $identifier, string $channelClass): void
    {
        if (!class_exists($channelClass)) {
            throw new InvalidArgumentException(
                "Channel class [{$channelClass}] does not exist."
            );
        }

        if (!is_subclass_of($channelClass, NotificationChannelInterface::class)) {
            throw new InvalidArgumentException(
                "Channel [{$channelClass}] must implement NotificationChannelInterface"
            );
        }

        $this->channels[$identifier] = $channelClass;
    }

    /**
     * Get all registered channels
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->channels;
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
     * Get a channel instance
     *
     * @param string $identifier
     * @return NotificationChannelInterface|null
     */
    public function getChannel(string $identifier): ?NotificationChannelInterface
    {
        $channelClass = $this->channels[$identifier] ?? null;

        if (!$channelClass) {
            return null;
        }

        return app($channelClass);
    }

    /**
     * Check if a channel is registered
     *
     * @param string $identifier
     * @return bool
     */
    public function isRegistered(string $identifier): bool
    {
        return isset($this->channels[$identifier]);
    }

    /**
     * Get available channels list with metadata (for API responses)
     *
     * @return array
     */
    public function getAvailableList(): array
    {
        $list = [];

        foreach ($this->channels as $identifier => $channelClass) {
            $instance = app($channelClass);

            $list[] = [
                'identifier' => $identifier,
                'name' => $instance->getName(),
                'is_configured' => $instance->isConfigured(),
                'recipient_label' => $instance->getRecipientLabel(),
            ];
        }

        return $list;
    }

    /**
     * Get only configured channels
     *
     * @return array
     */
    public function getConfiguredChannels(): array
    {
        return array_filter(
            $this->getAvailableList(),
            fn($channel) => $channel['is_configured']
        );
    }

    /**
     * Get total count of registered channels
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->channels);
    }
}
