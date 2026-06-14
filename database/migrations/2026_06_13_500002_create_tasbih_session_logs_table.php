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
        Schema::create('tasbih_session_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('tasbih_sessions')->cascadeOnDelete();
            $table->string('event_uuid', 36)->unique(); // Enforce deduplication globally
            $table->string('event_type', 30); // start, pause, resume, increment, end
            $table->integer('value')->nullable(); // count value at this event point
            $table->dateTime('timestamp');
            $table->timestamps();

            $table->index(['session_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasbih_session_logs');
    }
};
