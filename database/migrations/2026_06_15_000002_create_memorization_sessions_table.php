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
        Schema::create('memorization_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_type'); // 'memorization', 'review', 'quiz'
            $table->string('status')->default('completed'); // 'completed', 'interrupted', 'abandoned'
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('ayahs_reviewed')->default(0);
            $table->unsignedInteger('ayahs_memorized')->default(0);
            $table->unsignedInteger('score')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'session_type']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memorization_sessions');
    }
};
