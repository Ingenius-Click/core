<?php

namespace Ingenius\Core\Settings;

class ContactSettings extends Settings
{
    public string $about_us = '';
    public string $address = '';
    public string $phone = '';
    public string $email = '';
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

    public array $schedule = [];

    public static function group(): string
    {
        return 'contacts';
    }
}
