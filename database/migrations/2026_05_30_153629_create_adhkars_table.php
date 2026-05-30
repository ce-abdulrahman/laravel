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
        Schema::create('adhkars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('adhkar_categories')->onDelete('cascade');
            $table->text('arabic_text');
            $table->text('translation_ku')->nullable();
            $table->text('translation_en')->nullable();
            $table->integer('count')->default(1);
            $table->string('source')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adhkars');
    }
};
