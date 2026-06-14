<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Nullable: inactivity/smart reminders may not have a template
            $table->foreignId('template_id')
                  ->nullable()
                  ->constrained('reminder_templates')
                  ->nullOnDelete();

            // The type of notification (mirrors reminder_type)
            $table->string('notification_type', 50)->index();

            // Deterministic notification ID sent to device (userId_type_time)
            $table->string('notification_id', 100)->nullable()->index();

            // Delivery timestamps
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('opened_at')->nullable();

            // Delivery status: sent | opened | failed | snoozed | cancelled
            $table->string('status', 30)->default('sent')->index();

            // For analytics / troubleshooting
            $table->string('device_platform', 20)->nullable();   // android | ios | web
            $table->string('timezone', 64)->nullable();
            $table->json('payload_json')->nullable();             // full notification payload

            $table->timestamps();

            // Quick analytics queries
            $table->index(['user_id', 'sent_at']);
            $table->index(['notification_type', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
