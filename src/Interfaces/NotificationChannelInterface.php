<?php

namespace Ingenius\Core\Interfaces;

interface NotificationChannelInterface
{
    /**
     * Get the channel identifier (e.g., 'email', 'sms')
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Get the human-readable channel name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if this channel is properly configured and ready to use
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Get the label for recipient input (e.g., 'Email Address', 'Phone Number')
     *
     * @return string
     */
    public function getRecipientLabel(): string;

    /**
     * Validate if a recipient is in the correct format for this channel
     *
     * @param string $recipient
     * @return bool
     */
    public function validateRecipient(string $recipient): bool;

    /**
     * Send a notification through this channel
     *
     * @param string $recipient The recipient contact (email, phone, etc.)
     * @param object $event The event that triggered the notification
     * @param array $context Additional context data for the notification
     * @return array ['success' => bool, 'message' => string]
     */
    public function send(string $recipient, object $event, array $context = []): array;
}
