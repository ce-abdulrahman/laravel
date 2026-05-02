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
        Schema::create('memorization_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memorization_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surah_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('from_ayah_id')->constrained('ayahs')->cascadeOnDelete();
            $table->foreignId('to_ayah_id')->constrained('ayahs')->cascadeOnDelete();
            $table->unsignedInteger('day_number');
            $table->date('target_date')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['memorization_plan_id', 'day_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memorization_plan_items');
    }
};
