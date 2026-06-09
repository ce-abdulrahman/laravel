<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TranslationSyncService
{
    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Pull translations from a remote server and apply conflict strategy.
     */
    public function syncPull(string $remoteUrl, string $strategy = 'latest_wins'): array
    {
        try {
            $response = Http::withHeaders([
                'X-Translation-Sync-Token' => config('translations.sync_token')
            ])->timeout(15)->get($remoteUrl);
            
            if (!$response->successful()) {
                throw new \RuntimeException("Failed to fetch remote translations: Code " . $response->status());
            }
            
            $data = $response->json();
            if (!is_array($data) || !isset($data['translations'])) {
                throw new \RuntimeException("Invalid response format from remote synchronization source.");
            }
            
            $pulled = 0;
            $updated = 0;
            $conflicts = 0;
            $skipped = 0;
            
            $remoteTranslations = $data['translations'];
            
            UiTranslation::$currentChangeSource = 'sync';
            
            // Map languages for performance
            $languages = Language::all()->pluck('id', 'code')->toArray();
            
            // Loop and process
            foreach ($remoteTranslations as $remote) {
                $keyStr = $remote['key'] ?? null;
                $locale = $remote['locale'] ?? null;
                $value = $remote['value'] ?? null;
                $remoteUpdatedAt = $remote['updated_at'] ?? null;
                
                if (!$keyStr || !$locale) {
                    $skipped++;
                    continue;
                }
                
                // Get or create Language record if it exists
                $languageId = $languages[$locale] ?? null;
                if (!$languageId) {
                    $skipped++;
                    continue; // Skip if local language is not supported
                }
                
                // Get or create TranslationKey
                $keyRecord = TranslationKey::firstOrCreate(
                    ['key' => $keyStr],
                    ['group' => explode('.', $keyStr)[0] ?? 'general']
                );
                
                // Get local UiTranslation if exists
                $localTranslation = UiTranslation::where('translation_key_id', $keyRecord->id)
                    ->where('language_id', $languageId)
                    ->first();
                
                if (!$localTranslation) {
                    // Create new
                    UiTranslation::create([
                        'translation_key_id' => $keyRecord->id,
                        'language_id' => $languageId,
                        'value' => $value,
                        'is_auto_generated' => false,
                    ]);
                    $pulled++;
                } else {
                    // Compare values
                    if ($localTranslation->value === $value) {
                        $skipped++;
                        continue;
                    }
                    
                    // Apply strategy
                    $applyRemote = false;
                    
                    if ($strategy === 'remote_wins') {
                        $applyRemote = true;
                    } elseif ($strategy === 'local_wins') {
                        $applyRemote = false;
                    } elseif ($strategy === 'latest_wins') {
                        $localTime = $localTranslation->updated_at ? $localTranslation->updated_at->timestamp : 0;
                        $remoteTime = $remoteUpdatedAt ? strtotime($remoteUpdatedAt) : 0;
                        if ($remoteTime > $localTime) {
                            $applyRemote = true;
                        } else {
                            $conflicts++;
                        }
                    }
                    
                    if ($applyRemote) {
                        $localTranslation->update([
                            'value' => $value,
                            'is_auto_generated' => false,
                        ]);
                        $updated++;
                    } else {
                        $skipped++;
                    }
                }
            }
            
            UiTranslation::$currentChangeSource = 'manual';
            
            // Clear all caches
            foreach (array_keys($languages) as $code) {
                $this->translationService->clearCache($code);
            }
            
            return [
                'success' => true,
                'pulled' => $pulled,
                'updated' => $updated,
                'conflicts' => $conflicts,
                'skipped' => $skipped
            ];
            
        } catch (\Exception $e) {
            Log::error("Translation Sync Pull error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Push current translations to a remote server.
     */
    public function syncPush(string $remoteUrl): array
    {
        try {
            // Compile all translation keys and values
            $translations = DB::table('ui_translations')
                ->join('translation_keys', 'ui_translations.translation_key_id', '=', 'translation_keys.id')
                ->join('languages', 'ui_translations.language_id', '=', 'languages.id')
                ->select(
                    'translation_keys.key',
                    'languages.code as locale',
                    'ui_translations.value',
                    'ui_translations.updated_at'
                )
                ->get()
                ->map(function ($item) {
                    return [
                        'key' => $item->key,
                        'locale' => $item->locale,
                        'value' => $item->value,
                        'updated_at' => $item->updated_at
                    ];
                })
                ->toArray();
                
            $response = Http::withHeaders([
                'X-Translation-Sync-Token' => config('translations.sync_token')
            ])->timeout(15)->post($remoteUrl, [
                'translations' => $translations,
                'source_environment' => config('app.env')
            ]);
            
            if (!$response->successful()) {
                throw new \RuntimeException("Failed to push translations: Code " . $response->status());
            }
            
            return [
                'success' => true,
                'pushed_count' => count($translations),
                'remote_response' => $response->json()
            ];
            
        } catch (\Exception $e) {
            Log::error("Translation Sync Push error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
