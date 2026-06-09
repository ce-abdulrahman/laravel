<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Get the mappings configuration shared between up() and down().
     */
    private function getMappings(): array
    {
        return [
            'adhkar_categories' => [
                'translation_table' => 'adhkar_category_translations',
                'fk' => 'adhkar_category_id',
                'fields' => [
                    'name' => [
                        'ar' => 'name_ar',
                        'ku' => 'name_ku',
                        'en' => 'name_en',
                    ],
                ],
            ],
            'hadith_categories' => [
                'translation_table' => 'hadith_category_translations',
                'fk' => 'hadith_category_id',
                'fields' => [
                    'name' => [
                        'ar' => 'name_ar',
                        'ku' => 'name_ku',
                        'en' => 'name_en',
                    ],
                ],
            ],
            'surahs' => [
                'translation_table' => 'surah_translations',
                'fk' => 'surah_id',
                'fields' => [
                    'name' => [
                        'ar' => 'name_ar',
                        'ku' => 'name_ku',
                        'en' => 'name_en',
                    ],
                ],
            ],
            'tajweed_rule_categories' => [
                'translation_table' => 'tajweed_rule_category_translations',
                'fk' => 'tajweed_rule_category_id',
                'fields' => [
                    'name' => [
                        'ar' => 'name_ar',
                        'ku' => 'name_ku',
                        'en' => 'name',
                    ],
                    'description' => [
                        'ar' => 'description_ar',
                        'ku' => 'description_ku',
                        'en' => 'description',
                    ],
                ],
            ],
            'tajweed_rules' => [
                'translation_table' => 'tajweed_rule_translations',
                'fk' => 'tajweed_rule_id',
                'fields' => [
                    'name' => [
                        'ar' => 'name_ar',
                        'ku' => 'name_ku',
                        'en' => 'name',
                    ],
                    'description' => [
                        'ku' => 'description_ku',
                        'en' => 'description',
                    ],
                ],
            ],
            'adhkars' => [
                'translation_table' => 'adhkar_translations',
                'fk' => 'adhkar_id',
                'fields' => [
                    'translation' => [
                        'ku' => 'translation_ku',
                        'en' => 'translation_en',
                    ],
                ],
            ],
            'hadiths' => [
                'translation_table' => 'hadith_translations',
                'fk' => 'hadith_id',
                'fields' => [
                    'translation' => [
                        'ku' => 'translation_ku',
                        'en' => 'translation_en',
                    ],
                    'explanation' => [
                        'ku' => 'explanation_ku',
                        'en' => 'explanation_en',
                    ],
                ],
            ],
        ];
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Validate required languages exist in the system only if there is data to migrate
        $hasDataToMigrate = false;
        $tables = ['surahs', 'adhkar_categories', 'hadith_categories', 'tajweed_rule_categories', 'tajweed_rules', 'adhkars', 'hadiths'];
        foreach ($tables as $t) {
            if (Schema::hasTable($t) && DB::table($t)->count() > 0) {
                $hasDataToMigrate = true;
                break;
            }
        }

        if ($hasDataToMigrate && Schema::hasTable('languages')) {
            $requiredCodes = ['ar', 'ku', 'en'];
            $existingCodes = DB::table('languages')->whereIn('code', $requiredCodes)->pluck('code')->toArray();
            $missingCodes = array_diff($requiredCodes, $existingCodes);

            if (!empty($missingCodes)) {
                throw new \Exception("Migration aborted: Required language records not found in database: " . implode(', ', $missingCodes));
            }
        }

        // 2. Perform chunked migration & integrity verification inside a transaction
        DB::transaction(function () {
            $mappings = $this->getMappings();

            foreach ($mappings as $table => $config) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $translationTable = $config['translation_table'];
                $fk = $config['fk'];
                $fields = $config['fields'];

                if (app()->runningInConsole()) {
                    echo "  -> Migrating data from '{$table}' to '{$translationTable}'...\n";
                }

                // Check if the legacy columns exist
                $hasAnyLegacyColumn = false;
                foreach ($fields as $field => $locales) {
                    foreach ($locales as $locale => $legacyCol) {
                        if (Schema::hasColumn($table, $legacyCol)) {
                            $hasAnyLegacyColumn = true;
                            break 2;
                        }
                    }
                }

                if (!$hasAnyLegacyColumn) {
                    if (app()->runningInConsole()) {
                        echo "     * No legacy columns found for '{$table}', skipping data transfer.\n";
                    }
                    continue;
                }

                // Process chunk by chunk to protect memory consumption
                DB::table($table)->orderBy('id')->chunk(100, function ($rows) use ($translationTable, $fk, $fields) {
                    foreach ($rows as $row) {
                        $localePayloads = [];

                        foreach ($fields as $field => $locales) {
                            foreach ($locales as $locale => $legacyCol) {
                                if (property_exists($row, $legacyCol) && $row->{$legacyCol} !== null && $row->{$legacyCol} !== '') {
                                    $localePayloads[$locale][$field] = $row->{$legacyCol};
                                }
                            }
                        }

                        foreach ($localePayloads as $locale => $payload) {
                            // Safe idempotency: use updateOrInsert to prevent duplicate translation inserts
                            DB::table($translationTable)->updateOrInsert(
                                [$fk => $row->id, 'locale' => $locale],
                                array_merge($payload, ['created_at' => now(), 'updated_at' => now()])
                            );
                        }
                    }
                });
            }

            // 3. Verification checks: locale correctness, row count matches, checksum integrity, and duplicate detection
            foreach ($mappings as $table => $config) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $translationTable = $config['translation_table'];
                $fk = $config['fk'];
                $fields = $config['fields'];

                $hasAnyLegacyColumn = false;
                foreach ($fields as $field => $locales) {
                    foreach ($locales as $locale => $legacyCol) {
                        if (Schema::hasColumn($table, $legacyCol)) {
                            $hasAnyLegacyColumn = true;
                            break 2;
                        }
                    }
                }

                if (!$hasAnyLegacyColumn) {
                    continue;
                }

                if (app()->runningInConsole()) {
                    echo "  -> Verifying integrity for table '{$table}'...\n";
                }

                // Count-based and value-based checksum validation
                DB::table($table)->orderBy('id')->chunk(100, function ($rows) use ($translationTable, $fk, $fields) {
                    foreach ($rows as $row) {
                        foreach ($fields as $field => $locales) {
                            foreach ($locales as $locale => $legacyCol) {
                                if (property_exists($row, $legacyCol) && $row->{$legacyCol} !== null && $row->{$legacyCol} !== '') {
                                    $sourceValue = $row->{$legacyCol};

                                    // Verify destination record exists
                                    $destRecord = DB::table($translationTable)
                                        ->where($fk, $row->id)
                                        ->where('locale', $locale)
                                        ->first();

                                    if (!$destRecord) {
                                        throw new \Exception("Migration integrity check failed: Missing translation record in '{$translationTable}' for {$fk}={$row->id}, locale='{$locale}'.");
                                    }

                                    $destValue = $destRecord->{$field} ?? null;

                                    // Checksum comparison
                                    $sourceHash = md5(trim($sourceValue));
                                    $destHash = md5(trim($destValue ?? ''));

                                    if ($sourceHash !== $destHash) {
                                        throw new \Exception("Migration integrity check failed: Checksum mismatch in '{$translationTable}' for {$fk}={$row->id}, locale='{$locale}', field='{$field}'.");
                                    }
                                }
                            }
                        }
                    }
                });

                // Detect duplicate translation entries
                $duplicates = DB::table($translationTable)
                    ->select($fk, 'locale')
                    ->groupBy($fk, 'locale')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();

                if ($duplicates->isNotEmpty()) {
                    $firstDup = $duplicates->first();
                    throw new \Exception("Migration integrity check failed: Duplicate translations detected in '{$translationTable}' for {$fk}={$firstDup->{$fk}}, locale='{$firstDup->locale}'.");
                }
            }
        });

        // 4. Drop legacy columns only AFTER successful verification above
        Schema::table('adhkar_categories', function (Blueprint $table) {
            $table->dropColumn(['name_ku', 'name_ar', 'name_en']);
        });

        Schema::table('hadith_categories', function (Blueprint $table) {
            $table->dropColumn(['name_ku', 'name_ar', 'name_en']);
        });

        if (Schema::hasColumn('surahs', 'name_ar') || Schema::hasColumn('surahs', 'name_ku') || Schema::hasColumn('surahs', 'name_en')) {
            Schema::table('surahs', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('surahs', 'name_ar')) $columns[] = 'name_ar';
                if (Schema::hasColumn('surahs', 'name_ku')) $columns[] = 'name_ku';
                if (Schema::hasColumn('surahs', 'name_en')) $columns[] = 'name_en';
                $table->dropColumn($columns);
            });
        }

        Schema::table('tajweed_rule_categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'name_ku', 'name_ar', 'description', 'description_ku', 'description_ar']);
        });

        Schema::table('tajweed_rules', function (Blueprint $table) {
            $table->dropColumn(['name', 'name_ku', 'name_ar', 'description', 'description_ku']);
        });

        Schema::table('adhkars', function (Blueprint $table) {
            $table->dropColumn(['translation_ku', 'translation_en']);
        });

        Schema::table('hadiths', function (Blueprint $table) {
            $table->dropColumn(['translation_ku', 'translation_en', 'explanation_ku', 'explanation_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restore legacy columns in database schema
        Schema::table('adhkar_categories', function (Blueprint $table) {
            $table->string('name_ku')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
        });

        Schema::table('hadith_categories', function (Blueprint $table) {
            $table->string('name_ku')->nullable();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
        });

        Schema::table('surahs', function (Blueprint $table) {
            $table->string('name_ar')->nullable();
            $table->string('name_ku')->nullable();
            $table->string('name_en')->nullable();
        });

        Schema::table('tajweed_rule_categories', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('name_ku')->nullable();
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ku')->nullable();
            $table->text('description_ar')->nullable();
        });

        Schema::table('tajweed_rules', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('name_ku')->nullable();
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ku')->nullable();
        });

        Schema::table('adhkars', function (Blueprint $table) {
            $table->text('translation_ku')->nullable();
            $table->text('translation_en')->nullable();
        });

        Schema::table('hadiths', function (Blueprint $table) {
            $table->text('translation_ku')->nullable();
            $table->text('translation_en')->nullable();
            $table->text('explanation_ku')->nullable();
            $table->text('explanation_en')->nullable();
        });

        // 2. Repopulate legacy values from translation tables inside a transaction
        DB::transaction(function () {
            $mappings = $this->getMappings();

            foreach ($mappings as $table => $config) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $translationTable = $config['translation_table'];
                $fk = $config['fk'];
                $fields = $config['fields'];

                if (app()->runningInConsole()) {
                    echo "  -> Restoring data from '{$translationTable}' back to legacy columns in '{$table}'...\n";
                }

                DB::table($table)->orderBy('id')->chunk(100, function ($rows) use ($translationTable, $fk, $fields, $table) {
                    foreach ($rows as $row) {
                        $updatePayload = [];

                        foreach ($fields as $field => $locales) {
                            foreach ($locales as $locale => $legacyCol) {
                                $transRecord = DB::table($translationTable)
                                    ->where($fk, $row->id)
                                    ->where('locale', $locale)
                                    ->first();

                                if ($transRecord && !empty($transRecord->{$field})) {
                                    $updatePayload[$legacyCol] = $transRecord->{$field};
                                }
                            }
                        }

                        if (!empty($updatePayload)) {
                            DB::table($table)->where('id', $row->id)->update($updatePayload);
                        }
                    }
                });
            }
        });
    }
};
