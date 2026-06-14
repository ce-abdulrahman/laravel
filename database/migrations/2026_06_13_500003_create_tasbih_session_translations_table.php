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
        Schema::create('tasbih_session_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tasbih_session_id')->constrained('tasbih_sessions')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('field', 50); // e.g. custom_dhikr_name
            $table->text('value');
            $table->timestamps();

            $table->unique(['tasbih_session_id', 'language_id', 'field'], 'session_translation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasbih_session_translations');
    }
};
