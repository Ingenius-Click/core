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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_class')->index()->comment('Event that triggered notification');
            $table->string('channel')->index()->comment('Notification channel used: email, sms, etc.');
            $table->string('recipient')->comment('Email address or phone number');
            $table->string('recipient_name')->nullable()->comment('Name of recipient');
            $table->string('status')->index()->comment('Notification status: pending, sent, failed');
            $table->text('error_message')->nullable()->comment('Error message if failed');
            $table->json('event_data')->nullable()->comment('Snapshot of event data for debugging');
            $table->json('metadata')->nullable()->comment('Additional context');
            $table->timestamp('sent_at')->nullable()->index()->comment('When notification was sent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
