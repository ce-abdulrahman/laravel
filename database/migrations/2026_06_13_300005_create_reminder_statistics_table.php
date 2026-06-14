<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_statistics', function (Blueprint $table) {
            $table->id();

            // Aggregate per calendar date (UTC)
            $table->date('date')->unique();

            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('opened_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('snoozed_count')->default(0);

            // Unique users who received at least one notification
            $table->unsignedInteger('active_users')->default(0);

            // Overall open rate (percentage, pre-calculated for speed)
            $table->decimal('open_rate', 5, 2)->default(0.00);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_statistics');
    }
};
