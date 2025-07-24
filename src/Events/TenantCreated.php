<?php

namespace Ingenius\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ingenius\Core\Models\Tenant;

class TenantCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function getTenant(): Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('tenant.created.' . $this->tenant->id),
        ];
    }
}
