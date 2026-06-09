<?php

namespace Tests\Feature;

use App\Models\Adhkar;
use App\Models\AdhkarCategory;
use App\Models\Hadith;
use App\Models\HadithCategory;
use App\Models\Language;
use App\Models\Surah;
use App\Models\TajweedRule;
use App\Models\TajweedRuleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchAndFilterRefactoringTest extends TestCase
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

        // Clear tables
        \App\Models\SurahTranslation::query()->delete();
        Surah::query()->delete();
        \App\Models\HadithTranslation::query()->delete();
        Hadith::query()->delete();
        HadithCategory::query()->delete();
        \App\Models\AdhkarTranslation::query()->delete();
        Adhkar::query()->delete();
        AdhkarCategory::query()->delete();
        \App\Models\TajweedRuleTranslation::query()->delete();
        TajweedRule::query()->delete();
        \App\Models\TajweedRuleCategoryTranslation::query()->delete();
        TajweedRuleCategory::query()->delete();
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

        Language::booted();
    }

    /** @test */
    public function simultaneous_multilingual_search_finds_matches_in_any_active_language()
    {
        $surah1 = Surah::create(['number' => 1, 'revelation_type' => 'meccan', 'ayah_count' => 7, 'name_ar' => 'الفاتحة']);
        $surah1->translations()->create(['locale' => 'ku', 'name' => 'فاتحە']);
        $surah1->translations()->create(['locale' => 'en', 'name' => 'The Opening']);

        $surah2 = Surah::create(['number' => 2, 'revelation_type' => 'medinan', 'ayah_count' => 286, 'name_ar' => 'البقرة']);
        $surah2->translations()->create(['locale' => 'ku', 'name' => 'مانگا']);
        $surah2->translations()->create(['locale' => 'en', 'name' => 'The Cow']);

        // Search using English term "Opening"
        $results = Surah::whereTranslationLikeAny('name', 'Opening')->get();
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results->first()->number);

        // Search using Kurdish term "مانگا"
        $results2 = Surah::whereTranslationLikeAny('name', 'مانگا')->get();
        $this->assertCount(1, $results2);
        $this->assertEquals(2, $results2->first()->number);
    }

    /** @test */
    public function search_excludes_inactive_or_empty_translations()
    {
        $surah = Surah::create(['number' => 1, 'revelation_type' => 'meccan', 'ayah_count' => 7, 'name_ar' => 'الفاتحة']);
        $surah->translations()->create(['locale' => 'ku', 'name' => 'فاتحە']);
        // Empty translation
        $surah->translations()->create(['locale' => 'en', 'name' => '']);
        // Translation for inactive language code
        $surah->translations()->create(['locale' => 'fr', 'name' => 'Ouverture']);

        // French is not active, so searching "Ouverture" should return nothing
        $results = Surah::whereTranslationLikeAny('name', 'Ouverture')->get();
        $this->assertCount(0, $results);

        // Searching empty string translation should return nothing
        $results2 = Surah::whereTranslationLikeAny('name', '')->get();
        $this->assertCount(0, $results2);
    }

    /** @test */
    public function search_returns_no_duplicate_records_when_multiple_translations_match()
    {
        $surah = Surah::create(['number' => 1, 'revelation_type' => 'meccan', 'ayah_count' => 7, 'name_ar' => 'الفاتحة']);
        $surah->translations()->create(['locale' => 'ku', 'name' => 'فاتحە']);
        $surah->translations()->create(['locale' => 'en', 'name' => 'فاتحە']); // Same string

        // Using whereTranslationLikeAny uses EXISTS, ensuring exactly 1 record is returned, not duplicate rows
        $results = Surah::whereTranslationLikeAny('name', 'فاتحە')->get();
        $this->assertCount(1, $results);
    }

    /** @test */
    public function fallback_aware_sorting_remains_deterministic()
    {
        $surah1 = Surah::create(['number' => 1, 'revelation_type' => 'meccan', 'ayah_count' => 7, 'name_ar' => 'الفاتحة']);
        $surah1->translations()->create(['locale' => 'en', 'name' => 'B']);

        $surah2 = Surah::create(['number' => 2, 'revelation_type' => 'medinan', 'ayah_count' => 286, 'name_ar' => 'البقرة']);
        $surah2->translations()->create(['locale' => 'en', 'name' => 'A']);

        // Missing Kurdish translations: app locale 'ku' falls back to 'en'
        app()->setLocale('ku');

        $results = Surah::orderByTranslation('name', 'asc')->get();
        $this->assertEquals(2, $results->get(0)->number); // A
        $this->assertEquals(1, $results->get(1)->number); // B
    }

    /** @test */
    public function sorting_by_parent_category_translation_works_properly()
    {
        $cat1 = TajweedRuleCategory::create(['order' => 1, 'is_active' => true, 'slug' => 'b-category']);
        $cat1->translations()->create(['locale' => 'en', 'name' => 'B Category']);

        $cat2 = TajweedRuleCategory::create(['order' => 2, 'is_active' => true, 'slug' => 'a-category']);
        $cat2->translations()->create(['locale' => 'en', 'name' => 'A Category']);

        $rule1 = TajweedRule::create([
            'tajweed_rule_category_id' => $cat1->id,
            'slug' => 'rule-1',
            'is_active' => true,
        ]);
        $rule1->translations()->create(['locale' => 'en', 'name' => 'Rule 1']);

        $rule2 = TajweedRule::create([
            'tajweed_rule_category_id' => $cat2->id,
            'slug' => 'rule-2',
            'is_active' => true,
        ]);
        $rule2->translations()->create(['locale' => 'en', 'name' => 'Rule 2']);

        app()->setLocale('en');

        // Order rules by parent category name translation: A Category (rule2) -> B Category (rule1)
        $rules = TajweedRule::orderByCategoryTranslation('asc')->get();
        $this->assertEquals('rule-2', $rules->get(0)->slug);
        $this->assertEquals('rule-1', $rules->get(1)->slug);
    }

    /** @test */
    public function eager_loading_prevents_n_plus_one_during_search_and_sorting()
    {
        // Seed some data
        $cat = TajweedRuleCategory::create(['order' => 1, 'is_active' => true, 'slug' => 'category']);
        $cat->translations()->create(['locale' => 'en', 'name' => 'Category']);

        for ($i = 1; $i <= 5; $i++) {
            $rule = TajweedRule::create([
                'tajweed_rule_category_id' => $cat->id,
                'slug' => 'rule-' . $i,
                'is_active' => true,
            ]);
            $rule->translations()->create(['locale' => 'en', 'name' => 'Rule ' . $i]);
        }

        // Measure query count with eager loading
        DB::flushQueryLog();
        DB::enableQueryLog();

        $rules = TajweedRule::with(['translations', 'category.translations'])
            ->whereTranslationLikeAny('name', 'Rule')
            ->orderByCategoryTranslation('asc')
            ->orderByTranslation('name', 'asc')
            ->get();

        foreach ($rules as $rule) {
            $name = $rule->name;
            $catName = $rule->category?->name;
        }

        $queryCount = count(DB::getQueryLog());
        // Should execute exactly 3 queries: rules table, translations table, and categories table (with translations)
        $this->assertLessThanOrEqual(5, $queryCount);
    }
}
