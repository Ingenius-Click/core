<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->index();
            $table->string('event_name');
            $table->string('channel')->index()->comment('Notification channel: email, sms, etc.');
            $table->boolean('is_enabled')->default(true)->comment('Toggle notification on/off');
            $table->boolean('notify_customer')->default(true)->comment('Send notification to customer');
            $table->json('admin_recipients')->nullable()->comment('Array of admin emails or phone numbers');
            $table->string('template_key')->nullable();
            $table->json('metadata')->nullable()->comment('Additional channel-specific configuration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_configurations');
    }
};
