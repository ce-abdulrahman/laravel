<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_templates', function (Blueprint $table) {
            $table->id();

            // Unique machine key (e.g. morning_reminder, streak_warning)
            $table->string('key', 100)->unique();

            // Reminder type: MORNING | AFTERNOON | EVENING | BEFORE_SLEEP |
            //                DAILY_GOAL | STREAK | ACHIEVEMENT | INACTIVITY | CUSTOM
            $table->string('type', 50)->index();

            // Display icon (emoji or icon key)
            $table->string('icon', 20)->default('🔔');

            // Display priority (used for ordering in UI)
            $table->unsignedTinyInteger('priority')->default(5);

            // Admin-controlled sort order
            $table->unsignedSmallInteger('sort_order')->default(0);

            // Template version — increments when content changes, triggers Flutter resync
            $table->unsignedSmallInteger('version')->default(1);

            // Is this template available for scheduling?
            $table->boolean('is_active')->default(true)->index();

            // Extensible future metadata: color, sound, channel, etc.
            $table->json('metadata')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_templates');
    }
};
