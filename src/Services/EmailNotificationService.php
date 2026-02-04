<?php

namespace Ingenius\Core\Services;

use Ingenius\Core\Interfaces\NotificationChannelInterface;
use Ingenius\Core\Mail\EventNotificationMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationService implements NotificationChannelInterface
{
    /**
     * Get channel identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'email';
    }

    /**
     * Get human-readable channel name
     *
     * @return string
     */
    public function getName(): string
    {
        return __('Email');
    }

    /**
     * Check if channel is configured and ready to use
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        // Check if mail configuration is set
        return config('mail.default') !== null
            && config('mail.from.address') !== null;
    }

    /**
     * Get label for recipient input field
     *
     * @return string
     */
    public function getRecipientLabel(): string
    {
        return __('Email Address');
    }

    /**
     * Validate recipient format
     *
     * @param string $recipient
     * @return bool
     */
    public function validateRecipient(string $recipient): bool
    {
        return filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Send notification via email
     *
     * @param string $recipient Email address
     * @param object $event Event instance
     * @param array $context Additional context (recipient object, etc.)
     * @return array Result with success status and message
     */
    public function send(string $recipient, object $event, array $context = []): array
    {
        $eventClass = get_class($event);

        // Capture tenant and mail configuration data for logging
        $tenantIdentifier = tenant()?->getTenantKey();
        $defaultMailer = config('mail.default');
        $mailerConfig = config("mail.mailers.{$defaultMailer}");

        $mailConfigData = [
            'default_mailer' => $defaultMailer,
            'host' => $mailerConfig['host'] ?? null,
            'port' => $mailerConfig['port'] ?? null,
            'username' => $mailerConfig['username'] ?? null,
            'encryption' => $mailerConfig['encryption'] ?? null,
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];

        try {
            // Create mailable instance
            $mailable = new EventNotificationMailable($event, $context);

            Log::info('Sending email notification');
            Log::info(config('app.name'));

            // Send email synchronously (not queued, since the job already handles queuing)
            Mail::to($recipient)->send($mailable);

            Log::info("Email sent successfully", [
                'event' => $eventClass,
                'recipient' => $recipient,
                'tenant' => $tenantIdentifier,
                'mail_config' => $mailConfigData,
            ]);

            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'metadata' => [
                    'tenant_identifier' => $tenantIdentifier,
                    'mail_config' => $mailConfigData,
                ],
            ];
        } catch (\Exception $e) {
            Log::error("Failed to send email notification", [
                'event' => $eventClass,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'tenant' => $tenantIdentifier,
                'mail_config' => $mailConfigData,
            ]);

            throw $e; // Re-throw to let the job handle retry logic
        }
    }

}
