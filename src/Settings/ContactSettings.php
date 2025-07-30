<?php

namespace Ingenius\Core\Settings;

class ContactSettings extends Settings
{
    public string $address = '';

    public string $phone = '';

    public string $latitude = '';
    public string $longitude = '';

    public string $location_iframe = '';

    public string $whatsapp = '';
    public string $facebook = '';
    public string $instagram = '';
    public string $twitter = '';
    public string $linkedin = '';
    public string $youtube = '';
    public string $tiktok = '';
    public string $pinterest = '';

    public static function group(): string
    {
        return 'contacts';
    }
}
