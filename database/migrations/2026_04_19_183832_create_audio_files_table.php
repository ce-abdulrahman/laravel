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
        Schema::create('audio_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reciter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surah_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ayah_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_path');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('quality')->nullable();
            $table->string('source_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['reciter_id', 'surah_id']);
            $table->index(['reciter_id', 'ayah_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audio_files');
    }
};
