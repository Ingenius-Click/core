<?php

namespace Ingenius\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'template' => $this->template?->name,
            'domain' => $this->domains->first()?->domain,
        ];
    }
}