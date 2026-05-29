<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('points_total')->default(0)->after('status');
            $table->unsignedInteger('streak_days')->default(0)->after('points_total');
            $table->unsignedInteger('longest_streak')->default(0)->after('streak_days');
            $table->date('last_read_date')->nullable()->after('longest_streak');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['points_total', 'streak_days', 'longest_streak', 'last_read_date']);
        });
    }
};
