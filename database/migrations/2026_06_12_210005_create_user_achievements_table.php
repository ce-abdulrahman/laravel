<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained('achievements')->cascadeOnDelete();

            // Incremental progress tracking (e.g. 57/100 goals)
            $table->unsignedBigInteger('progress_value')->default(0);

            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable(); // UTC

            // Store the achievement version at unlock time for history integrity
            $table->unsignedSmallInteger('unlocked_version')->default(1);

            $table->timestamps();

            // Prevent duplicate achievement rows per user
            $table->unique(['user_id', 'achievement_id'], 'uq_user_achievement');
            $table->index(['user_id', 'is_completed']);
            $table->index(['achievement_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};
