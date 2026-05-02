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
        Schema::create('qiraat_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qiraah_id')->constrained('qiraat')->cascadeOnDelete();
            $table->foreignId('ayah_id')->constrained()->cascadeOnDelete();
            $table->longText('text_variant');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['qiraah_id', 'ayah_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qiraat_texts');
    }
};
