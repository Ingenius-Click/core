<?php

namespace Ingenius\Core\Settings;

class CustomizeSettings extends Settings
{

    public string $store_name = 'Tienda X';

    public string $store_logo = '';

    public string $store_favicon = '';

    public static function group(): string
    {
        return 'customize';
    }
}
