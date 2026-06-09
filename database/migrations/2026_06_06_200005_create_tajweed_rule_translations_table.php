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
        Schema::create('tajweed_rule_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tajweed_rule_id')->constrained('tajweed_rules')->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tajweed_rule_id', 'locale'], 'uq_tajweed_rule_locale');
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tajweed_rule_translations');
    }
};
