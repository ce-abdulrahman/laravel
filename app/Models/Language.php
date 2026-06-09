<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'native_name',
        'direction',
        'flag',
        'is_active',
        'is_default',
        'order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
        'order'      => 'integer',
    ];

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('code');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    public function getIsRtlAttribute(): bool
    {
        return $this->isRtl();
    }

    public function getTextAlignAttribute(): string
    {
        return $this->isRtl() ? 'right' : 'left';
    }

    public function getAlignClassAttribute(): string
    {
        return $this->isRtl() ? 'text-end' : 'text-start';
    }

    public function getTypographyClassAttribute(): string
    {
        return $this->isRtl() ? 'arabic-text' : '';
    }

    // ─── Static Helpers (cached) ─────────────────────────────────────────────────

    /**
     * Return all active locale codes. Cached to prevent N+1 across every request.
     */
    public static function activeCodes(): array
    {
        return Cache::remember('language:active_codes', 3600, function () {
            return static::active()->ordered()->pluck('code')->toArray();
        });
    }

    /**
     * Return the full active language list. Cached.
     */
    public static function activeList(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('language:active_list', 3600, function () {
            return static::active()->ordered()->get();
        });
    }

    /**
     * Return the default language model. Cached.
     */
    public static function default(): ?Language
    {
        return Cache::remember('language:default', 3600, function () {
            return static::where('is_default', true)->first() ?? static::first();
        });
    }

    /**
     * Synchronize languages table from config('languages.supported').
     * Uses config hash tracking to prevent database queries on every request.
     */
    public static function syncFromConfig(bool $force = false): void
    {
        // 1. Skip sync during early console boots where DB isn't bound or required
        if (app()->runningInConsole() && !$force) {
            $isSeeding = app()->bound('db') && (
                strpos(implode(' ', $_SERVER['argv'] ?? []), 'db:seed') !== false ||
                strpos(implode(' ', $_SERVER['argv'] ?? []), 'migrate') !== false
            );
            $isTestForced = env('LANGUAGE_SYNC_TEST') === true || env('LANGUAGE_SYNC_TEST') === 'true';

            if (!$isSeeding && !$isTestForced) {
                return;
            }
        }

        try {
            $configLanguages = config('languages.supported', []);
            if (empty($configLanguages)) {
                return;
            }

            // Compute and verify config checksum hash to skip DB reads on cache hits
            $configHash = md5(json_encode($configLanguages));
            $cachedHash = Cache::get('language:config_hash');

            if (!$force && $cachedHash === $configHash) {
                return;
            }

            if (!Schema::hasTable('languages')) {
                return;
            }

            // 2. Validate exactly one default language is configured
            $defaultCount = 0;
            foreach ($configLanguages as $langData) {
                if (!empty($langData['is_default'])) {
                    $defaultCount++;
                }
            }
            if ($defaultCount !== 1) {
                throw new \Exception("Language registry synchronization failed: Exactly one language must be marked as default. Found: {$defaultCount}.");
            }

            // 4. Run sync inside transaction
            DB::transaction(function () use ($configLanguages, $configHash) {
                $dbCodes = DB::table('languages')->pluck('code')->toArray();
                $configCodes = array_keys($configLanguages);

                // Insert missing languages
                $missingCodes = array_diff($configCodes, $dbCodes);
                foreach ($missingCodes as $code) {
                    $data = $configLanguages[$code];
                    DB::table('languages')->insert([
                        'code'        => $code,
                        'name'        => $data['name'],
                        'native_name' => $data['native_name'] ?? $data['name'],
                        'direction'   => $data['direction'] ?? 'ltr',
                        'flag'        => $data['flag'] ?? null,
                        'is_active'   => $data['is_active'] ?? true,
                        'is_default'  => $data['is_default'] ?? false,
                        'order'       => $data['order'] ?? 0,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                // Update existing languages (idempotent updates)
                $existingCodes = array_intersect($configCodes, $dbCodes);
                foreach ($existingCodes as $code) {
                    $data = $configLanguages[$code];
                    DB::table('languages')->where('code', $code)->update([
                        'name'        => $data['name'],
                        'native_name' => $data['native_name'] ?? $data['name'],
                        'direction'   => $data['direction'] ?? 'ltr',
                        'flag'        => $data['flag'] ?? null,
                        'is_active'   => $data['is_active'] ?? true,
                        'is_default'  => $data['is_default'] ?? false,
                        'order'       => $data['order'] ?? 0,
                        'updated_at'  => now(),
                    ]);
                }

                // Soft-disable removed languages to preserve history and analytics
                DB::table('languages')->whereNotIn('code', $configCodes)->update([
                    'is_active'  => false,
                    'is_default' => false,
                    'updated_at' => now(),
                ]);

                // Clear registry, translation, analytics, and API caches
                Cache::forget('language:active_codes');
                Cache::forget('language:active_list');
                Cache::forget('language:default');

                if (class_exists(\App\Services\QuranApiCache::class)) {
                    \App\Services\QuranApiCache::incrementGlobalVersion();
                    \App\Services\QuranApiCache::clearAllLocales();
                }

                try {
                    if (app()->bound(\App\Services\TranslationService::class)) {
                        resolve(\App\Services\TranslationService::class)->clearCache();
                    }
                } catch (\Exception $e) {}

                // Save new config hash
                Cache::forever('language:config_hash', $configHash);
            });
        } catch (\Exception $e) {
            // Re-throw the default registry errors to abort boot/seed when validation fails
            if (str_contains($e->getMessage(), 'Exactly one language must be marked as default')) {
                throw $e;
            }
            // Silent fallback for schema/early DB connection issues
        }
    }

    /**
     * Bust language caches whenever a language is saved or deleted.
     */
    protected static function booted(): void
    {
        $bust = function () {
            Cache::forget('language:active_codes');
            Cache::forget('language:active_list');
            Cache::forget('language:default');
            if (class_exists(\App\Services\QuranApiCache::class)) {
                \App\Services\QuranApiCache::clearAllLocales();
            }
            try {
                if (app()->bound(\App\Services\TranslationService::class)) {
                    resolve(\App\Services\TranslationService::class)->clearCache();
                }
            } catch (\Exception $e) {}
        };
        static::saved($bust);
        static::deleted($bust);
    }
}
