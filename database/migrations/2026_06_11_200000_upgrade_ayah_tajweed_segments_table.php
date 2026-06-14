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
        Schema::table('ayah_tajweed_segments', function (Blueprint $table) {
            $table->renameColumn('text_segment', 'matched_text');
            $table->json('metadata')->nullable()->after('end_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ayah_tajweed_segments', function (Blueprint $table) {
            $table->renameColumn('matched_text', 'text_segment');
            $table->dropColumn('metadata');
        });
    }
};
