<?php

namespace Tests\Feature;

use App\Models\Adhkar;
use App\Models\AdhkarCategory;
use App\Models\Hadith;
use App\Models\HadithCategory;
use App\Models\Language;
use App\Models\Surah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataTableRefactoringTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Language $ku;
    protected Language $en;
    protected Language $ar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        // Clear tables safely to avoid conflicts with seeded data from other tests
        \App\Models\SurahTranslation::query()->delete();
        Surah::query()->delete();
        \App\Models\HadithTranslation::query()->delete();
        Hadith::query()->delete();
        HadithCategory::query()->delete();
        \App\Models\AdhkarTranslation::query()->delete();
        Adhkar::query()->delete();
        AdhkarCategory::query()->delete();
        Language::query()->delete();

        // Seed languages
        $this->ku = Language::create([
            'code' => 'ku',
            'name' => 'Kurdish',
            'native_name' => 'Kurdî',
            'direction' => 'rtl',
            'flag' => 'ku.png',
            'is_active' => true,
            'is_default' => true,
            'order' => 1,
        ]);

        $this->en = Language::create([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'flag' => 'en.png',
            'is_active' => true,
            'is_default' => false,
            'order' => 2,
        ]);

        $this->ar = Language::create([
            'code' => 'ar',
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'direction' => 'rtl',
            'flag' => 'ar.png',
            'is_active' => true,
            'is_default' => false,
            'order' => 3,
        ]);

        Language::booted(); // Bust language registry caches
    }

    /** @test */
    public function surah_index_renders_dynamic_name_columns()
    {
        $surah = Surah::create([
            'number' => 1,
            'revelation_type' => 'meccan',
            'ayah_count' => 7,
            'name_ar' => 'الفاتحة', // legacy fallback
        ]);

        $surah->translations()->create(['locale' => 'ku', 'name' => 'فاتحە']);
        $surah->translations()->create(['locale' => 'en', 'name' => 'The Opening']);

        $this->actingAs($this->admin);

        // 1. Visit with Kurdish locale
        app()->setLocale('ku');
        session(['locale' => 'ku']);
        $response = $this->get(route('surahs.index'));
        $response->assertOk();
        $response->assertSee('فاتحە');
        $response->assertSee('The Opening');

        // Verify that headers exist for each active language
        $response->assertSee('<th>Name (Kurdish)</th>', false);
        $response->assertSee('<th>Name (English)</th>', false);
        $response->assertSee('<th>Name (Arabic)</th>', false);
    }

    /** @test */
    public function translation_resolution_fallback_is_correct()
    {
        $surah = Surah::create([
            'number' => 2,
            'revelation_type' => 'meccan',
            'ayah_count' => 286,
            'name_ar' => 'البقرة',
        ]);

        // Only create English translation (missing Kurdish translation)
        $surah->translations()->create(['locale' => 'en', 'name' => 'The Cow']);

        $this->actingAs($this->admin);

        // Under Kurdish locale, should fallback to English translation
        app()->setLocale('ku');
        session(['locale' => 'ku']);

        $attrs = $surah->getTranslationAttributes('name');
        $this->assertEquals('The Cow', $attrs['value']);
        $this->assertEquals('ltr', $attrs['dir']);

        $response = $this->get(route('surahs.index'));
        $response->assertOk();
        $response->assertSee('The Cow');
    }

    /** @test */
    public function search_scope_filters_strictly_by_resolved_locale_value()
    {
        $surah1 = Surah::create(['number' => 1, 'revelation_type' => 'meccan', 'ayah_count' => 7, 'name_ar' => 'الفاتحة']);
        $surah1->translations()->create(['locale' => 'ku', 'name' => 'فاتحە']);
        $surah1->translations()->create(['locale' => 'en', 'name' => 'The Opening']);

        $surah2 = Surah::create(['number' => 2, 'revelation_type' => 'medinan', 'ayah_count' => 286, 'name_ar' => 'البقرة']);
        $surah2->translations()->create(['locale' => 'ku', 'name' => 'مانگا']);
        $surah2->translations()->create(['locale' => 'en', 'name' => 'The Cow']);

        app()->setLocale('ku');

        // Search for "فاتحە" should return surah1
        $results = Surah::whereTranslationLike('name', 'فاتحە')->get();
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results->first()->number);

        // Search for "مانگا" should return surah2
        $results = Surah::whereTranslationLike('name', 'مانگا')->get();
        $this->assertCount(1, $results);
        $this->assertEquals(2, $results->first()->number);

        // Search for "Opening" in English while locale is English should find surah1
        app()->setLocale('en');
        $results = Surah::whereTranslationLike('name', 'Opening')->get();
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results->first()->number);
    }

    /** @test */
    public function order_by_translation_scope_works_correctly()
    {
        $surah1 = Surah::create(['number' => 1, 'revelation_type' => 'meccan', 'ayah_count' => 7, 'name_ar' => 'الفاتحة']);
        $surah1->translations()->create(['locale' => 'en', 'name' => 'B']);

        $surah2 = Surah::create(['number' => 2, 'revelation_type' => 'medinan', 'ayah_count' => 286, 'name_ar' => 'البقرة']);
        $surah2->translations()->create(['locale' => 'en', 'name' => 'A']);

        app()->setLocale('en');

        // Order ascending: should return A (surah 2) then B (surah 1)
        $resultsAsc = Surah::orderByTranslation('name', 'asc')->get();
        $this->assertEquals(2, $resultsAsc->get(0)->number);
        $this->assertEquals(1, $resultsAsc->get(1)->number);

        // Order descending: should return B (surah 1) then A (surah 2)
        $resultsDesc = Surah::orderByTranslation('name', 'desc')->get();
        $this->assertEquals(1, $resultsDesc->get(0)->number);
        $this->assertEquals(2, $resultsDesc->get(1)->number);
    }

    /** @test */
    public function hadiths_index_renders_dynamic_translation_columns()
    {
        $cat = HadithCategory::create(['order' => 1, 'is_active' => true]);
        $cat->translations()->create(['locale' => 'en', 'name' => 'Faith']);

        $hadith = Hadith::create([
            'category_id' => $cat->id,
            'arabic_text' => 'العلم نور',
            'order' => 1,
            'is_active' => true,
        ]);
        $hadith->translations()->create(['locale' => 'ku', 'translation' => 'زانست ڕووناکییە']);
        $hadith->translations()->create(['locale' => 'en', 'translation' => 'Knowledge is light']);

        $this->actingAs($this->admin);

        // Visit in Kurdish
        app()->setLocale('ku');
        session(['locale' => 'ku']);
        $response = $this->get(route('hadiths.index'));
        $response->assertOk();
        $response->assertSee('زانست ڕووناکییە');
        $response->assertSee('Knowledge is light');

        // Verify category name displays dynamic translated name
        $response->assertSee('Faith'); // fell back to English
    }

    /** @test */
    public function adhkars_index_renders_dynamic_translation_columns()
    {
        $cat = AdhkarCategory::create(['order' => 1, 'is_active' => true]);
        $cat->translations()->create(['locale' => 'ku', 'name' => 'بەیانیان']);

        $adhkar = Adhkar::create([
            'category_id' => $cat->id,
            'arabic_text' => 'الحمد لله',
            'count' => 33,
            'order' => 1,
            'is_active' => true,
        ]);
        $adhkar->translations()->create(['locale' => 'ku', 'translation' => 'سوپاس بۆ خودا']);
        $adhkar->translations()->create(['locale' => 'en', 'translation' => 'Praise be to Allah']);

        $this->actingAs($this->admin);

        // Visit in English
        app()->setLocale('en');
        session(['locale' => 'en']);
        $response = $this->get(route('adhkars.index'));
        $response->assertOk();
        $response->assertSee('Praise be to Allah');
        $response->assertSee('سوپاس بۆ خودا');

        // Category falls back to Kurdish
        $response->assertSee('بەیانیان');
    }
}
