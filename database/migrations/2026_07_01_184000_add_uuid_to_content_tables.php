<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'surahs',
            'ayahs',
            'tafsirs',
            'hadith_categories',
            'hadiths',
            'adhkar_categories',
            'adhkars',
            'reciters',
            'tajweed_rules',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->uuid('uuid')->nullable()->after('id');
                });
                
                // Populate existing records with UUIDs
                DB::table($table)->get()->each(function ($record) use ($table) {
                    DB::table($table)
                        ->where('id', $record->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                });

                Schema::table($table, function (Blueprint $table) {
                    $table->uuid('uuid')->nullable(false)->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'surahs',
            'ayahs',
            'tafsirs',
            'hadith_categories',
            'hadiths',
            'adhkar_categories',
            'adhkars',
            'reciters',
            'tajweed_rules',
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('uuid');
                });
            }
        }
    }
};
