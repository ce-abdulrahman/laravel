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
        Schema::create('surahs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique();
            $table->string('revelation_type', 20);
            $table->unsignedSmallInteger('ayah_count');
            $table->unsignedSmallInteger('page_start')->nullable();
            $table->unsignedSmallInteger('page_end')->nullable();
            $table->unsignedTinyInteger('juz_start')->nullable();
            $table->unsignedTinyInteger('juz_end')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surahs');
    }
};
