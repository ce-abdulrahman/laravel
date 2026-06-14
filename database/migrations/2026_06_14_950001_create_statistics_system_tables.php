<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Cached aggregates per user ─────────────────────────────────────
        Schema::create('user_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Dhikr
            $table->unsignedBigInteger('total_dhikr')->default(0);
            $table->unsignedBigInteger('total_sessions')->default(0);

            // Streaks
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('longest_streak')->default(0);
            $table->unsignedInteger('total_streak_days')->default(0);

            // Goals
            $table->unsignedInteger('total_goals_completed')->default(0);
            $table->unsignedInteger('total_goals_missed')->default(0);
            $table->decimal('goal_completion_rate', 5, 2)->default(0);

            // Achievements
            $table->unsignedInteger('total_achievements')->default(0);
            $table->unsignedInteger('rare_achievements')->default(0);

            // Fingerprint
            $table->unsignedBigInteger('fingerprint_total_counts')->default(0);
            $table->unsignedInteger('fingerprint_total_sessions')->default(0);

            // Leaderboard
            $table->unsignedInteger('current_rank')->nullable();
            $table->unsignedInteger('highest_rank')->nullable();

            // Reminders
            $table->unsignedInteger('reminders_sent')->default(0);
            $table->unsignedInteger('reminders_opened')->default(0);

            // Derived
            $table->unsignedTinyInteger('productivity_score')->default(0);   // 0-100
            $table->string('productivity_label', 20)->default('beginner');    // beginner/active/dedicated/advanced/master

            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
        });

        // ── 2. Historical snapshots (daily/weekly/monthly) ────────────────────
        Schema::create('statistics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->enum('snapshot_type', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->json('data_json');   // full stats at that point in time
            $table->timestamps();

            $table->unique(['user_id', 'snapshot_date', 'snapshot_type']);
            $table->index(['user_id', 'snapshot_type', 'snapshot_date']);
        });

        // ── 3. Generated insights per user ────────────────────────────────────
        Schema::create('insight_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('insight_type', 60);   // peak_time / trend / milestone / comparison
            $table->json('insight_data');          // { key, params, locale_fallback }
            $table->string('icon', 10)->nullable();
            $table->timestamp('generated_at');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'generated_at']);
        });

        // ── 4. Admin-configurable productivity score weights ──────────────────
        Schema::create('statistics_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('value', 255);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // Seed default weights
        DB::table('statistics_settings')->insert([
            ['key' => 'streak_weight',      'value' => '0.25', 'description' => 'Weight for streak factor in productivity score (0.0–1.0)',      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'goal_weight',        'value' => '0.30', 'description' => 'Weight for goal completion rate in productivity score',         'created_at' => now(), 'updated_at' => now()],
            ['key' => 'session_weight',     'value' => '0.25', 'description' => 'Weight for session activity factor in productivity score',      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'achievement_weight', 'value' => '0.20', 'description' => 'Weight for achievement factor in productivity score',           'created_at' => now(), 'updated_at' => now()],
            ['key' => 'snapshot_daily_retention_days',   'value' => '90',  'description' => 'Days to keep daily snapshots',   'created_at' => now(), 'updated_at' => now()],
            ['key' => 'snapshot_weekly_retention_days',  'value' => '730', 'description' => 'Days to keep weekly snapshots',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'insights_expire_hours', 'value' => '24', 'description' => 'Hours before insights are regenerated',    'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('statistics_settings');
        Schema::dropIfExists('insight_logs');
        Schema::dropIfExists('statistics_snapshots');
        Schema::dropIfExists('user_statistics');
    }
};
