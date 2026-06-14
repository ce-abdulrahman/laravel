<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained('achievements')->cascadeOnDelete();
            $table->string('event_type', 50); // progress_updated | unlocked
            $table->bigInteger('event_value')->default(0); // delta or new value
            $table->timestamp('created_at')->useCurrent(); // no updated_at (append-only log)

            $table->index(['user_id', 'created_at']);
            $table->index('created_at'); // for TTL cleanup command
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_events');
    }
};
