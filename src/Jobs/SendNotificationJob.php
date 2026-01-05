<?php

namespace Ingenius\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Ingenius\Core\Services\ChannelRegistryService;
use Ingenius\Core\Models\NotificationLog;
use Ingenius\Core\Models\NotificationConfiguration;
use Ingenius\Core\Support\Recipient;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $eventClass,
        protected string $channel,
        protected Recipient $recipient,
        protected object $event,
        protected ?NotificationConfiguration $configuration = null,
        protected array $notificationData = []
    ) {
        // Queue configuration based on priority
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(ChannelRegistryService $channelRegistry): void
    {
        $recipientValue = $this->recipient->getRecipientForChannel($this->channel);

        if (!$recipientValue) {
            Log::warning("No recipient value for channel [{$this->channel}]", [
                'event' => $this->eventClass,
                'recipient' => $this->recipient->getName() ?? 'Unknown',
            ]);
            return;
        }

        // Get channel instance
        $channelInstance = $channelRegistry->getChannel($this->channel);

        if (!$channelInstance) {
            Log::error("Channel [{$this->channel}] not registered", [
                'event' => $this->eventClass,
            ]);
            $this->fail(new \Exception("Channel [{$this->channel}] not registered"));
            return;
        }

        // Create notification log entry
        $log = $this->createLog('pending');

        try {
            // Send notification with configuration context and notification data
            $result = $channelInstance->send($recipientValue, $this->event, [
                'recipient' => $this->recipient,
                'configuration' => $this->configuration,
                'notification_data' => $this->notificationData,
            ]);

            // Update log with success and include metadata from result
            $this->updateLog($log, 'sent', $result);

        } catch (\Exception $e) {
            // Update log with failure
            $this->updateLog($log, 'failed', null, $e->getMessage());

            Log::error("Failed to send notification", [
                'event' => $this->eventClass,
                'channel' => $this->channel,
                'recipient' => $recipientValue,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger job retry
            throw $e;
        }
    }

    /**
     * Create a notification log entry
     *
     * @param string $status
     * @return NotificationLog
     */
    protected function createLog(string $status): NotificationLog
    {
        return NotificationLog::create([
            'event_class' => $this->eventClass,
            'channel' => $this->channel,
            'recipient' => $this->recipient->getRecipientForChannel($this->channel),
            'recipient_name' => $this->recipient->getName(),
            'status' => $status,
            'metadata' => [
                'is_customer' => $this->recipient->isCustomer(),
                'configuration_id' => $this->configuration?->id,
                'template_key' => $this->configuration?->template_key,
                'attempt' => $this->attempts(),
            ],
        ]);
    }

    /**
     * Update notification log entry
     *
     * @param NotificationLog $log
     * @param string $status
     * @param array|null $response
     * @param string|null $error
     * @return void
     */
    protected function updateLog(
        NotificationLog $log,
        string $status,
        ?array $response = null,
        ?string $error = null
    ): void {
        $metadata = $log->metadata ?? [];
        $metadata['attempt'] = $this->attempts();

        if ($response) {
            $metadata['response'] = $response;

            // Extract tenant and mail config from response metadata if available
            if (isset($response['metadata'])) {
                $metadata = [...$metadata, ...$response['metadata']];
            }
        }

        $log->update([
            'status' => $status,
            'error_message' => $error,
            'sent_at' => $status === 'sent' ? now() : null,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendNotificationJob failed permanently", [
            'event' => $this->eventClass,
            'channel' => $this->channel,
            'recipient' => $this->recipient->getRecipientForChannel($this->channel),
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Try to find and update the log entry
        $log = NotificationLog::where('event_class', $this->eventClass)
            ->where('channel', $this->channel)
            ->where('recipient', $this->recipient->getRecipientForChannel($this->channel))
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($log) {
            $this->updateLog($log, 'failed', null, $exception->getMessage());
        }
    }
}
