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
        Settings::set('customize', 'store_name', 'Tienda X');
        Settings::set('customize', 'store_logo', '');
        Settings::set('customize', 'store_favicon', '');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Settings::forget('customize', 'store_name');
        Settings::forget('customize', 'store_logo');
        Settings::forget('customize', 'store_favicon');
    }
};
