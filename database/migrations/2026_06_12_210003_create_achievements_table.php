<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();

            // Machine-readable unique identifier for code-based references
            $table->string('key')->unique();

            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('achievement_categories')
                  ->nullOnDelete();

            // Visual assets
            $table->string('icon', 50)->default('🏆');
            $table->string('badge_image')->nullable();

            // Condition engine
            $table->string('condition_type', 50); // TOTAL_DHIKR, CURRENT_STREAK, etc.
            $table->unsignedBigInteger('condition_value'); // threshold
            $table->json('condition_meta')->nullable(); // extra config (e.g. time window)

            // Reward architecture (prepared for future gamification)
            $table->string('reward_type', 50)->default('POINTS'); // POINTS, BADGE, TITLE, SPECIAL_THEME
            $table->unsignedInteger('reward_points')->default(0);
            $table->string('reward_value')->nullable(); // extra reward payload

            // Versioning — users who unlocked v1 keep their achievement even if v2 changes thresholds
            $table->unsignedSmallInteger('version')->default(1);

            // Flags
            $table->boolean('is_hidden')->default(false);   // hide name/condition until unlocked
            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('condition_type');
            $table->index(['is_active', 'is_hidden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
