<?php

namespace App\Console\Commands;

use App\Services\TranslationRegistryService;
use App\Services\TranslationService;
use Illuminate\Console\Command;

class TranslationScanCommand extends Command
{
    protected $signature = 'translation:scan';
    protected $description = 'Scan the codebase build-time and register all discovered translation keys';

    protected TranslationRegistryService $registryService;

    public function __construct(TranslationRegistryService $registryService)
    {
        parent::__construct();
        $this->registryService = $registryService;
    }

    public function handle(): int
    {
        $this->info('🔍 Starting build-time translation scan...');
        
        $keys = $this->registryService->scanCodebase();
        $count = count($keys);

        $this->info("Found {$count} translation keys.");

        foreach ($keys as $key) {
            $this->registryService->registerKey($key);
        }

        app(TranslationService::class)->clearCache();

        $this->info('✅ Scan complete.');
        return self::SUCCESS;
    }
}
