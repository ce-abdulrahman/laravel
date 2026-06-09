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
        Schema::create('adhkar_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adhkar_id')->constrained('adhkars')->onDelete('cascade');
            $table->string('locale', 10);
            $table->text('translation')->nullable();
            $table->timestamps();

            $table->unique(['adhkar_id', 'locale'], 'uq_adhkar_locale');
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adhkar_translations');
    }
};
