<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_template_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reminder_template_id')
                  ->constrained('reminder_templates')
                  ->cascadeOnDelete();
            $table->foreignId('language_id')
                  ->constrained('languages')
                  ->cascadeOnDelete();

            // Translatable fields: title | body
            $table->string('field', 50);
            $table->text('value');

            $table->timestamps();

            $table->unique(['reminder_template_id', 'language_id', 'field'], 'rtt_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_template_translations');
    }
};
