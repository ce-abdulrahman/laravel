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
        Schema::create('user_goal_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('goal_id')->constrained('daily_goal_templates')->cascadeOnDelete();
            $table->integer('current_progress')->default(0);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->boolean('is_completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->date('goal_date');
            $table->enum('period', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->timestamps();

            $table->unique(['user_id', 'goal_id', 'goal_date', 'period'], 'uq_user_goal_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_goal_progress');
    }
};
