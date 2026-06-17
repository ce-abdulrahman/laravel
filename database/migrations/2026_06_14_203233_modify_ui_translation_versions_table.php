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
        Schema::table('ui_translation_versions', function (Blueprint $table) {
            $table->string('key')->nullable()->index();
            $table->string('module')->nullable()->index();
            $table->integer('version')->default(1);
            $table->boolean('is_auto_generated')->default(false);
        });

        // Populate existing rows in ui_translation_versions
        try {
            $versions = DB::table('ui_translation_versions')
                ->join('ui_translations', 'ui_translation_versions.ui_translation_id', '=', 'ui_translations.id')
                ->join('translation_keys', 'ui_translations.translation_key_id', '=', 'translation_keys.id')
                ->select('ui_translation_versions.id', 'translation_keys.key', 'translation_keys.group')
                ->get();

            $keyCounts = [];
            foreach ($versions as $v) {
                if (!isset($keyCounts[$v->key])) {
                    $keyCounts[$v->key] = 0;
                }
                $keyCounts[$v->key]++;
                
                DB::table('ui_translation_versions')
                    ->where('id', $v->id)
                    ->update([
                        'key' => $v->key,
                        'module' => $v->group,
                        'version' => $keyCounts[$v->key],
                    ]);
            }
        } catch (\Exception $e) {
            // Silence if tables or database do not contain records yet
        }

        // Apply unique constraint
        Schema::table('ui_translation_versions', function (Blueprint $table) {
            $table->unique(['key', 'version'], 'ui_trans_key_version_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ui_translation_versions', function (Blueprint $table) {
            $table->dropUnique('ui_trans_key_version_unique');
            $table->dropColumn(['key', 'module', 'version', 'is_auto_generated']);
        });
    }
};
