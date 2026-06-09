<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\Surah;
use App\Models\HadithCategory;
use App\Models\Hadith;
use App\Models\AdhkarCategory;
use App\Models\Adhkar;
use App\Models\TajweedRuleCategory;
use App\Models\TajweedRule;
use App\Models\Ayah;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AuditTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:audit 
                            {--format=text : Output format (text, markdown, json)} 
                            {--save : Save report to storage/app/translation_coverage_report.md}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit translation coverage across all content models, Ayahs, and UI keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $format = $this->option('format');

        if ($format !== 'json') {
            $this->info('Starting Translation Coverage Audit...');
        }

        // Get active languages
        try {
            $activeLanguages = Language::activeList();
        } catch (\Exception $e) {
            $this->error('Failed to load active languages: ' . $e->getMessage());
            return 1;
        }

        if ($activeLanguages->isEmpty()) {
            $this->warn('No active languages found in the database. Please seed or activate languages first.');
            return 1;
        }

        $activeCodes = $activeLanguages->pluck('code')->toArray();

        if ($format !== 'json') {
            $this->info('Active locales: ' . implode(', ', $activeCodes));
        }

        // Define translatable content models
        $contentModels = [
            'Surah' => [
                'class' => Surah::class,
                'fields' => ['name'],
                'label' => 'Surahs (Names)',
            ],
            'HadithCategory' => [
                'class' => HadithCategory::class,
                'fields' => ['name'],
                'label' => 'Hadith Categories',
            ],
            'Hadith' => [
                'class' => Hadith::class,
                'fields' => ['translation', 'explanation'],
                'label' => 'Hadiths (Texts & Explanations)',
            ],
            'AdhkarCategory' => [
                'class' => AdhkarCategory::class,
                'fields' => ['name'],
                'label' => 'Adhkar Categories',
            ],
            'Adhkar' => [
                'class' => Adhkar::class,
                'fields' => ['translation'],
                'label' => 'Adhkars (Translations)',
            ],
            'TajweedRuleCategory' => [
                'class' => TajweedRuleCategory::class,
                'fields' => ['name', 'description'],
                'label' => 'Tajweed Rule Categories',
            ],
            'TajweedRule' => [
                'class' => TajweedRule::class,
                'fields' => ['name', 'description'],
                'label' => 'Tajweed Rules',
            ],
        ];

        $results = [];
        $summaryRows = [];

        // 1. Audit Translatable Content Models
        foreach ($contentModels as $name => $config) {
            $modelClass = $config['class'];
            $fields = $config['fields'];
            $label = $config['label'];

            $totalCount = $modelClass::count();

            foreach ($activeCodes as $locale) {
                $missingIds = [];
                $emptyItems = [];

                if ($totalCount > 0) {
                    $modelClass::with('translations')->chunk(150, function ($records) use ($locale, $fields, &$missingIds, &$emptyItems) {
                        foreach ($records as $record) {
                            $trans = $record->translations->where('locale', $locale)->first();
                            if (!$trans) {
                                $missingIds[] = $record->id;
                            } else {
                                $emptyFields = [];
                                foreach ($fields as $field) {
                                    $val = $trans->{$field};
                                    if ($val === null || $val === '') {
                                        $emptyFields[] = $field;
                                    }
                                }
                                if (!empty($emptyFields)) {
                                    $emptyItems[$record->id] = $emptyFields;
                                }
                            }
                        }
                    });
                }

                $missingCount = count($missingIds);
                $emptyCount = count($emptyItems);
                $translatedCount = $totalCount - $missingCount - $emptyCount;
                $coveragePct = $totalCount > 0 ? round(($translatedCount / $totalCount) * 100, 2) : 100;

                $results['content'][$name][$locale] = [
                    'total' => $totalCount,
                    'translated' => $translatedCount,
                    'missing_count' => $missingCount,
                    'missing_ids' => $missingIds,
                    'empty_count' => $emptyCount,
                    'empty_items' => $emptyItems,
                    'coverage' => $coveragePct,
                ];

                $summaryRows[] = [
                    'section' => $label,
                    'locale' => $locale,
                    'total' => $totalCount,
                    'translated' => $translatedCount,
                    'missing' => $missingCount,
                    'empty' => $emptyCount,
                    'coverage' => $coveragePct . '%',
                ];
            }
        }

        // 2. Audit Ayah Translations
        $totalAyahs = Ayah::count();
        foreach ($activeCodes as $locale) {
            $missingAyahIds = [];
            $emptyAyahIds = [];

            if ($totalAyahs > 0) {
                Ayah::with('translations')->chunk(300, function ($ayahs) use ($locale, &$missingAyahIds, &$emptyAyahIds) {
                    foreach ($ayahs as $ayah) {
                        $trans = $ayah->translations->where('language_code', $locale)->first();
                        if (!$trans) {
                            $missingAyahIds[] = $ayah->id;
                        } elseif ($trans->content === null || $trans->content === '') {
                            $emptyAyahIds[] = $ayah->id;
                        }
                    }
                });
            }

            $missingCount = count($missingAyahIds);
            $emptyCount = count($emptyAyahIds);
            $translatedCount = $totalAyahs - $missingCount - $emptyCount;
            $coveragePct = $totalAyahs > 0 ? round(($translatedCount / $totalAyahs) * 100, 2) : 100;

            $results['ayahs'][$locale] = [
                'total' => $totalAyahs,
                'translated' => $translatedCount,
                'missing_count' => $missingCount,
                'missing_ids' => $missingAyahIds,
                'empty_count' => $emptyCount,
                'empty_ids' => $emptyAyahIds,
                'coverage' => $coveragePct,
            ];

            $summaryRows[] = [
                'section' => 'Ayah Verse Translations',
                'locale' => $locale,
                'total' => $totalAyahs,
                'translated' => $translatedCount,
                'missing' => $missingCount,
                'empty' => $emptyCount,
                'coverage' => $coveragePct . '%',
            ];
        }

        // 3. Audit UI Translations
        $totalUiKeys = TranslationKey::count();
        foreach ($activeLanguages as $lang) {
            $locale = $lang->code;
            $missingUiKeys = [];
            $emptyUiKeys = [];

            if ($totalUiKeys > 0) {
                TranslationKey::with(['translations' => function ($q) use ($lang) {
                    $q->where('language_id', $lang->id);
                }])->chunk(150, function ($keys) use (&$missingUiKeys, &$emptyUiKeys) {
                    foreach ($keys as $key) {
                        $trans = $key->translations->first();
                        if (!$trans) {
                            $missingUiKeys[] = $key->key;
                        } elseif ($trans->value === null || $trans->value === '') {
                            $emptyUiKeys[] = $key->key;
                        }
                    }
                });
            }

            $missingCount = count($missingUiKeys);
            $emptyCount = count($emptyUiKeys);
            $translatedCount = $totalUiKeys - $missingCount - $emptyCount;
            $coveragePct = $totalUiKeys > 0 ? round(($translatedCount / $totalUiKeys) * 100, 2) : 100;

            $results['ui'][$locale] = [
                'total' => $totalUiKeys,
                'translated' => $translatedCount,
                'missing_count' => $missingCount,
                'missing_keys' => $missingUiKeys,
                'empty_count' => $emptyCount,
                'empty_keys' => $emptyUiKeys,
                'coverage' => $coveragePct,
            ];

            $summaryRows[] = [
                'section' => 'UI Screen Translations',
                'locale' => $locale,
                'total' => $totalUiKeys,
                'translated' => $translatedCount,
                'missing' => $missingCount,
                'empty' => $emptyCount,
                'coverage' => $coveragePct . '%',
            ];
        }

        // Generate Console output
        $format = $this->option('format');

        if ($format === 'json') {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
        } else {
            $this->newLine();
            $this->info('--- COVERAGE AUDIT SUMMARY ---');
            $this->table(
                ['Audited Section', 'Locale', 'Total Items', 'Translated', 'Missing', 'Empty', 'Coverage %'],
                $summaryRows
            );

            // Display Critical Warnings in CLI
            $hasWarnings = false;
            foreach ($summaryRows as $row) {
                $pct = (float) rtrim($row['coverage'], '%');
                if ($pct < 100) {
                    $hasWarnings = true;
                    $this->warn(sprintf(
                        '[%s] Locale "%s" has %d missing and %d empty translations (Coverage: %s).',
                        $row['section'],
                        $row['locale'],
                        $row['missing'],
                        $row['empty'],
                        $row['coverage']
                    ));
                }
            }

            if (!$hasWarnings) {
                $this->info('All active locales are 100% fully translated across all content and UI keys!');
            }
        }

        // Handle file exports (markdown/json) if --save option passed
        if ($this->option('save')) {
            $reportPath = storage_path('app/translation_coverage_report.md');
            $reportContent = $this->generateMarkdownReport($summaryRows, $results, $contentModels);
            
            try {
                File::ensureDirectoryExists(dirname($reportPath));
                File::put($reportPath, $reportContent);
                $this->info("Audit report successfully saved to: {$reportPath}");
            } catch (\Exception $e) {
                $this->error('Failed to save report: ' . $e->getMessage());
            }
        }

        return 0;
    }

    /**
     * Compile a beautiful markdown report string.
     */
    protected function generateMarkdownReport(array $summaryRows, array $results, array $contentModels): string
    {
        $dateStr = date('Y-m-d H:i:s');
        
        $md = "# Translation Coverage Audit Report\n";
        $md .= "Generated on: `{$dateStr}`\n\n";
        
        $md .= "## 1. Coverage Summary Matrix\n\n";
        $md .= "| Section | Locale | Total Items | Fully Translated | Missing (No Record) | Empty (Blank Value) | Coverage % |\n";
        $md .= "| --- | --- | --- | --- | --- | --- | --- |\n";
        foreach ($summaryRows as $row) {
            $md .= sprintf(
                "| %s | %s | %d | %d | %d | %d | %s |\n",
                $row['section'],
                $row['locale'],
                $row['total'],
                $row['translated'],
                $row['missing'],
                $row['empty'],
                $row['coverage']
            );
        }
        $md .= "\n---\n\n";

        $md .= "## 2. Detailed Audit & Action Items\n\n";

        // Translatable Content
        $md .= "### Translatable Content Models\n";
        foreach ($contentModels as $name => $config) {
            $label = $config['label'];
            $md .= "#### {$label}\n";
            
            if (isset($results['content'][$name])) {
                foreach ($results['content'][$name] as $locale => $data) {
                    $md .= "* **Locale [{$locale}]**: Coverage: `{$data['coverage']}%`\n";
                    if ($data['missing_count'] > 0) {
                        $md .= "  * ❌ **Missing translations (IDs)**: " . implode(', ', array_slice($data['missing_ids'], 0, 30));
                        if ($data['missing_count'] > 30) {
                            $md .= " ... (and " . ($data['missing_count'] - 30) . " more)";
                        }
                        $md .= "\n";
                    }
                    if ($data['empty_count'] > 0) {
                        $md .= "  * ⚠️ **Empty fields (IDs & fields)**:\n";
                        $count = 0;
                        foreach ($data['empty_items'] as $id => $fields) {
                            if ($count++ < 20) {
                                $md .= "    * ID `{$id}`: empty field(s) [" . implode(', ', $fields) . "]\n";
                            } else {
                                $md .= "    * ... (and " . ($data['empty_count'] - 20) . " more)\n";
                                break;
                            }
                        }
                    }
                    if ($data['missing_count'] === 0 && $data['empty_count'] === 0) {
                        $md .= "  * ✅ Fully translated!\n";
                    }
                }
            }
            $md .= "\n";
        }

        // Ayahs
        $md .= "### Ayah Verse Translations\n";
        if (isset($results['ayahs'])) {
            foreach ($results['ayahs'] as $locale => $data) {
                $md .= "* **Locale [{$locale}]**: Coverage: `{$data['coverage']}%`\n";
                if ($data['missing_count'] > 0) {
                    $md .= "  * ❌ **Missing translations (Ayah IDs)**: " . implode(', ', array_slice($data['missing_ids'], 0, 30));
                    if ($data['missing_count'] > 30) {
                        $md .= " ... (and " . ($data['missing_count'] - 30) . " more)";
                    }
                    $md .= "\n";
                }
                if ($data['empty_count'] > 0) {
                    $md .= "  * ⚠️ **Empty translation records (Ayah IDs)**: " . implode(', ', array_slice($data['empty_ids'], 0, 30));
                    if ($data['empty_count'] > 30) {
                        $md .= " ... (and " . ($data['empty_count'] - 30) . " more)";
                    }
                    $md .= "\n";
                }
                if ($data['missing_count'] === 0 && $data['empty_count'] === 0) {
                    $md .= "  * ✅ Fully translated!\n";
                }
            }
        }
        $md .= "\n";

        // UI Translation keys
        $md .= "### UI Translation Keys\n";
        if (isset($results['ui'])) {
            foreach ($results['ui'] as $locale => $data) {
                $md .= "* **Locale [{$locale}]**: Coverage: `{$data['coverage']}%`\n";
                if ($data['missing_count'] > 0) {
                    $md .= "  * ❌ **Missing translations (Keys)**: \n";
                    $count = 0;
                    foreach ($data['missing_keys'] as $key) {
                        if ($count++ < 30) {
                            $md .= "    * `{$key}`\n";
                        } else {
                            $md .= "    * ... (and " . ($data['missing_count'] - 30) . " more)\n";
                            break;
                        }
                    }
                }
                if ($data['empty_count'] > 0) {
                    $md .= "  * ⚠️ **Empty values (Keys)**: \n";
                    $count = 0;
                    foreach ($data['empty_keys'] as $key) {
                        if ($count++ < 30) {
                            $md .= "    * `{$key}`\n";
                        } else {
                            $md .= "    * ... (and " . ($data['empty_count'] - 30) . " more)\n";
                            break;
                        }
                    }
                }
                if ($data['missing_count'] === 0 && $data['empty_count'] === 0) {
                    $md .= "  * ✅ Fully translated!\n";
                }
            }
        }

        return $md;
    }
}
