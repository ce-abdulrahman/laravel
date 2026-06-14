<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achievement_id')
                  ->constrained('achievements')
                  ->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['achievement_id', 'locale'], 'uq_achievement_locale');
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_translations');
    }
};
