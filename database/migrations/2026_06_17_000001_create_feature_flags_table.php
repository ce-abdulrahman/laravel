<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->boolean('is_enabled')->default(true);

            // 0 = disabled for all, 100 = enabled for all, 1-99 = gradual rollout
            $table->unsignedTinyInteger('rollout_percentage')->default(100);

            // 'all', 'android', 'ios'
            $table->string('platform', 20)->default('all');

            // Optional app version constraints (semver strings, nullable)
            $table->string('min_app_version', 20)->nullable();
            $table->string('max_app_version', 20)->nullable();

            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('user_feature_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('flag_key');
            $table->boolean('is_enabled');
            $table->timestamps();

            $table->unique(['user_id', 'flag_key']);
        });

        // Seed default flags — all enabled by default
        DB::table('feature_flags')->insert([
            ['key' => 'memorization_module',   'is_enabled' => true,  'rollout_percentage' => 100, 'description' => 'Full memorization plan, review, and analytics module'],
            ['key' => 'tasbih_leaderboard',    'is_enabled' => true,  'rollout_percentage' => 100, 'description' => 'Tasbih daily/weekly/monthly leaderboard rankings'],
            ['key' => 'audio_download',        'is_enabled' => true,  'rollout_percentage' => 100, 'description' => 'On-demand audio download manager for reciters'],
            ['key' => 'tajweed_module',        'is_enabled' => true,  'rollout_percentage' => 100, 'description' => 'Tajweed rules and learning module'],
            ['key' => 'hadith_module',         'is_enabled' => true,  'rollout_percentage' => 100, 'description' => 'Hadith browsing module'],
            ['key' => 'statistics_module',     'is_enabled' => true,  'rollout_percentage' => 100, 'description' => 'Reading statistics and insights page'],
            ['key' => 'khatm_tracker',         'is_enabled' => true,  'rollout_percentage' => 100, 'description' => 'Khatm completion tracker'],
            ['key' => 'fingerprint_counter',   'is_enabled' => true,  'rollout_percentage' => 100, 'description' => 'Fingerprint-based tasbih counter'],
            ['key' => 'offline_packages',      'is_enabled' => true,  'rollout_percentage' => 100, 'description' => 'Modular offline content package download system'],
            ['key' => 'achievements_module',   'is_enabled' => true,  'rollout_percentage' => 100, 'description' => 'User achievements and badge system'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_feature_overrides');
        Schema::dropIfExists('feature_flags');
    }
};
