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
        Schema::create('translation_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('ui_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_key_id')->constrained('translation_keys')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->boolean('is_auto_generated')->default(false);
            $table->timestamps();

            $table->unique(['translation_key_id', 'language_id'], 'ui_trans_key_lang_unique');
            $table->index('translation_key_id');
            $table->index('language_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ui_translations');
        Schema::dropIfExists('translation_keys');
    }
};
