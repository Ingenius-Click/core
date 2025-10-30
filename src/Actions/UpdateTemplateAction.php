<?php

namespace Ingenius\Core\Actions;

use Ingenius\Core\Models\Template;
use Ingenius\Core\Traits\HandleImages;

class UpdateTemplateAction
{

    use HandleImages;

    public function handle(Template $template, array $data): Template
    {
        if (isset($data['name'])) {
            $template->name = $data['name'];
        }

        if (isset($data['new_images'])) {
            $this->saveImages($data['new_images'], $template);
        }

        if (isset($data['removed_images'])) {
            $this->removeImages($data['removed_images'], $template);
        }

        if (isset($data['styles'])) {
            // Decode styles if it's a JSON string and store in styles_vars field
            $template->styles_vars = is_string($data['styles'])
                ? json_decode($data['styles'], true)
                : $data['styles'];
        }

        if( isset($data['configurable'])) {
            $template->configurable = $data['configurable'];
        }

        if (isset($data['features'])) {
            $template->features = $data['features'];
        }

        $template->save();

        return $template->fresh();
    }
}
