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
        Schema::create('user_ayah_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ayah_id')->constrained()->cascadeOnDelete();
            $table->string('memorize_status')->default('not_started');
            $table->timestamp('last_memorized_at')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->unsignedInteger('strength_score')->default(0);
            $table->unsignedInteger('mistakes_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'ayah_id']);
            $table->index(['user_id', 'memorize_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ayah_progress');
    }
};
