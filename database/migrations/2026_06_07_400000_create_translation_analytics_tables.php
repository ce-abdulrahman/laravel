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
        Schema::create('translation_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_key_id')->nullable()->constrained('translation_keys')->cascadeOnDelete();
            $table->string('key_name', 255);
            $table->string('locale', 10);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('page', 255)->nullable();
            $table->integer('hit_count')->default(1);
            $table->boolean('is_missing')->default(false);
            $table->timestamps();
        });

        Schema::create('translation_usage_summary', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('locale', 10);
            $table->integer('total_requests')->default(0);
            $table->integer('missing_keys_count')->default(0);
            $table->foreignId('top_key_id')->nullable()->constrained('translation_keys')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_usage_summary');
        Schema::dropIfExists('translation_analytics');
    }
};
