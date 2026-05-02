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
        Schema::create('ayahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surah_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('ayah_number');
            $table->longText('text_uthmani');
            $table->longText('text_simple')->nullable();
            $table->unsignedSmallInteger('page_number')->nullable();
            $table->unsignedTinyInteger('juz_number')->nullable();
            $table->unsignedTinyInteger('hizb_number')->nullable();
            $table->unsignedTinyInteger('rub_number')->nullable();
            $table->boolean('sajda_flag')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['surah_id', 'ayah_number']);
            $table->index(['surah_id', 'page_number']);
            $table->index(['juz_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayahs');
    }
};
