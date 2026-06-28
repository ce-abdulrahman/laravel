<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audio_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('reciter_id');
            $table->unsignedBigInteger('surah_id');
            $table->string('status');
            $table->decimal('progress', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['user_id', 'reciter_id', 'surah_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audio_downloads');
    }
};
