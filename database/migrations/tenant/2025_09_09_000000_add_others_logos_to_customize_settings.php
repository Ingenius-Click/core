<?php

use Illuminate\Database\Migrations\Migration;
use Ingenius\Core\Facades\Settings;

return new class extends Migration
{
    public function up(): void
    {
        Settings::set('customize', 'store_black_white_logo', '');
        Settings::set('customize', 'store_footer_logo', '');
        Settings::set('customize', 'store_footer_black_white_logo', '');
    }

    public function down(): void
    {
        Settings::forget('customize', 'store_black_white_logo');
        Settings::forget('customize', 'store_footer_logo');
        Settings::forget('customize', 'store_footer_black_white_logo');
    }
};
