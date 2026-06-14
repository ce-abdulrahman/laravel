<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Type matches ReminderTemplate::TYPE_* constants (VARCHAR, not ENUM)
            $table->string('reminder_type', 50);

            $table->boolean('enabled')->default(true);

            // HH:MM format, stored in user's local timezone
            $table->time('scheduled_time')->nullable();

            // daily | weekdays | weekends | custom — VARCHAR for extensibility
            $table->string('frequency', 50)->default('daily');

            // JSON array of day numbers [1=Mon .. 7=Sun] for custom frequency
            $table->json('custom_days')->nullable();

            // User's IANA timezone string e.g. Asia/Baghdad
            $table->string('timezone', 64)->default('Asia/Baghdad');

            $table->timestamp('last_sent_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // One row per user per reminder type
            $table->unique(['user_id', 'reminder_type'], 'ur_user_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reminders');
    }
};
