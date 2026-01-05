<?php

namespace Ingenius\Core\Actions;

use Ingenius\Core\Models\NotificationConfiguration;

class CreateOrUpdateNotificationConfigurationAction {

    public function handle(array $data) {
        $configuration = NotificationConfiguration::where('event_key', $data['event_key'])
            ->where('channel', $data['channel'])
            ->first();

        if($configuration) {
            // Update existing configuration
            $action = new UpdateNotificationConfigurationAction();
            return $action->handle($configuration, $data);
        }

        // Create new configuration
        $action = new CreateNotificationConfigurationAction();
        return $action->handle($data);
    }

}