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
        // 1. Fingerprint Settings Table
        Schema::create('fingerprint_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('count_mode', 30)->default('single_touch'); // single_touch, hold_to_count, continuous
            $table->integer('hold_interval_seconds')->default(1); // 1, 2, 3 seconds
            $table->string('haptic_profile', 30)->default('normal'); // light, normal, strong, custom, disabled
            $table->integer('custom_haptic_vibration_ms')->default(50);
            $table->string('audio_profile', 30)->default('soft_click'); // soft_click, tasbih_bead, water_drop, silent
            $table->boolean('blind_mode')->default(false);
            $table->boolean('focus_mode')->default(false);
            $table->timestamps();
        });

        // 2. Fingerprint Statistics Table
        Schema::create('fingerprint_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('total_counts')->default(0);
            $table->unsignedInteger('total_sessions')->default(0);
            $table->unsignedInteger('total_blind_sessions')->default(0);
            $table->unsignedInteger('total_focus_sessions')->default(0);
            $table->decimal('avg_touch_rate', 8, 2)->default(0.00); // touch rate per minute
            $table->string('favorite_mode', 30)->default('single_touch');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        // 3. Fingerprint Session Logs Table
        Schema::create('fingerprint_session_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->unique()->constrained('tasbih_sessions')->cascadeOnDelete();
            $table->unsignedInteger('touch_count')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->decimal('touch_rate', 8, 2)->default(0.00); // average touches per minute
            $table->boolean('is_blind')->default(false);
            $table->boolean('is_focus')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fingerprint_session_logs');
        Schema::dropIfExists('fingerprint_statistics');
        Schema::dropIfExists('fingerprint_settings');
    }
};
