<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achievement_category_id')
                  ->constrained('achievement_categories')
                  ->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->timestamps();

            $table->unique(['achievement_category_id', 'locale'], 'uq_ach_cat_locale');
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_category_translations');
    }
};
