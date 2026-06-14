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
        Schema::create('leaderboard_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('score_type', 50); // TOTAL_DHIKR, DAILY_DHIKR, etc.
            $table->integer('score_value')->default(0);
            $table->integer('score_version')->default(1);
            $table->dateTime('calculated_at');
            $table->timestamps();

            $table->unique(['user_id', 'score_type']);
            $table->index(['score_type', 'score_value']);
            $table->index(['user_id', 'score_type', 'calculated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_scores');
    }
};
