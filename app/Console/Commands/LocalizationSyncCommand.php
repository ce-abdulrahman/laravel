<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Services\TranslationRegistryService;
use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class LocalizationSyncCommand extends Command
{
    protected $signature = 'localization:sync';
    protected $description = 'Synchronize translation database with codebase keys, filesystem files, and rebuild cache';

    protected TranslationRegistryService $registryService;

    public function __construct(TranslationRegistryService $registryService)
    {
        parent::__construct();
        $this->registryService = $registryService;
    }

    public function handle(): int
    {
        $this->info('🚀 Starting Translation System Synchronization...');

        // 1. Run Codebase Scan
        $this->info('Step 1/5: Scanning codebase for new translation keys...');
        $keys = $this->registryService->scanCodebase();
        $this->line("Discovered " . count($keys) . " keys in codebase.");
        foreach ($keys as $key) {
            $this->registryService->registerKey($key);
        }

        // 2. Run Import from files
        $this->info('Step 2/5: Importing from filesystem lang files...');
        try {
            Artisan::call('localization:import', [], $this->output);
        } catch (\Exception $e) {
            $this->warn('File import skipped or failed: ' . $e->getMessage());
        }

        // 3. Generate Missing Rows
        $this->info('Step 3/5: Ensuring missing active language rows exist...');
        $languages = Language::all();
        $translationKeys = TranslationKey::all();
        $generatedCount = 0;

        DB::transaction(function () use ($languages, $translationKeys, &$generatedCount) {
            foreach ($translationKeys as $key) {
                foreach ($languages as $lang) {
                    $exists = UiTranslation::where('translation_key_id', $key->id)
                        ->where('language_id', $lang->id)
                        ->exists();

                    if (!$exists) {
                        $value = null;
                        $isAuto = true;
                        
                        if ($lang->code === 'en') {
                            $value = $this->registryService->generateDefaultEnglish($key->key);
                            $isAuto = false;
                        }

                        UiTranslation::create([
                            'translation_key_id' => $key->id,
                            'language_id' => $lang->id,
                            'value' => $value,
                            'is_auto_generated' => $isAuto,
                        ]);
                        $generatedCount++;
                    }
                }
            }
        });
        $this->line("Generated {$generatedCount} missing language translation rows.");

        // 4. Clear Cache
        $this->info('Step 4/5: Clearing translation cache...');
        $translationService = app(TranslationService::class);
        $translationService->clearCache();

        // 5. Rebuild Translation Registry (re-cache everything)
        $this->info('Step 5/5: Rebuilding translation cache registry...');
        foreach ($languages as $lang) {
            if ($lang->is_active) {
                $translationService->getTranslationsForLocale($lang->code);
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════');
        $this->info('  🎉  Localization Synchronization Complete');
        $this->info('═══════════════════════════════════════════════');
        $this->line("  Total Keys in DB     : " . TranslationKey::count());
        $this->line("  Total Lang Rows      : " . UiTranslation::count());
        $this->line("  Active Languages     : " . Language::where('is_active', true)->count());
        $this->line("  New Rows Generated   : " . $generatedCount);
        $this->info('═══════════════════════════════════════════════');

        return self::SUCCESS;
    }
}
