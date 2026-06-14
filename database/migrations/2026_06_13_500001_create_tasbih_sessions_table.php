<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasbih_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dhikr_id')->nullable()->constrained('tasbihs')->cascadeOnDelete();
            $table->string('custom_dhikr_name')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->integer('total_count')->default(0);
            $table->decimal('avg_per_minute', 8, 2)->default(0.00);
            $table->date('session_date');
            $table->string('status', 20)->default('active'); // active, completed, paused
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('session_date');
        });

        // Apply conditional index to prevent more than one active session per user
        if (config('database.default') === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX unique_active_session_per_user ON tasbih_sessions (user_id) WHERE status = "active"');
        } else {
            DB::statement('CREATE UNIQUE INDEX unique_active_session_per_user ON tasbih_sessions (user_id, status) WHERE status = "active"');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasbih_sessions');
    }
};
