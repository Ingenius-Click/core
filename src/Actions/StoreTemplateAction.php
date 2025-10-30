<?php

namespace Ingenius\Core\Actions;

use Ingenius\Core\Models\Template;
use Ingenius\Core\Traits\HandleImages;

class StoreTemplateAction
{

    use HandleImages;

    public function handle(array $data): Template
    {
        // Decode styles if it's a JSON string and map to styles_vars
        if (isset($data['styles']) && is_string($data['styles'])) {
            $data['styles_vars'] = json_decode($data['styles'], true);
        } elseif (isset($data['styles'])) {
            $data['styles_vars'] = $data['styles'];
        }

        // Create the template with the basic data
        $template = Template::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'identifier' => $data['identifier'],
            'features' => $data['features'],
            'styles_vars' => $data['styles_vars'] ?? null,
            'configurable' => $data['configurable'] ?? false,
            'active' => $data['active'] ?? true,
        ]);

        // Handle images if provided
        if (isset($data['new_images']) && !empty($data['new_images'])) {
            $this->saveImages($data['new_images'], $template);
        }

        return $template->fresh();
    }
}
