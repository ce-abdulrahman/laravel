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
        Schema::create('tafsirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ayah_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tafsir_book_id')->constrained()->cascadeOnDelete();
            $table->longText('content');
            $table->text('short_content')->nullable();
            $table->string('source_reference')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['ayah_id', 'tafsir_book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tafsirs');
    }
};
