<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Language;
use App\Models\Surah;
use App\Models\SurahTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TranslationFinalVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * Test dynamic language registration and automated binding.
     */
    public function test_dynamic_turkish_registration_binds_seamlessly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Add new language (Turkish) dynamically
        $trLanguage = Language::firstOrCreate(['code' => 'tr'], [
            'name' => 'Turkish',
            'native_name' => 'Türkçe',
            'direction' => 'ltr',
            'flag' => '🇹🇷',
            'is_active' => true,
            'is_default' => false,
            'order' => 10,
        ]);

        // 2. Language instantly appears in the active codes cache
        $this->assertContains('tr', Language::activeCodes());

        // 3. Create translation for a Surah
        $surah = Surah::orderBy('number')->first();
        $surahTranslation = SurahTranslation::create([
            'surah_id' => $surah->id,
            'locale' => 'tr',
            'name' => 'Fatiha Suresi',
        ]);

        // 4. API resolves tr dynamically
        // Accept-Language header
        $response = $this->withHeaders(['Accept-Language' => 'tr'])->getJson('/api/surahs');
        $response->assertStatus(200);

        // Verify dynamic localized response payload
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        
        $firstSurah = $data[0];
        // Suffix mapping should appear dynamically without hardcoded files
        $this->assertArrayHasKey('name_tr', $firstSurah);
        $this->assertEquals('Fatiha Suresi', $firstSurah['name_tr']);
        
        // Dynamic name resolution should match request locale
        $this->assertEquals('Fatiha Suresi', $firstSurah['name']);

        // 5. Query string ?locale=tr resolves identically
        $responseQuery = $this->getJson('/api/surahs?locale=tr');
        $responseQuery->assertStatus(200);
        $firstSurahQuery = $responseQuery->json('data.0');
        $this->assertEquals('Fatiha Suresi', $firstSurahQuery['name']);
    }

    /**
     * Test validation safety and nested translation array processing on dynamic locales.
     */
    public function test_dynamic_validation_saves_nested_translation_without_controller_edits(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create Turkish language
        $trLanguage = Language::firstOrCreate(['code' => 'tr'], [
            'name' => 'Turkish',
            'native_name' => 'Türkçe',
            'direction' => 'ltr',
            'flag' => '🇹🇷',
            'is_active' => true,
            'is_default' => false,
            'order' => 10,
        ]);

        // Post Surah Update with nested translations array containing Turkish translations
        // No validation or controller changes required. We submit all locales dynamically.
        $surah = Surah::orderBy('number')->first();
        
        // Prepare dynamic translation inputs matching active registry (en, ku, ar, tr)
        $translations = [];
        foreach (Language::activeList() as $lang) {
            $translations[$lang->code] = [
                'name' => ($lang->code === 'tr') ? 'Türkçe Fatiha' : "Name in {$lang->code}",
            ];
        }

        $response = $this->actingAs($admin)->put(route('surahs.update', $surah->id), [
            'number' => $surah->number,
            'revelation_type' => $surah->revelation_type ?? 'meccan',
            'ayah_count' => $surah->ayah_count ?? 7,
            'is_active' => true,
            'translations' => $translations,
        ]);

        $response->assertRedirect(route('surahs.show', $surah->id));

        // Assert Turkish translation saved successfully in database
        $this->assertDatabaseHas('surah_translations', [
            'surah_id' => $surah->id,
            'locale' => 'tr',
            'name' => 'Türkçe Fatiha',
        ]);
    }

    /**
     * Verify N+1 query prevention: fetching surahs runs constant number of database queries.
     */
    public function test_no_n_plus_one_queries_when_retrieving_translations(): void
    {
        // Add multiple languages
        foreach (['tr', 'es', 'fr', 'de'] as $code) {
            Language::firstOrCreate(['code' => $code], [
                'name' => ucfirst($code),
                'native_name' => ucfirst($code),
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => false,
                'order' => 10,
            ]);
        }

        // Count queries needed for API retrieval
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->getJson('/api/surahs?locale=tr');
        $response->assertStatus(200);

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // 1 query for default setting cache fallback (if not cached)
        // 1 query for Language list (if cache misses)
        // 1 query for fetching Surahs
        // 1 query for eager-loaded Surah translations (single IN query for all surahs instead of N)
        // Verify query count is small, indicating eager-loading of translation works
        $this->assertLessThan(10, $queryCount, "N+1 queries detected: Total queries executed was {$queryCount}");
    }
}
