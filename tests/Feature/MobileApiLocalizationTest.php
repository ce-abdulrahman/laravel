<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Surah;
use App\Models\Ayah;
use App\Models\Translation;
use App\Models\SurahTranslation;
use App\Models\TajweedRule;
use App\Models\TajweedRuleTranslation;
use App\Models\AyahTajweedSegment;
use App\Services\QuranApiCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MobileApiLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear cache before each test
        Cache::flush();
        QuranApiCache::clearAllLocales();
    }

    public function test_api_locale_resolution_priorities_and_fallbacks(): void
    {
        // Setup a test surah and translations
        $surah = Surah::firstOrFail();
        
        // Seed Turkish Language
        $trLang = Language::firstOrCreate(['code' => 'tr'], [
            'name' => 'Turkish',
            'native_name' => 'Türkçe',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => false,
            'order' => 10
        ]);

        SurahTranslation::updateOrCreate(
            ['surah_id' => $surah->id, 'locale' => 'tr'],
            ['name' => 'Turkish Surah Name']
        );

        SurahTranslation::updateOrCreate(
            ['surah_id' => $surah->id, 'locale' => 'en'],
            ['name' => 'English Surah Name']
        );

        // 1. Accept-Language Priority
        $response = $this->withHeaders([
            'Accept-Language' => 'tr',
        ])->getJson('/api/surahs');

        $response->assertStatus(200);
        $this->assertEquals('Turkish Surah Name', $response->json('data.0.name'));

        // 2. Query parameter Priority overrides Accept-Language
        $response = $this->withHeaders([
            'Accept-Language' => 'tr',
        ])->getJson('/api/surahs?locale=en');

        $response->assertStatus(200);
        $this->assertEquals('English Surah Name', $response->json('data.0.name'));

        // 3. Fallback on invalid locale to default/first active (English)
        $this->flushHeaders();
        $response = $this->getJson('/api/surahs?locale=xyz');
        $response->assertStatus(200);
        $this->assertEquals('English Surah Name', $response->json('data.0.name'));
    }

    public function test_api_cache_isolation_per_locale_and_versioning(): void
    {
        $surah = Surah::firstOrFail();
        
        // Seed Turkish Language
        $trLang = Language::firstOrCreate(['code' => 'tr'], [
            'name' => 'Turkish',
            'native_name' => 'Türkçe',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => false,
            'order' => 10
        ]);

        SurahTranslation::updateOrCreate(
            ['surah_id' => $surah->id, 'locale' => 'tr'],
            ['name' => 'Turkish Surah Name']
        );

        SurahTranslation::updateOrCreate(
            ['surah_id' => $surah->id, 'locale' => 'en'],
            ['name' => 'English Surah Name']
        );

        // Fetch en locale (will cache it)
        $responseEn = $this->getJson('/api/surahs?locale=en');
        $responseEn->assertStatus(200);
        $this->assertEquals('English Surah Name', $responseEn->json('data.0.name'));

        // Fetch tr locale (should get tr translation, proving cache isolation)
        $responseTr = $this->getJson('/api/surahs?locale=tr');
        $responseTr->assertStatus(200);
        $this->assertEquals('Turkish Surah Name', $responseTr->json('data.0.name'));

        // Verify cache keys exist in Cache store
        $version = QuranApiCache::getGlobalVersion();
        $this->assertTrue(Cache::has(QuranApiCache::KEY_SURAHS . '.en.' . $version));
        $this->assertTrue(Cache::has(QuranApiCache::KEY_SURAHS . '.tr.' . $version));

        // Test cache busting on translation updates
        SurahTranslation::updateOrCreate(
            ['surah_id' => $surah->id, 'locale' => 'tr'],
            ['name' => 'Updated Turkish Surah Name']
        );

        // Fetch again, should show updated translation immediately due to version increment / cache busting
        $responseTrUpdated = $this->getJson('/api/surahs?locale=tr');
        $responseTrUpdated->assertStatus(200);
        $this->assertEquals('Updated Turkish Surah Name', $responseTrUpdated->json('data.0.name'));
    }

    public function test_ayahs_endpoint_localizations_fallbacks_and_field_selection(): void
    {
        $surah = Surah::firstOrFail();
        $ayah = $surah->ayahs()->firstOrFail();

        // Seed Turkish Language
        $trLang = Language::firstOrCreate(['code' => 'tr'], [
            'name' => 'Turkish',
            'native_name' => 'Türkçe',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => false,
            'order' => 10
        ]);

        // Seed Turkish translation for Ayah
        Translation::updateOrCreate(
            ['ayah_id' => $ayah->id, 'language_code' => 'tr'],
            [
                'content' => 'Turkish Ayah Translation',
                'translator_name' => 'TR Translator',
                'is_active' => true,
                'is_default' => false
            ]
        );

        // Seed English translation for Ayah
        Translation::updateOrCreate(
            ['ayah_id' => $ayah->id, 'language_code' => 'en'],
            [
                'content' => 'English Ayah Translation',
                'translator_name' => 'EN Translator',
                'is_active' => true,
                'is_default' => true
            ]
        );

        // Setup a Tajweed rule and segment with Turkish translation
        $rule = TajweedRule::firstOrFail();
        TajweedRuleTranslation::updateOrCreate(
            ['tajweed_rule_id' => $rule->id, 'locale' => 'tr'],
            ['name' => 'Turkish Rule Name']
        );
        TajweedRuleTranslation::updateOrCreate(
            ['tajweed_rule_id' => $rule->id, 'locale' => 'en'],
            ['name' => 'English Rule Name']
        );

        $segment = new AyahTajweedSegment([
            'ayah_id' => $ayah->id,
            'tajweed_rule_id' => $rule->id,
            'text_segment' => 'Segment',
            'start_index' => 0,
            'end_index' => 5
        ]);
        $segment->surah_id = $surah->id;
        $segment->save();

        // Request with tr locale
        $response = $this->getJson("/api/surahs/{$surah->number}/ayahs?locale=tr");
        $response->assertStatus(200);

        // Verify translations
        $response->assertJsonPath('data.ayahs.0.translation', 'Turkish Ayah Translation');
        $response->assertJsonPath('data.ayahs.0.text_tr', 'Turkish Ayah Translation');
        $response->assertJsonPath('data.ayahs.0.text_en', 'English Ayah Translation');
        
        // Verify tajweed rule dynamic translation
        $this->assertEquals('Turkish Rule Name', $response->json('data.ayahs.0.tajweed_segments.0.rule.name_tr'));
        $this->assertEquals('English Rule Name', $response->json('data.ayahs.0.tajweed_segments.0.rule.name_en'));

        // Request with fields filter
        $responseFields = $this->getJson("/api/surahs/{$surah->number}/ayahs?locale=tr&fields=id,ayah_number,translation");
        $responseFields->assertStatus(200);
        
        // Verify only selected fields are returned in ayahs
        $ayahJson = $responseFields->json('data.ayahs.0');
        $this->assertArrayHasKey('id', $ayahJson);
        $this->assertArrayHasKey('ayah_number', $ayahJson);
        $this->assertArrayHasKey('translation', $ayahJson);
        $this->assertArrayNotHasKey('text_uthmani', $ayahJson);
        $this->assertArrayNotHasKey('text_tr', $ayahJson);
        $this->assertArrayNotHasKey('tajweed_segments', $ayahJson);
    }
}
