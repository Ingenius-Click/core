<?php

namespace Ingenius\Core\Support;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class TenantAwareUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        if (tenant()) {
            $url = asset($this->getPathRelativeToRoot());

            $url = $this->versionUrl($url);

            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . 'tenant=' . tenant()->domains()->first()->domain;

            return $url;
        }

        return parent::getUrl();
    }
}
