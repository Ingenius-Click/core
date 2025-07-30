<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ingenius\Core\Facades\Settings;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Settings::set('contacts', 'address', '');
        Settings::set('contacts', 'phone', '');
        Settings::set('contacts', 'latitude', '');
        Settings::set('contacts', 'longitude', '');
        Settings::set('contacts', 'location_iframe', '');
        Settings::set('contacts', 'whatsapp', '');
        Settings::set('contacts', 'facebook', '');
        Settings::set('contacts', 'instagram', '');
        Settings::set('contacts', 'twitter', '');
        Settings::set('contacts', 'linkedin', '');
        Settings::set('contacts', 'youtube', '');
        Settings::set('contacts', 'tiktok', '');
        Settings::set('contacts', 'pinterest', '');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Settings::forget('contacts', 'address');
        Settings::forget('contacts', 'phone');
        Settings::forget('contacts', 'latitude');
        Settings::forget('contacts', 'longitude');
        Settings::forget('contacts', 'location_iframe');
        Settings::forget('contacts', 'whatsapp');
        Settings::forget('contacts', 'facebook');
        Settings::forget('contacts', 'instagram');
        Settings::forget('contacts', 'twitter');
        Settings::forget('contacts', 'linkedin');
        Settings::forget('contacts', 'youtube');
        Settings::forget('contacts', 'tiktok');
        Settings::forget('contacts', 'pinterest');
    }
};
