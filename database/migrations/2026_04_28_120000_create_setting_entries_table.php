<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Key/value settings for the mobile API (GET /api/settings).
     * Keeps the legacy `settings` row intact for admin / web app.
     */
    public function up(): void
    {
        Schema::create('setting_entries', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_entries');
    }
};
