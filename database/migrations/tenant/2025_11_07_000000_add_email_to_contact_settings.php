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
        Settings::set('contacts', 'email', '');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Settings::forget('contacts', 'email');
    }
};