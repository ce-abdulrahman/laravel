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
        // 1. Theme Categories
        Schema::create('theme_categories', function (Blueprint $table) {
            $table->id();
            $table->string('icon', 50);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Themes
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('theme_key', 100)->unique();
            $table->foreignId('category_id')->constrained('theme_categories')->cascadeOnDelete();
            $table->string('preview_image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('unlock_type', 30)->default('free'); // free, points, achievement, streak, event, premium
            $table->string('unlock_value')->nullable(); // points threshold, achievement key, streak days
            $table->string('min_app_version', 20)->nullable();
            $table->string('max_app_version', 20)->nullable();
            $table->json('theme_metadata')->nullable(); // Enforces the schema_version JSON configuration
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Theme Assets
        Schema::create('theme_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained('themes')->cascadeOnDelete();
            $table->string('asset_type', 30); // background, sound, animation, font, particle
            $table->string('file_path');
            $table->unsignedInteger('file_size'); // size in bytes
            $table->string('checksum', 64); // SHA256 checksum
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        // 4. Theme Translations
        Schema::create('theme_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained('themes')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('field', 50); // name, description
            $table->text('value');
            $table->timestamps();

            $table->unique(['theme_id', 'language_id', 'field']);
        });

        // 5. Theme Category Translations
        Schema::create('theme_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_category_id')->constrained('theme_categories')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('field', 50); // name
            $table->text('value');
            $table->timestamps();

            $table->unique(['theme_category_id', 'language_id', 'field']);
        });

        // 6. User Themes (Track active / favorite status)
        Schema::create('user_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('theme_id')->constrained('themes')->cascadeOnDelete();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'theme_id']);
        });

        // 7. User Theme Preferences (Theme customization overrides)
        Schema::create('user_theme_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('theme_id')->constrained('themes')->cascadeOnDelete();
            $table->boolean('sound_enabled')->default(true);
            $table->boolean('haptic_enabled')->default(true);
            $table->boolean('animation_enabled')->default(true);
            $table->string('custom_ring_color', 7)->nullable(); // HEX format: #RRGGBB
            $table->double('custom_font_scale')->default(1.0);
            $table->timestamps();

            $table->unique(['user_id', 'theme_id']);
        });

        // 8. Theme Downloads
        Schema::create('theme_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('theme_id')->constrained('themes')->cascadeOnDelete();
            $table->timestamp('downloaded_at')->useCurrent();
            $table->unsignedInteger('version');
            $table->timestamps();
        });

        // 9. Theme Usage Logs
        Schema::create('theme_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('theme_id')->constrained('themes')->cascadeOnDelete();
            $table->string('event_type', 30); // apply, preview, download, favorite, uninstall
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_usage_logs');
        Schema::dropIfExists('theme_downloads');
        Schema::dropIfExists('user_theme_preferences');
        Schema::dropIfExists('user_themes');
        Schema::dropIfExists('theme_category_translations');
        Schema::dropIfExists('theme_translations');
        Schema::dropIfExists('theme_assets');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('theme_categories');
    }
};
