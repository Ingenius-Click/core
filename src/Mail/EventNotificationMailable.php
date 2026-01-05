<?php

namespace Ingenius\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Ingenius\Core\Support\Recipient;
use Ingenius\Core\Services\EventRegistryService;
use Ingenius\Core\Services\NotificationTemplateRenderer;
use Ingenius\Core\Models\NotificationTemplate;
use Ingenius\Core\Models\NotificationConfiguration;

class EventNotificationMailable extends Mailable
{
    use Queueable, SerializesModels;

    protected object $event;
    protected array $context;
    protected ?Recipient $recipient;
    protected array $eventMetadata;
    protected ?NotificationTemplate $template = null;
    protected ?NotificationConfiguration $configuration = null;
    protected array $notificationData = [];
    protected ?array $cachedEventData = null; // Cache to prevent duplicate processing

    /**
     * Create a new message instance.
     *
     * @param object $event
     * @param array $context
     */
    public function __construct(object $event, array $context = [])
    {
        $this->event = $event;
        $this->context = $context;
        $this->recipient = $context['recipient'] ?? null;
        $this->configuration = $context['configuration'] ?? null;
        $this->notificationData = $context['notification_data'] ?? [];

        // Get event metadata from registry
        $eventRegistry = app(EventRegistryService::class);
        $this->eventMetadata = $eventRegistry->get(get_class($event)) ?? [];

        // Load template if configuration is provided
        if ($this->configuration) {
            $this->template = $this->configuration->getTemplateOrDefault();
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Determine view name based on event metadata and recipient
        $viewName = $this->getViewName();

        // Fallback to generic template
        return new Content(
            view: $viewName,
            with: [
                'event' => $this->event,
                'recipient' => $this->recipient,
                'eventMetadata' => $this->eventMetadata,
                'subject' => $this->getSubject(),
                'slots' => [],
            ] + $this->getEventData(),
        );
    }

    /**
     * Build the message (alternative approach for custom HTML).
     * This method is called when the mailable is being built.
     */
    public function build()
    {
        // If we have a custom template, render it directly
        if ($this->template) {
            $renderer = app(NotificationTemplateRenderer::class);
            $eventData = $this->getEventData();
            $eventData['recipient'] = $this->recipient;

            $rendered = $renderer->render($this->template, $eventData, $this->eventMetadata['key'] ?? '');

            return $this->subject($this->getSubject())
                        ->html($rendered['html']);
        }

        // This won't be reached because content() handles the fallback
        return $this;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get email subject
     *
     * @return string
     */
    protected function getSubject(): string
    {
        // If we have a template, render its subject
        if ($this->template) {
            $renderer = app(NotificationTemplateRenderer::class);
            $eventData = $this->getEventData();
            $variables = $renderer->render($this->template, $eventData, $this->eventMetadata['key'] ?? '');

            // Customize subject based on recipient type
            $subject = $variables['subject'];
            if ($this->recipient && !$this->recipient->isCustomer()) {
                // Add [Admin] prefix for admin recipients
                $subject = "[Admin] {$subject}";
            }

            return $subject;
        }

        // Fallback to default subject
        $eventLabel = $this->eventMetadata['label'] ?? 'Notification';
        $appName = config('app.name', 'Application');

        // Customize subject based on recipient type
        if ($this->recipient && !$this->recipient->isCustomer()) {
            return "[Admin] {$eventLabel} - {$appName}";
        }

        return "{$eventLabel} - {$appName}";
    }

    /**
     * Get event data for template (cached to prevent duplicate processing)
     *
     * @return array
     */
    protected function getEventData(): array
    {
        // Return cached data if already processed
        if ($this->cachedEventData !== null) {
            return $this->cachedEventData;
        }

        // Priority 1: Use pre-computed notification data from resolver
        if (!empty($this->notificationData)) {
            $data = $this->notificationData;
        }
        // Priority 2: If event has toArray method, use it
        elseif (method_exists($this->event, 'toArray')) {
            $data = $this->event->toArray();
        }
        // Priority 3: Fallback to public properties
        else {
            $data = get_object_vars($this->event);
        }

        // Always add recipient info if available
        if ($this->recipient) {
            $data['recipient'] = $this->recipient;
            $data['recipient_name'] = $this->recipient->getName();
            $data['recipient_email'] = $this->recipient->getEmail();
        }

        // Cache the result
        $this->cachedEventData = $data;

        return $data;
    }

    /**
     * Get view name for event based on metadata and recipient type
     *
     * @return string
     */
    protected function getViewName(): string
    {
        $viewName = $this->eventMetadata['view_name'] ?? 'general';
        $recipientType = $this->recipient?->isCustomer() ? 'customer' : 'admin';

        return "core::emails.templates.{$recipientType}.{$viewName}";
    }
}
