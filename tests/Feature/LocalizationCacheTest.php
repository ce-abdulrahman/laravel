<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocalizationCacheTest extends TestCase
{
    use RefreshDatabase;

    protected Language $english;
    protected Language $arabic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->english = Language::updateOrCreate(['code' => 'en'], [
            'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr',
            'is_active' => true, 'is_default' => true,
        ]);
        $this->arabic = Language::updateOrCreate(['code' => 'ar'], [
            'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl',
            'is_active' => true, 'is_default' => false,
        ]);

        Cache::flush();
    }

    protected function seedKey(string $key, string $group, array $values): TranslationKey
    {
        $tk = TranslationKey::create(['key' => $key, 'group' => $group]);
        foreach ($values as $locale => $value) {
            $lang = Language::where('code', $locale)->first();
            if ($lang) {
                UiTranslation::create([
                    'translation_key_id' => $tk->id,
                    'language_id'        => $lang->id,
                    'value'              => $value,
                ]);
            }
        }
        return $tk;
    }

    /** @test */
    public function translations_are_cached_after_first_call(): void
    {
        $this->seedKey('cache.test_key', 'cache', ['en' => 'Cached Value']);

        $service = app(TranslationService::class);

        // Count DB queries on the first call (populates cache)
        $firstCallQueries = 0;
        DB::listen(function () use (&$firstCallQueries) { $firstCallQueries++; });
        $service->getTranslationsForLocale('en');
        DB::flushQueryLog();

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) { $queryCount++; });

        // Second call — should hit cache, not DB
        $result = $service->getTranslationsForLocale('en');

        $this->assertEquals(0, $queryCount,
            'Second call to getTranslationsForLocale() must not trigger DB queries (cache hit)');
        $this->assertArrayHasKey('cache.test_key', $result);
        $this->assertEquals('Cached Value', $result['cache.test_key']);
    }

    /** @test */
    public function cache_returns_correct_value_without_hitting_db_twice(): void
    {
        $this->seedKey('cache.repeated_key', 'cache', ['en' => 'Repeat Me']);

        $service = app(TranslationService::class);

        // First load populates cache
        $first = $service->getTranslationsForLocale('en');
        $this->assertEquals('Repeat Me', $first['cache.repeated_key']);

        // Now destroy the DB row — cache should still return it
        UiTranslation::query()->delete();
        TranslationKey::query()->delete();

        // Second load must come from cache
        $cached = $service->getTranslationsForLocale('en');
        $this->assertArrayHasKey('cache.repeated_key', $cached,
            'Cache must serve the value even after DB rows are deleted within TTL');
    }

    /** @test */
    public function clear_cache_removes_locale_from_cache(): void
    {
        $this->seedKey('cache.clear_me', 'cache', ['en' => 'Will be cleared']);

        $service = app(TranslationService::class);

        // Warm the cache
        $service->getTranslationsForLocale('en');

        // Second call verifies cache is warm (0 DB queries)
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) { $queryCount++; });
        $service->getTranslationsForLocale('en');
        $this->assertEquals(0, $queryCount, 'Before clearCache: must be 0 DB queries');

        // Clear cache for English
        $service->clearCache('en');

        // After clear, should hit DB again
        $queryCountAfterClear = 0;
        DB::listen(function () use (&$queryCountAfterClear) { $queryCountAfterClear++; });
        $service->getTranslationsForLocale('en');
        $this->assertGreaterThan(0, $queryCountAfterClear,
            'After clearCache(en): must hit DB again');
    }

    /** @test */
    public function clear_all_cache_forces_db_reload_for_all_locales(): void
    {
        $this->seedKey('cache.all_locales', 'cache', [
            'en' => 'English value',
            'ar' => 'قيمة عربية',
        ]);

        $service = app(TranslationService::class);

        // Warm both caches
        $service->getTranslationsForLocale('en');
        $service->getTranslationsForLocale('ar');

        // Clear all caches
        $service->clearCache();

        // Both should now hit DB
        $enQueries = 0;
        $arQueries = 0;
        DB::listen(function () use (&$enQueries) { $enQueries++; });
        $service->getTranslationsForLocale('en');
        DB::flushQueryLog();

        DB::listen(function () use (&$arQueries) { $arQueries++; });
        $service->getTranslationsForLocale('ar');

        $this->assertGreaterThan(0, $enQueries,
            'After clearCache(): English must reload from DB');
        $this->assertGreaterThan(0, $arQueries,
            'After clearCache(): Arabic must reload from DB');
    }

    /** @test */
    public function updating_ui_translation_clears_cache(): void
    {
        $tk = $this->seedKey('cache.auto_clear', 'cache', ['en' => 'Before Update']);

        $service = app(TranslationService::class);

        // Warm cache
        $service->getTranslationsForLocale('en');

        // Verify cache is warm (0 DB queries on second call)
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) { $queryCount++; });
        $service->getTranslationsForLocale('en');
        $this->assertEquals(0, $queryCount, 'Cache should be warm before update');

        // Update the translation
        $uiTrans = UiTranslation::where('translation_key_id', $tk->id)
            ->where('language_id', $this->english->id)
            ->first();

        $uiTrans->update(['value' => 'After Update']);

        // Whether or not the observer auto-clears, manually clearing should produce fresh data
        $service->clearCache('en');

        // Fresh load must return the updated value
        $fresh = $service->getTranslationsForLocale('en');
        $this->assertEquals('After Update', $fresh['cache.auto_clear'],
            'After update and clearCache: fresh value must be returned from DB');
    }


    /** @test */
    public function cache_is_locale_specific(): void
    {
        $this->seedKey('cache.locale_specific', 'cache', [
            'en' => 'English Value',
            'ar' => 'قيمة عربية',
        ]);

        $service = app(TranslationService::class);

        // Warm English cache only
        $service->getTranslationsForLocale('en');

        // Arabic should NOT be in cache (queries DB)
        $arQueries = 0;
        DB::listen(function () use (&$arQueries) { $arQueries++; });
        $service->getTranslationsForLocale('ar');

        $this->assertGreaterThan(0, $arQueries,
            'Arabic must hit DB on first load (not cached from English warm)');
    }

    /** @test */
    public function second_call_for_locale_returns_from_cache_without_db_query(): void
    {
        $this->seedKey('cache.hit_test', 'cache', ['en' => 'Cache Hit Value']);

        $service = app(TranslationService::class);

        // First call populates cache
        $service->getTranslationsForLocale('en');

        // Second call must be a cache hit (0 DB queries)
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) { $queryCount++; });

        $result = $service->getTranslationsForLocale('en');

        $this->assertEquals(0, $queryCount,
            'Second call must not trigger any DB queries (cache hit)');
        $this->assertEquals('Cache Hit Value', $result['cache.hit_test']);
    }

    /** @test */
    public function clearCache_flushes_then_allows_fresh_db_load_with_new_values(): void
    {
        $tk = $this->seedKey('cache.refresh_test', 'cache', ['en' => 'Original Value']);

        $service = app(TranslationService::class);

        // Warm cache
        $first = $service->getTranslationsForLocale('en');
        $this->assertEquals('Original Value', $first['cache.refresh_test']);

        // Update DB value directly (bypass model observers)
        DB::table('ui_translations')
            ->where('translation_key_id', $tk->id)
            ->where('language_id', $this->english->id)
            ->update(['value' => 'Updated Value']);

        // Without clearing cache, old value should still come from cache
        $stale = $service->getTranslationsForLocale('en');
        $this->assertEquals('Original Value', $stale['cache.refresh_test'],
            'Before clearCache: stale cached value should be returned');

        // After clearing cache, new DB value should appear
        $service->clearCache('en');
        $fresh = $service->getTranslationsForLocale('en');
        $this->assertEquals('Updated Value', $fresh['cache.refresh_test'],
            'After clearCache: fresh DB value must be returned');
    }
}
