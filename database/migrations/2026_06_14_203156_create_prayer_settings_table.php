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
        Schema::create('prayer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('calculation_method')->default('muslim_world_league');
            $table->boolean('global_notifications_enabled')->default(true);
            $table->json('adhan_settings')->nullable();
            $table->longText('cached_prayer_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_settings');
    }
};
