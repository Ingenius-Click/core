<?php

namespace Ingenius\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationConfigurationResource extends JsonResource {

    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'event_key' => $this->event_key,
            'event_name' => $this->event_name,
            'channel' => $this->channel,
            'is_enabled' => $this->is_enabled,
            'notify_customer' => $this->notify_customer,
            'admin_recipients' => $this->admin_recipients,
            'template_key' => $this->template_key,
            'metadata' => $this->metadata
        ];
    }

}