<?php

namespace Ingenius\Core\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Ingenius\Core\Services\FeatureManager;

class TemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'identifier' => $this->identifier,
            'styles' => $this->styles_vars,
            'features' => $this->getFeatures(),
        ];
    }
}
