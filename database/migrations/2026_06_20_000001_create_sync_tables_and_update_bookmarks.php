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
        // Update bookmarks table
        Schema::table('bookmarks', function (Blueprint $table) {
            if (!Schema::hasColumn('bookmarks', 'bookmark_id')) {
                $table->string('bookmark_id')->nullable()->unique();
            }
            if (!Schema::hasColumn('bookmarks', 'surah_number')) {
                $table->unsignedInteger('surah_number')->nullable();
            }
            if (!Schema::hasColumn('bookmarks', 'ayah_number')) {
                $table->unsignedInteger('ayah_number')->nullable();
            }
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropColumn(['bookmark_id', 'surah_number', 'ayah_number']);
        });
    }
};
