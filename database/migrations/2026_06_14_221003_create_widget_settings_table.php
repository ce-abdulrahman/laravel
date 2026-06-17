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
        Schema::create('widget_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('widget_enabled')->default(true);
            $table->string('widget_visibility')->default('always_visible'); // always_visible, only_authenticated
            $table->integer('widget_refresh_interval')->default(300); // in seconds
            $table->foreignId('widget_default_city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->integer('widget_display_order')->default(1);
            $table->string('hijri_source')->default('tabular'); // tabular, umm_al_qura, custom
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_settings');
    }
};
