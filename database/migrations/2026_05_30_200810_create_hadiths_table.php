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
        Schema::create('hadiths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('hadith_categories')->onDelete('cascade');
            $table->text('arabic_text');
            $table->text('translation_ku');
            $table->text('translation_en')->nullable();
            $table->string('narrator')->nullable();
            $table->string('source')->nullable();
            $table->text('explanation_ku')->nullable();
            $table->text('explanation_en')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hadiths');
    }
};
