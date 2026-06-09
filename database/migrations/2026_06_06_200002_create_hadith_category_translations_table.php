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
        Schema::create('hadith_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hadith_category_id')->constrained('hadith_categories')->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('name', 255);
            $table->timestamps();

            $table->unique(['hadith_category_id', 'locale'], 'uq_hadith_cat_locale');
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hadith_category_translations');
    }
};
