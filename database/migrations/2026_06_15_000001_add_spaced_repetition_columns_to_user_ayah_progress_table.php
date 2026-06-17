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
        Schema::table('user_ayah_progress', function (Blueprint $table) {
            $table->date('next_review_date')->nullable()->after('last_reviewed_at');
            $table->unsignedInteger('review_count')->default(0)->after('next_review_date');
            $table->unsignedInteger('current_interval_days')->default(0)->after('review_count');
            $table->string('mastery_level')->default('not_started')->after('current_interval_days');
            $table->string('last_review_result')->nullable()->after('mastery_level');

            $table->index(['user_id', 'next_review_date']);
            $table->index(['user_id', 'mastery_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_ayah_progress', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'next_review_date']);
            $table->dropIndex(['user_id', 'mastery_level']);
            $table->dropColumn([
                'next_review_date',
                'review_count',
                'current_interval_days',
                'mastery_level',
                'last_review_result',
            ]);
        });
    }
};
