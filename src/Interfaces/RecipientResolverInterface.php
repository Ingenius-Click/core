<?php

namespace Ingenius\Core\Interfaces;

interface RecipientResolverInterface
{
    /**
     * Resolve customer recipients for the event
     *
     * This should return an array of Recipient objects representing customers
     * who should receive notifications about this event.
     *
     * Admin recipients are managed separately via NotificationConfiguration.
     *
     * @param object $event The event instance
     * @return array<\Ingenius\Core\Support\Recipient> Array of customer Recipient objects
     */
    public function resolve($event): array;

    /**
     * Transform event into notification-ready data for templates
     *
     * This method extracts relevant data from the event and formats it
     * for use in email templates, SMS, etc.
     *
     * @param object $event The event instance
     * @return array Associative array of data for template rendering
     */
    public function getNotificationData($event): array;
}