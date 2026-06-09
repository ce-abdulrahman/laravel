<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LocalizationImport extends Command
{
    protected $signature = 'localization:import
                            {--locale= : Only import a specific locale (e.g. en, ku, ar)}
                            {--dry-run : Preview what would be imported without writing to DB}
                            {--force : Overwrite existing values even if already set}';

    protected $description = 'Import filesystem lang files (PHP + JSON) into the database localization system';

    /** Summary counters */
    protected int $filesScanned   = 0;
    protected int $keysFound      = 0;
    protected int $keysInserted   = 0;
    protected int $keysUpdated    = 0;
    protected int $keysSkipped    = 0;
    protected int $valuesInserted = 0;
    protected int $valuesUpdated  = 0;
    protected int $valuesSkipped  = 0;
    protected array $missingLocales = [];

    public function handle(): int
    {
        $isDryRun  = (bool) $this->option('dry-run');
        $isForce   = (bool) $this->option('force');
        $onlyLocale = $this->option('locale');

        if ($isDryRun) {
            $this->warn('🔍  DRY-RUN mode — no changes will be written.');
        }

        // ── 1. Resolve locales to process ───────────────────────────────────
        $langPath = resource_path('lang');

        if (!File::isDirectory($langPath)) {
            $this->error("Lang directory not found: {$langPath}");
            return self::FAILURE;
        }

        // Collect locale codes from directories and JSON files
        $localeDirs  = collect(File::directories($langPath))
            ->map(fn($p) => basename($p));

        $localeJsons = collect(File::files($langPath))
            ->filter(fn($f) => $f->getExtension() === 'json')
            ->map(fn($f) => $f->getFilenameWithoutExtension());

        $allLocales = $localeDirs->merge($localeJsons)->unique()->values();

        if ($onlyLocale) {
            if (!$allLocales->contains($onlyLocale)) {
                $this->error("Locale '{$onlyLocale}' not found in lang directory.");
                return self::FAILURE;
            }
            $allLocales = collect([$onlyLocale]);
        }

        $this->info("📦  Locales detected: " . $allLocales->implode(', '));
        $this->newLine();

        // ── 2. Resolve active languages from DB ─────────────────────────────
        $languages = Language::all()->keyBy('code');

        foreach ($allLocales as $locale) {
            if (!$languages->has($locale)) {
                $this->missingLocales[] = $locale;
                $this->warn("  ⚠  Locale '{$locale}' has no Language record in DB — values will be skipped.");
            }
        }

        // ── 3. Collect all keys from all locales ─────────────────────────────
        // Structure: ['group.key' => ['en' => 'value', 'ku' => 'value', 'ar' => 'value']]
        $keyValues = [];

        foreach ($allLocales as $locale) {
            // 3a. PHP files inside lang/{locale}/ directory
            $localeDir = $langPath . DIRECTORY_SEPARATOR . $locale;
            if (File::isDirectory($localeDir)) {
                foreach (File::files($localeDir) as $file) {
                    if ($file->getExtension() !== 'php') {
                        continue;
                    }
                    $this->filesScanned++;
                    $group  = $file->getFilenameWithoutExtension();
                    $values = require $file->getPathname();

                    if (!is_array($values)) {
                        continue;
                    }

                    $flat = $this->flattenArray($values, $group);
                    foreach ($flat as $fullKey => $value) {
                        $keyValues[$fullKey][$locale] = $value;
                    }
                }
            }

            // 3b. JSON file lang/{locale}.json
            $jsonFile = $langPath . DIRECTORY_SEPARATOR . $locale . '.json';
            if (File::isFile($jsonFile)) {
                $this->filesScanned++;
                $decoded = json_decode(File::get($jsonFile), true);
                if (is_array($decoded)) {
                    foreach ($decoded as $jsonKey => $value) {
                        // Use _json group; slugify the key to dot-notation safe key
                        $safeKey = '_json.' . $this->slugifyJsonKey($jsonKey);
                        $keyValues[$safeKey][$locale] = $value;
                    }
                }
            }
        }

        $this->keysFound = count($keyValues);
        $this->info("🔑  Keys discovered: {$this->keysFound} across {$this->filesScanned} files");
        $this->newLine();

        if ($isDryRun) {
            $this->displayDryRunTable($keyValues, array_keys($languages->toArray()));
            $this->printSummary($isDryRun);
            return self::SUCCESS;
        }

        // ── 4. Upsert keys and translations inside a transaction ─────────────
        DB::transaction(function () use ($keyValues, $languages, $isForce) {

            $bar = $this->output->createProgressBar(count($keyValues));
            $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
            $bar->start();

            foreach ($keyValues as $fullKey => $localeValues) {
                $parts     = explode('.', $fullKey, 2);
                $group     = $parts[0];
                $keyString = $fullKey; // full dot notation stored as the key

                // Upsert the translation key
                $existing = TranslationKey::where('key', $keyString)->first();
                if ($existing) {
                    $translationKey = $existing;
                    $this->keysSkipped++;
                } else {
                    $translationKey = TranslationKey::create([
                        'key'         => $keyString,
                        'group'       => $group,
                        'description' => null,
                    ]);
                    $this->keysInserted++;
                }

                $bar->setMessage($keyString);

                // Ensure all active languages have a ui_translation row
                foreach ($languages as $lang) {
                    $locale   = $lang->code;
                    $newValue = $localeValues[$locale] ?? null;

                    $uiTrans = UiTranslation::where('translation_key_id', $translationKey->id)
                        ->where('language_id', $lang->id)
                        ->first();

                    if (!$uiTrans) {
                        // Use DB directly to bypass model events/observers during bulk import
                        DB::table('ui_translations')->insert([
                            'translation_key_id' => $translationKey->id,
                            'language_id'        => $lang->id,
                            'value'              => $newValue,
                            'is_auto_generated'  => ($newValue === null) ? 1 : 0,
                            'created_at'         => now(),
                            'updated_at'         => now(),
                        ]);
                        $this->valuesInserted++;
                    } elseif ($newValue !== null && ($isForce || empty($uiTrans->value))) {
                        // Only update if forcing OR the existing value is empty
                        DB::table('ui_translations')
                            ->where('id', $uiTrans->id)
                            ->update([
                                'value'             => $newValue,
                                'is_auto_generated' => 0,
                                'updated_at'        => now(),
                            ]);
                        $this->valuesUpdated++;
                    } else {
                        $this->valuesSkipped++;
                    }
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
        });

        // ── 5. Clear translation cache ────────────────────────────────────────
        app(TranslationService::class)->clearCache();
        $this->info('🧹  Translation cache cleared.');

        $this->printSummary($isDryRun);

        return self::SUCCESS;
    }

    /**
     * Recursively flatten a nested array to dot-notation keys.
     * e.g. ['auth' => ['login' => 'Log in']] => ['auth.login' => 'Log in']
     */
    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            // Skip numeric-keyed arrays (sub-arrays without string keys)
            if (is_int($key)) {
                continue;
            }
            $fullKey = $prefix ? "{$prefix}.{$key}" : (string) $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $fullKey));
            } else {
                $result[$fullKey] = (string) $value;
            }
        }
        return $result;
    }

    /**
     * Convert a JSON natural-language key to a slug-safe identifier.
     * e.g. "Forgot your password?" => "forgot_your_password"
     */
    protected function slugifyJsonKey(string $key): string
    {
        // For short keys (< 60 chars), slugify directly
        if (strlen($key) <= 60) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $key), '_'));
            return $slug ?: md5($key);
        }
        // For long natural-language keys, use a hash to keep keys short
        return 'key_' . substr(md5($key), 0, 12);
    }

    protected function displayDryRunTable(array $keyValues, array $localeCodes): void
    {
        $rows = [];
        $sample = array_slice($keyValues, 0, 30, true);
        foreach ($sample as $key => $localeVals) {
            $row = ['key' => $key];
            foreach ($localeCodes as $code) {
                $val = $localeVals[$code] ?? '—';
                $row[$code] = strlen($val) > 40 ? substr($val, 0, 37) . '...' : $val;
            }
            $rows[] = $row;
        }

        $headers = array_merge(['Key'], array_map('strtoupper', $localeCodes));
        $this->table($headers, $rows);

        if (count($keyValues) > 30) {
            $this->line('  ... and ' . (count($keyValues) - 30) . ' more keys');
        }
    }

    protected function printSummary(bool $isDryRun): void
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════');
        $this->info('  📊  Localization Import Summary');
        $this->info('═══════════════════════════════════════════════');
        $this->line("  Files scanned      : {$this->filesScanned}");
        $this->line("  Keys discovered    : {$this->keysFound}");

        if (!$isDryRun) {
            $this->line("  Keys inserted      : {$this->keysInserted}");
            $this->line("  Keys skipped       : {$this->keysSkipped} (already exist)");
            $this->line("  Values inserted    : {$this->valuesInserted}");
            $this->line("  Values updated     : {$this->valuesUpdated}");
            $this->line("  Values skipped     : {$this->valuesSkipped}");
        }

        if (!empty($this->missingLocales)) {
            $this->newLine();
            $this->warn('  ⚠  Locales with no Language DB record (values skipped):');
            foreach ($this->missingLocales as $ml) {
                $this->warn("       • {$ml}");
            }
        }

        $this->info('═══════════════════════════════════════════════');
        $this->newLine();
        $this->info($isDryRun
            ? '✅  Dry run complete. Re-run without --dry-run to apply.'
            : '✅  Import complete. All translations are now in the database.');
    }
}
