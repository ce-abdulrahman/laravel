<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('nickname', 100)->nullable();
            $table->string('public_title', 100)->nullable();
            $table->text('profile_quote')->nullable();
            $table->json('preferences')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('profile_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profile_id')->constrained('user_profiles')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('field', 50);
            $table->text('value');
            $table->timestamps();

            $table->unique(['user_profile_id', 'language_id', 'field'], 'profile_trans_unique');
        });

        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_identifier')->index();
            $table->string('device_name');
            $table->string('platform');
            $table->string('last_platform_version')->nullable();
            $table->string('push_token')->nullable();
            $table->string('last_ip')->nullable();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('user_login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_identifier')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device')->nullable();
            $table->string('platform')->nullable();
            $table->timestamp('login_at')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login_logs');
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('profile_translations');
        Schema::dropIfExists('user_profiles');
    }
};
