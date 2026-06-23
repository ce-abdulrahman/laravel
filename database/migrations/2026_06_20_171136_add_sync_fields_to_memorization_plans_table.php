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
        Schema::table('memorization_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('memorization_plans', 'plan_id')) {
                $table->string('plan_id')->nullable()->unique();
            }
            if (!Schema::hasColumn('memorization_plans', 'surah_id')) {
                $table->unsignedInteger('surah_id')->nullable();
            }
            if (!Schema::hasColumn('memorization_plans', 'from_ayah')) {
                $table->unsignedInteger('from_ayah')->nullable();
            }
            if (!Schema::hasColumn('memorization_plans', 'to_ayah')) {
                $table->unsignedInteger('to_ayah')->nullable();
            }
            // Also make some columns nullable because simplified plans from mobile sync might not supply them
            $table->string('title')->nullable()->change();
            $table->string('plan_type')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->string('daily_target_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memorization_plans', function (Blueprint $table) {
            $table->dropColumn(['plan_id', 'surah_id', 'from_ayah', 'to_ayah']);
        });
    }
};
