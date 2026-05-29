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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('Quran App');
            $table->string('app_logo')->nullable();
            $table->string('default_language')->nullable();
            $table->foreignId('default_tafsir_book_id')->nullable()->constrained('tafsir_books')->nullOnDelete();
            $table->foreignId('default_reciter_id')->nullable()->constrained('reciters')->nullOnDelete();
            $table->foreignId('default_qiraah_id')->nullable()->constrained('qiraats')->nullOnDelete();
            $table->text('about_text')->nullable();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
