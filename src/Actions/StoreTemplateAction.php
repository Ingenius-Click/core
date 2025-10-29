<?php

namespace Ingenius\Core\Actions;

use Ingenius\Core\Models\Template;
use Ingenius\Core\Traits\HandleImages;

class StoreTemplateAction
{

    use HandleImages;

    public function handle(array $data): Template
    {
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
        if (isset($data['images']) && !empty($data['images'])) {
            $this->saveImages($data['images'], $template);
        }

        return $template->fresh();
    }
}
