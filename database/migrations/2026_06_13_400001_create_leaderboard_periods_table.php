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
        Schema::create('leaderboard_periods', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50); // daily, weekly, monthly, all_time, achievement, streak
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('active'); // active, completed
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_periods');
    }
};
