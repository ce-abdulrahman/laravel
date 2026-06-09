<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:migrate {table? : The specific table to migrate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from flat multilingual columns to translation tables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tableArg = $this->argument('table');

        $mappings = [
            'adhkar_categories' => [
                'translation_table' => 'adhkar_category_translations',
                'foreign_key' => 'adhkar_category_id',
                'fields' => [
                    'name' => [
                        'name_ku' => 'ku',
                        'name_ar' => 'ar',
                        'name_en' => 'en',
                    ]
                ]
            ],
            'hadith_categories' => [
                'translation_table' => 'hadith_category_translations',
                'foreign_key' => 'hadith_category_id',
                'fields' => [
                    'name' => [
                        'name_ku' => 'ku',
                        'name_ar' => 'ar',
                        'name_en' => 'en',
                    ]
                ]
            ],
            'surahs' => [
                'translation_table' => 'surah_translations',
                'foreign_key' => 'surah_id',
                'fields' => [
                    'name' => [
                        'name_ku' => 'ku',
                        'name_ar' => 'ar',
                        'name_en' => 'en',
                    ]
                ]
            ],
            'tajweed_rule_categories' => [
                'translation_table' => 'tajweed_rule_category_translations',
                'foreign_key' => 'tajweed_rule_category_id',
                'fields' => [
                    'name' => [
                        'name' => 'en',
                        'name_ku' => 'ku',
                        'name_ar' => 'ar',
                    ],
                    'description' => [
                        'description' => 'en',
                        'description_ku' => 'ku',
                        'description_ar' => 'ar',
                    ]
                ]
            ],
            'tajweed_rules' => [
                'translation_table' => 'tajweed_rule_translations',
                'foreign_key' => 'tajweed_rule_id',
                'fields' => [
                    'name' => [
                        'name' => 'en',
                        'name_ku' => 'ku',
                        'name_ar' => 'ar',
                    ],
                    'description' => [
                        'description' => 'en',
                        'description_ku' => 'ku',
                    ]
                ]
            ],
            'adhkars' => [
                'translation_table' => 'adhkar_translations',
                'foreign_key' => 'adhkar_id',
                'fields' => [
                    'translation' => [
                        'translation_ku' => 'ku',
                        'translation_en' => 'en',
                    ]
                ]
            ],
            'hadiths' => [
                'translation_table' => 'hadith_translations',
                'foreign_key' => 'hadith_id',
                'fields' => [
                    'translation' => [
                        'translation_ku' => 'ku',
                        'translation_en' => 'en',
                    ],
                    'explanation' => [
                        'explanation_ku' => 'ku',
                        'explanation_en' => 'en',
                    ]
                ]
            ],
        ];

        if ($tableArg) {
            if (!isset($mappings[$tableArg])) {
                $this->error("Unknown table: {$tableArg}");
                return 1;
            }
            $this->migrateTable($tableArg, $mappings[$tableArg]);
        } else {
            foreach ($mappings as $table => $config) {
                $this->migrateTable($table, $config);
            }
        }

        $this->info('All migrations and verifications completed successfully.');
        return 0;
    }

    protected function migrateTable(string $table, array $config): void
    {
        $this->info("Migrating table: {$table}...");

        $translationTable = $config['translation_table'];
        $foreignKey = $config['foreign_key'];
        $fields = $config['fields'];

        // Get all parent records using DB query to bypass Eloquent attribute logic
        $parentRows = DB::table($table)->get();
        $this->info("Found {$parentRows->count()} rows in parent table '{$table}'.");

        $insertedCount = 0;

        foreach ($parentRows as $row) {
            // We group by locale to insert one row per locale containing all translated fields
            $localesData = [];

            foreach ($fields as $transField => $colToLocale) {
                foreach ($colToLocale as $col => $locale) {
                    if (!Schema::hasColumn($table, $col)) {
                        continue;
                    }
                    $value = $row->{$col} ?? null;
                    if ($value !== null && $value !== '') {
                        $localesData[$locale][$transField] = $value;
                    }
                }
            }

            foreach ($localesData as $locale => $data) {
                // Check if translation already exists
                $exists = DB::table($translationTable)
                    ->where($foreignKey, $row->id)
                    ->where('locale', $locale)
                    ->exists();

                if (!$exists) {
                    $insertData = array_merge([
                        $foreignKey => $row->id,
                        'locale' => $locale,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], $data);

                    DB::table($translationTable)->insert($insertData);
                    $insertedCount++;
                }
            }
        }

        $this->info("Inserted {$insertedCount} translations into '{$translationTable}'.");

        // VERIFICATION STEP
        $this->verifyTable($table, $config);
    }

    protected function verifyTable(string $table, array $config): void
    {
        $this->info("Verifying migration for table: {$table}...");

        $translationTable = $config['translation_table'];
        $foreignKey = $config['foreign_key'];
        $fields = $config['fields'];

        $parentRows = DB::table($table)->get();

        foreach ($parentRows as $row) {
            foreach ($fields as $transField => $colToLocale) {
                foreach ($colToLocale as $col => $locale) {
                    if (!Schema::hasColumn($table, $col)) {
                        continue;
                    }
                    $originalValue = $row->{$col} ?? null;
                    if ($originalValue !== null && $originalValue !== '') {
                        // Check if it exists in translation table
                        $transRow = DB::table($translationTable)
                            ->where($foreignKey, $row->id)
                            ->where('locale', $locale)
                            ->first();

                        if (!$transRow || $transRow->{$transField} !== $originalValue) {
                            $this->error("Verification FAILED for table {$table}, row ID {$row->id}, field '{$transField}' for locale '{$locale}'. Expected: '{$originalValue}', Got: " . ($transRow ? $transRow->{$transField} : 'None'));
                            throw new \Exception("Data migration verification failed.");
                        }
                    }
                }
            }
        }

        $this->info("Verification SUCCESS for table '{$table}'! All translatable data successfully copied.");
    }
}
