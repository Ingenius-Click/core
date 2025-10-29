<?php

namespace Ingenius\Core\Actions;

use Ingenius\Core\Models\Template;
use Ingenius\Core\Traits\HandleImages;

class UpdateTemplateAction
{

    use HandleImages;

    public function handle(Template $template, array $data): Template
    {
        if (isset($data['images'])) {
            $this->saveImages($data['images'], $template);
        }

        if (isset($data['removed_images'])) {
            $this->removeImages($data['removed_images'], $template);
        }

        if (isset($data['styles_vars'])) {
            $template->styles_vars = $data['styles_vars'];
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
