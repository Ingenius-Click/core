<?php

use Illuminate\Database\Migrations\Migration;
use Ingenius\Core\Facades\Settings;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Settings::set('contacts', 'about_us', '');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Settings::forget('contacts', 'about_us');
    }
};


