<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Surah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LanguageFutureSupportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Allow syncFromConfig to run during tests
        config(['languages.supported' => [
            'en' => [
                'name' => 'English',
                'native_name' => 'English',
                'direction' => 'ltr',
                'flag' => '🇬🇧',
                'is_active' => true,
                'is_default' => true,
                'order' => 1,
            ],
            'ku' => [
                'name' => 'Kurdish',
                'native_name' => 'کوردی',
                'direction' => 'rtl',
                'flag' => '☀️',
                'is_active' => true,
                'is_default' => false,
                'order' => 2,
            ],
            'ar' => [
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'flag' => '🌙',
                'is_active' => true,
                'is_default' => false,
                'order' => 3,
            ],
        ]]);

        // Force a clean bootstrap state
        Cache::forget('language:config_hash');
        Language::syncFromConfig(true);
        Language::whereNotIn('code', ['en', 'ku', 'ar'])->delete();
    }

    /** @test */
    public function registry_sync_is_idempotent()
    {
        $initialCount = Language::count();
        $this->assertEquals(3, $initialCount);

        // Run sync multiple times
        Language::syncFromConfig(true);
        Language::syncFromConfig(true);

        $this->assertEquals(3, Language::count());
    }

    /** @test */
    public function registry_validation_aborts_if_multiple_default_languages_configured()
    {
        $invalidConfig = config('languages.supported');
        $invalidConfig['ku']['is_default'] = true; // Two defaults (en and ku)
        config(['languages.supported' => $invalidConfig]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Exactly one language must be marked as default');
        Language::syncFromConfig(true);
    }

    /** @test */
    public function registry_validation_aborts_if_zero_default_languages_configured()
    {
        $invalidConfig = config('languages.supported');
        $invalidConfig['en']['is_default'] = false; // Zero defaults
        config(['languages.supported' => $invalidConfig]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Exactly one language must be marked as default');
        Language::syncFromConfig(true);
    }

    /** @test */
    public function removed_languages_are_soft_disabled_not_deleted()
    {
        $newConfig = config('languages.supported');
        unset($newConfig['ku']); // Remove Kurdish from config

        config(['languages.supported' => $newConfig]);
        Language::syncFromConfig(true);

        // Kurdish is still in database but marked as inactive
        $this->assertDatabaseHas('languages', [
            'code' => 'ku',
            'is_active' => false,
        ]);
        // English and Arabic are still active
        $this->assertDatabaseHas('languages', [
            'code' => 'en',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function bootstrap_skips_database_queries_on_hash_matches()
    {
        // Set test environment sync variable
        putenv('LANGUAGE_SYNC_TEST=true');

        // Initial sync to set cached hash
        Language::syncFromConfig(true);

        // Log DB queries
        DB::enableQueryLog();

        // Run sync (which should hit the cached hash check and return immediately)
        Language::syncFromConfig();

        $queryLog = DB::getQueryLog();
        $this->assertEmpty($queryLog, 'Database queries were executed despite config hash matching.');

        DB::disableQueryLog();
        putenv('LANGUAGE_SYNC_TEST');
    }

    /** @test */
    public function new_language_turkish_automatically_binds_to_system_and_saves_translations()
    {
        // Avoid unique constraint violation with pre-seeded data
        Surah::where('number', 112)->delete();

        // 1. Add Turkish 'tr' to config
        $newConfig = config('languages.supported');
        $newConfig['tr'] = [
            'name' => 'Turkish',
            'native_name' => 'Türkçe',
            'direction' => 'ltr',
            'flag' => '🇹🇷',
            'is_active' => true,
            'is_default' => false,
            'order' => 4,
        ];
        config(['languages.supported' => $newConfig]);
        Language::syncFromConfig(true);

        // Assert database record was inserted
        $this->assertDatabaseHas('languages', [
            'code' => 'tr',
            'name' => 'Turkish',
            'is_active' => true,
        ]);

        // 2. Assert Surah model immediately supports Turkish translations dynamically without structural migration
        $surah = Surah::create([
            'number' => 112,
            'revelation_type' => 'meccan',
            'ayah_count' => 4,
            'is_active' => true,
        ]);

        $surah->saveTranslationsFromArray([
            'tr' => ['name' => 'İhlas Suresi'],
            'en' => ['name' => 'Al-Ikhlas'],
        ]);

        $this->assertDatabaseHas('surah_translations', [
            'surah_id' => $surah->id,
            'locale' => 'tr',
            'name' => 'İhlas Suresi',
        ]);

        // 3. Verify fallback is correct
        app()->setLocale('tr');
        $this->assertEquals('İhlas Suresi', $surah->getTranslation('name'));

        // 4. Verify language direction updates immediately affect rendering direction metadata
        $attrs = $surah->getTranslationAttributes('name', 'tr');
        $this->assertEquals('ltr', $attrs['dir']);

        // Update direction to RTL in config
        $newConfig['tr']['direction'] = 'rtl';
        config(['languages.supported' => $newConfig]);
        Language::syncFromConfig(true);

        $attrsRtl = $surah->getTranslationAttributes('name', 'tr');
        $this->assertEquals('rtl', $attrsRtl['dir']);

        // 5. Deactivating Turkish soft-disables it in database
        $newConfig['tr']['is_active'] = false;
        config(['languages.supported' => $newConfig]);
        Language::syncFromConfig(true);

        $this->assertDatabaseHas('languages', [
            'code' => 'tr',
            'is_active' => false,
        ]);
        
        // Dynamic active list no longer includes 'tr'
        $activeCodes = Language::activeCodes();
        $this->assertNotContains('tr', $activeCodes);

        // But translation record still remains intact in database
        $this->assertDatabaseHas('surah_translations', [
            'surah_id' => $surah->id,
            'locale' => 'tr',
            'name' => 'İhlas Suresi',
        ]);
    }

    /** @test */
    public function registry_scales_to_10_and_25_languages()
    {
        // 1. Generate 10 active languages configuration
        $config10 = [];
        $config10['en'] = [
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => true,
            'order' => 1,
        ];
        for ($i = 2; $i <= 10; $i++) {
            $config10["l{$i}"] = [
                'name' => "Language {$i}",
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => false,
                'order' => $i,
            ];
        }

        config(['languages.supported' => $config10]);
        Language::syncFromConfig(true);
        $this->assertEquals(10, Language::active()->count());

        // 2. Scale up to 25 active languages configuration
        $config25 = [];
        $config25['en'] = [
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => true,
            'order' => 1,
        ];
        for ($i = 2; $i <= 25; $i++) {
            $config25["l{$i}"] = [
                'name' => "Language {$i}",
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => false,
                'order' => $i,
            ];
        }

        config(['languages.supported' => $config25]);
        Language::syncFromConfig(true);
        $this->assertEquals(25, Language::active()->count());
    }
}
