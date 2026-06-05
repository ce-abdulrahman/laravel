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
        Schema::create('ayah_tajweed_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ayah_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tajweed_rule_id')->constrained()->cascadeOnDelete();
            $table->text('text_segment');
            $table->unsignedInteger('start_index')->nullable();
            $table->unsignedInteger('end_index')->nullable();
            $table->text('note')->nullable();
            $table->timestamps(); 

            $table->index(['ayah_id', 'tajweed_rule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayah_tajweed_segments');
    }
};
