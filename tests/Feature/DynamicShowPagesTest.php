<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Surah;
use App\Models\HadithCategory;
use App\Models\Hadith;
use App\Models\AdhkarCategory;
use App\Models\Adhkar;
use App\Models\TajweedRuleCategory;
use App\Models\TajweedRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DynamicShowPagesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('language:active_codes');
        Cache::forget('language:active_list');
        Cache::forget('language:default');
        $this->user = User::factory()->create();

        // Seed Hadith, Adhkar, and other dependency seeders
        $this->seed(\Database\Seeders\HadithSeeder::class);
        $this->seed(\Database\Seeders\AdhkarSeeder::class);
    }

    public function test_dynamic_show_pages_render_successfully(): void
    {
        $entities = [
            'surahs.show' => Surah::firstOrFail(),
            'hadith-categories.show' => HadithCategory::firstOrFail(),
            'hadiths.show' => Hadith::firstOrFail(),
            'adhkar-categories.show' => AdhkarCategory::firstOrFail(),
            'adhkars.show' => Adhkar::firstOrFail(),
            'tajweed-rule-categories.show' => TajweedRuleCategory::firstOrFail(),
            'tajweed-rules.show' => TajweedRule::firstOrFail(),
        ];

        foreach ($entities as $routeName => $model) {
            $response = $this->actingAs($this->user)->get(route($routeName, $model));
            $response->assertStatus(200);

            // Verify active language names are in the response
            foreach (Language::activeList() as $lang) {
                $response->assertSee($lang->name);
            }
        }
    }

    public function test_models_with_no_translations(): void
    {
        $surah = Surah::firstOrFail();
        $surah->translations()->delete(); // Clear translations

        $response = $this->actingAs($this->user)->get(route('surahs.show', $surah));
        $response->assertStatus(200);
        
        // Under dynamic missing translation presentation, it should display the localized badge
        $response->assertSee('Missing Translation');
    }

    public function test_models_with_partially_completed_translations(): void
    {
        $hadith = Hadith::firstOrFail();
        
        // Remove English translations specifically
        $hadith->translations()->where('locale', 'en')->delete();

        $response = $this->actingAs($this->user)->get(route('hadiths.show', $hadith));
        $response->assertStatus(200);

        // English translation should show "Missing Translation"
        $response->assertSee('Missing Translation');
    }

    public function test_rtl_ltr_rendering_behavior(): void
    {
        $hadith = Hadith::firstOrFail();
        $hadith->setTranslation('translation', 'ar', 'الترجمة بالعربية');
        $hadith->setTranslation('translation', 'en', 'Translation in English');

        // Seed an RTL language and LTR language and verify attributes in response
        $rtlLang = Language::where('code', 'ar')->first();
        $rtlLang->update(['direction' => 'rtl', 'is_active' => true]);

        $ltrLang = Language::where('code', 'en')->first();
        $ltrLang->update(['direction' => 'ltr', 'is_active' => true]);

        Cache::forget('language:active_list');

        $response = $this->actingAs($this->user)->get(route('hadiths.show', $hadith));
        $response->assertStatus(200);

        // RTL specific styling
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('style="text-align: right;', false);
        $response->assertSee('arabic-text', false);

        // LTR specific styling
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('style="text-align: left;', false);
    }

    public function test_scale_rendering_with_10_active_languages(): void
    {
        // Add 10 active languages
        for ($i = 1; $i <= 10; $i++) {
            Language::create([
                'code' => 'l' . $i,
                'name' => 'LangName' . $i,
                'native_name' => 'LangName' . $i,
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => false,
                'order' => 100 + $i,
            ]);
        }
        
        Cache::forget('language:active_codes');
        Cache::forget('language:active_list');

        $surah = Surah::firstOrFail();
        
        // Fetch show details
        $response = $this->actingAs($this->user)->get(route('surahs.show', $surah));
        $response->assertStatus(200);

        for ($i = 1; $i <= 10; $i++) {
            $response->assertSee('LangName' . $i);
        }
    }

    public function test_zero_per_language_database_queries_during_rendering(): void
    {
        $surah = Surah::firstOrFail();

        // Prime the Auth Guard so user lookup query is run once before we start measuring
        $this->actingAs($this->user)->get(route('surahs.show', $surah));

        // Clear cache before first run
        Cache::forget('language:active_codes');
        Cache::forget('language:active_list');

        // 1. Measure queries for active languages
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        
        $this->actingAs($this->user)->get(route('surahs.show', $surah));
        $initialQueryCount = count(DB::connection()->getQueryLog());
        DB::connection()->disableQueryLog();

        // 2. Add 10 more languages
        for ($i = 1; $i <= 10; $i++) {
            Language::create([
                'code' => 'scale' . $i,
                'name' => 'ScaleLang' . $i,
                'native_name' => 'ScaleLang' . $i,
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => false,
                'order' => 200 + $i,
            ]);
        }

        // Clear cache before second run
        Cache::forget('language:active_codes');
        Cache::forget('language:active_list');

        // 3. Measure queries with scaled active languages
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();

        $this->actingAs($this->user)->get(route('surahs.show', $surah));
        $scaledQueryCount = count(DB::connection()->getQueryLog());
        DB::connection()->disableQueryLog();

        // The query count should not increase with the number of languages.
        // It must stay exactly the same (O(1) database complexity relative to language count).
        $this->assertEquals($initialQueryCount, $scaledQueryCount, "Eager loading failed! Queries scale with language count.");
    }
}
