<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Safe add-only: creates the prayer_times table if it does not exist.
     */
    public function up(): void
    {
        if (Schema::hasTable('prayer_times')) {
            return; // already migrated, nothing to do
        }

        Schema::create('prayer_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')
                  ->constrained('cities')
                  ->cascadeOnDelete();

            $table->date('date')->index();
            $table->unsignedSmallInteger('year')->index();

            $table->time('fajr');
            $table->time('sunrise');
            $table->time('dhuhr');
            $table->time('asr');
            $table->time('maghrib');
            $table->time('isha');

            // Source tracking: manual | import | calculated
            $table->string('source', 20)->default('import');

            $table->timestamps();

            // Prevent duplicate city/date combinations
            $table->unique(['city_id', 'date'], 'uq_prayer_times_city_date');

            // Composite index for common API query: city + year
            $table->index(['city_id', 'year'], 'idx_prayer_times_city_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_times');
    }
};
