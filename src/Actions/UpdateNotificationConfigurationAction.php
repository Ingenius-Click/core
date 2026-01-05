<?php

namespace Ingenius\Core\Actions;

use Ingenius\Core\Models\NotificationConfiguration;

class UpdateNotificationConfigurationAction {

    public function handle(NotificationConfiguration $notificationConfiguration, array $data): NotificationConfiguration {

        $notificationConfiguration->update($data);

        return $notificationConfiguration;
    }

}