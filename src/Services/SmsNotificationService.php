<?php

namespace Ingenius\Core\Services;

use Ingenius\Core\Interfaces\NotificationChannelInterface;
use Ingenius\Core\Models\NotificationLog;
use Ingenius\Core\Enums\NotificationStatus;
use Illuminate\Support\Facades\Log;

class SmsNotificationService implements NotificationChannelInterface
{
    /**
     * Get channel identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'sms';
    }

    /**
     * Get human-readable channel name
     *
     * @return string
     */
    public function getName(): string
    {
        return __('SMS');
    }

    /**
     * Check if channel is configured and ready to use
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        // SMS is not configured by default
        // This would check for SMS provider credentials (Twilio, etc.)
        return false;
    }

    /**
     * Get label for recipient input field
     *
     * @return string
     */
    public function getRecipientLabel(): string
    {
        return __('Phone Number');
    }

    /**
     * Validate recipient format (phone number)
     *
     * @param string $recipient
     * @return bool
     */
    public function validateRecipient(string $recipient): bool
    {
        // Basic phone number validation
        // Accepts formats: +1234567890, 1234567890, (123) 456-7890, etc.
        $pattern = '/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/';
        return preg_match($pattern, $recipient) === 1;
    }

    /**
     * Send notification via SMS
     *
     * @param string $recipient Phone number
     * @param object $event Event instance
     * @param array $context Additional context (recipient object, etc.)
     * @return array Result with success status and message
     */
    public function send(string $recipient, object $event, array $context = []): array
    {
        $eventClass = get_class($event);

        // SMS sending is not implemented yet - this is a stub
        // In the future, integrate with Twilio, Nexmo, AWS SNS, etc.

        Log::info("SMS notification stub called", [
            'event' => $eventClass,
            'recipient' => $recipient,
            'message' => 'SMS sending is not yet implemented',
        ]);

        // Log as failed since it's not implemented
        $this->logNotification(
            eventClass: $eventClass,
            recipient: $recipient,
            status: NotificationStatus::FAILED,
            event: $event,
            context: $context,
            errorMessage: 'SMS channel is not yet implemented'
        );

        return [
            'success' => false,
            'message' => 'SMS channel is not yet implemented',
        ];

        // TODO: Implement SMS sending
        // Example implementation:
        // try {
        //     $smsClient = app(SmsClientInterface::class);
        //     $message = $this->formatSmsMessage($event, $context);
        //
        //     $smsClient->send($recipient, $message);
        //
        //     $this->logNotification(
        //         eventClass: $eventClass,
        //         recipient: $recipient,
        //         status: NotificationStatus::SENT,
        //         event: $event,
        //         context: $context
        //     );
        //
        //     return [
        //         'success' => true,
        //         'message' => 'SMS sent successfully',
        //     ];
        // } catch (\Exception $e) {
        //     $this->logNotification(
        //         eventClass: $eventClass,
        //         recipient: $recipient,
        //         status: NotificationStatus::FAILED,
        //         event: $event,
        //         context: $context,
        //         errorMessage: $e->getMessage()
        //     );
        //
        //     Log::error("Failed to send SMS notification", [
        //         'event' => $eventClass,
        //         'recipient' => $recipient,
        //         'error' => $e->getMessage(),
        //     ]);
        //
        //     return [
        //         'success' => false,
        //         'message' => $e->getMessage(),
        //     ];
        // }
    }

    /**
     * Log notification attempt
     *
     * @param string $eventClass
     * @param string $recipient
     * @param NotificationStatus $status
     * @param object $event
     * @param array $context
     * @param string|null $errorMessage
     * @return void
     */
    protected function logNotification(
        string $eventClass,
        string $recipient,
        NotificationStatus $status,
        object $event,
        array $context = [],
        ?string $errorMessage = null
    ): void {
        NotificationLog::create([
            'event_class' => $eventClass,
            'channel' => $this->getIdentifier(),
            'recipient' => $recipient,
            'status' => $status->value,
            'error_message' => $errorMessage,
            'event_data' => $this->serializeEvent($event),
            'metadata' => [
                'recipient_type' => $context['recipient']?->getType()?->value ?? null,
                'recipient_name' => $context['recipient']?->getName() ?? null,
            ],
            'sent_at' => $status === NotificationStatus::SENT ? now() : null,
        ]);
    }

    /**
     * Serialize event data for logging
     *
     * @param object $event
     * @return array
     */
    protected function serializeEvent(object $event): array
    {
        try {
            // Try to convert event to array
            if (method_exists($event, 'toArray')) {
                return $event->toArray();
            }

            // Fallback to public properties
            return get_object_vars($event);
        } catch (\Exception $e) {
            return [
                'class' => get_class($event),
                'serialization_error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format SMS message from event data
     *
     * @param object $event
     * @param array $context
     * @return string
     */
    protected function formatSmsMessage(object $event, array $context): string
    {
        // TODO: Implement SMS message formatting
        // This could use templates or event-specific formatting logic
        return "You have a new notification from " . config('app.name');
    }
}
