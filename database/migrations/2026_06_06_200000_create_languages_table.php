<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the languages registry table.
     *
     * This table is the single source of truth for all supported locales.
     * Adding a new language ONLY requires inserting a row here — no migrations needed.
     */
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();       // e.g. 'ar', 'ku', 'en', 'fr'
            $table->string('name', 100);                 // English name: 'Arabic'
            $table->string('native_name', 100)->nullable(); // Native: 'العربية', 'کوردی'
            $table->string('direction', 3)->default('ltr'); // 'ltr' | 'rtl'
            $table->string('flag', 10)->nullable();      // Optional emoji or icon code
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index(['is_active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
