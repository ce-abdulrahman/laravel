<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->timestamps();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('country_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('field', 50);
            $table->text('value');
            $table->timestamps();

            $table->unique(['country_id', 'language_id', 'field'], 'country_trans_unique');
        });

        Schema::create('province_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('field', 50);
            $table->text('value');
            $table->timestamps();

            $table->unique(['province_id', 'language_id', 'field'], 'province_trans_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('province_translations');
        Schema::dropIfExists('country_translations');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('countries');
    }
};
