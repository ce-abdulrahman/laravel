<?php

namespace App\Console\Commands;

use App\Services\TranslationRegistryService;
use App\Services\TranslationService;
use Illuminate\Console\Command;

class LocalizationScanCommand extends Command
{
    protected $signature = 'localization:scan';
    protected $description = 'Scan the entire codebase recursively and register all discovered translation keys';

    protected TranslationRegistryService $registryService;

    public function __construct(TranslationRegistryService $registryService)
    {
        parent::__construct();
        $this->registryService = $registryService;
    }

    public function handle(): int
    {
        $startTime = microtime(true);
        $this->info('🔍 Starting full codebase translation scan...');
        
        $keys = $this->registryService->scanCodebase();
        $count = count($keys);

        $this->info("🔑 Found {$count} unique translation keys in codebase.");

        if ($count === 0) {
            $this->warn('No translation keys discovered in the scan paths.');
            return self::SUCCESS;
        }

        $this->info('💾 Registering keys in the database system...');
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $newKeysCount = 0;
        $existingKeysCount = 0;

        foreach ($keys as $key) {
            $bar->setMessage("Key: {$key}");
            
            $isNew = !\App\Models\TranslationKey::where('key', $key)->exists();
            if ($isNew) {
                $newKeysCount++;
            } else {
                $existingKeysCount++;
            }

            $this->registryService->registerKey($key);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Clear the translation cache to ensure the changes take effect immediately
        app(TranslationService::class)->clearCache();

        $duration = microtime(true) - $startTime;
        $scanStats = \Illuminate\Support\Facades\Cache::get('translation_scan_stats', []);
        $languagesCount = \App\Models\Language::count();
        $translationRowsCount = $count * $languagesCount;

        $this->info("✅ Scan complete. {$count} keys processed and synced with active languages.");
        $this->newLine();
        $this->info('📊 Scan Telemetry Report');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Scanned PHP Files', $scanStats['php_files'] ?? 0],
                ['Scanned Blade Files', $scanStats['blade_files'] ?? 0],
                ['Total Files Scanned', $scanStats['total_files'] ?? 0],
                ['Total Discovered Keys', $scanStats['keys_found'] ?? 0],
                ['Duplicate Keys Ignored', max(0, ($scanStats['keys_raw'] ?? 0) - ($scanStats['keys_found'] ?? 0))],
                ['Newly Created Keys', $newKeysCount],
                ['Existing Keys Retained', $existingKeysCount],
                ['Dynamic Warnings Logged', $scanStats['dynamic_warnings'] ?? 0],
                ['Languages Processed', $languagesCount],
                ['Translation Rows Synced', $translationRowsCount],
                ['Execution Duration', number_format($duration, 2) . ' seconds'],
                ['Peak Memory Usage', number_format(memory_get_peak_usage(true) / (1024 * 1024), 2) . ' MB'],
            ]
        );

        return self::SUCCESS;
    }
}
