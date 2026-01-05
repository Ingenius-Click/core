<?php

namespace Ingenius\Core\Actions;

use Ingenius\Core\Models\NotificationConfiguration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateNotificationConfigurationAction
{
    /**
     * Create a new notification configuration
     *
     * @param array $data Validated data containing:
     *   - event_key: string
     *   - event_name: string
     *   - channel: string
     *   - is_enabled: bool
     *   - notify_customer: bool
     *   - admin_recipients: array|null
     *   - template_key: string|null
     *   - metadata: array|null
     * @return NotificationConfiguration
     */
    public function handle(array $data): NotificationConfiguration
    {
        return DB::transaction(function () use ($data) {
            // Create the notification configuration
            $configuration = NotificationConfiguration::create([
                'event_key' => $data['event_key'],
                'event_name' => $data['event_name'],
                'channel' => $data['channel'],
                'is_enabled' => $data['is_enabled'] ?? true,
                'notify_customer' => $data['notify_customer'] ?? true,
                'admin_recipients' => $data['admin_recipients'] ?? [],
                'template_key' => $data['template_key'] ?? null,
                'metadata' => $data['metadata'] ?? [],
            ]);

            Log::info('Notification configuration created', [
                'id' => $configuration->id,
                'event_key' => $configuration->event_key,
                'event_name' => $configuration->event_name,
                'channel' => $configuration->channel,
                'template_key' => $configuration->template_key,
            ]);

            return $configuration;
        });
    }
}
