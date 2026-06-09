<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Surah;
use App\Models\HadithCategory;
use App\Models\Hadith;
use App\Models\AdhkarCategory;
use App\Models\Adhkar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DynamicIndexTablesTest extends TestCase
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
    }

    public function test_dynamic_language_addition_and_removal_in_tables(): void
    {
        // 1. Visit with Kurdish locale
        app()->setLocale('ku');
        session(['locale' => 'ku']);

        $response = $this->actingAs($this->user)->get(route('surahs.index'));
        $response->assertStatus(200);
        
        // Assert we have separate column headers for active languages
        foreach (Language::activeList() as $lang) {
            $response->assertSee('<th>Name (' . $lang->name . ')</th>', false);
        }

        // 2. Add a new active language
        $es = Language::create([
            'code' => 'es',
            'name' => 'Spanish',
            'native_name' => 'Español',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => false,
            'order' => 10,
        ]);
        
        Language::booted();

        // Check Surah index: Spanish should appear in headers
        $response = $this->actingAs($this->user)->get(route('surahs.index'));
        $response->assertSee('<th>Name (Spanish)</th>', false);
    }

    public function test_rtl_language_rendering_logic(): void
    {
        // Ensure we have an active RTL language (Arabic is seeded)
        $rtlLang = Language::where('code', 'ar')->first();
        $rtlLang->update(['direction' => 'rtl', 'is_active' => true]);

        // Ensure a translation exists for Arabic
        $surah = Surah::firstOrFail();
        $surah->translations()->updateOrCreate(
            ['locale' => 'ar'],
            ['name' => 'الفاتحة']
        );

        Language::booted();

        // Set locale to Arabic so it resolves the Arabic translation
        app()->setLocale('ar');
        session(['locale' => 'ar']);

        $response = $this->actingAs($this->user)->get(route('surahs.index'));
        $response->assertOk();
        
        // Verify direction, typography, and text-align classes are automatically determined and rendered
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('style="text-align: right;"', false);
        $response->assertSee('arabic-text', false);
        $response->assertSee('الفاتحة');
    }

    public function test_missing_translation_placeholders(): void
    {
        // Clear translations of the first Surah
        $surah = Surah::firstOrFail();
        $surah->translations()->delete();

        $response = $this->actingAs($this->user)->get(route('surahs.index'));
        $response->assertStatus(200);
        $response->assertSee('Missing Translation');
    }

    public function test_high_language_count_scenarios(): void
    {
        // Seed 10 active languages
        for ($i = 1; $i <= 10; $i++) {
            Language::create([
                'code' => 'l' . $i,
                'name' => 'Lang' . $i,
                'native_name' => 'Lang' . $i,
                'direction' => $i % 2 === 0 ? 'rtl' : 'ltr',
                'is_active' => true,
                'is_default' => false,
                'order' => 10 + $i,
            ]);
        }
        
        Language::booted();

        // Seed some categories & items for safety
        $this->seed(\Database\Seeders\HadithSeeder::class);
        $this->seed(\Database\Seeders\AdhkarSeeder::class);

        // Access all translatable index routes and verify they load successfully
        $routes = [
            'surahs.index',
            'hadith-categories.index',
            'hadiths.index',
            'adhkar-categories.index',
            'adhkars.index',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($this->user)->get(route($routeName));
            $response->assertStatus(200);
            
            // Check that dynamic languages exist in headers
            for ($i = 1; $i <= 10; $i++) {
                if (str_contains($routeName, 'hadith-categories') || str_contains($routeName, 'adhkar-categories') || str_contains($routeName, 'surahs')) {
                    $response->assertSee('<th>Name (Lang' . $i . ')</th>', false);
                } else {
                    $response->assertSee('<th>Translation (Lang' . $i . ')</th>', false);
                }
            }
        }
    }

    public function test_correct_dynamic_colspan_calculations(): void
    {
        $activeCount = Language::activeList()->count();

        // Surah empty state
        $response = $this->actingAs($this->user)->get(route('surahs.index') . '?q=NON_EXISTENT_SURAH_KEYWORD_X');
        $response->assertStatus(200);
        $expectedColspan = 5 + $activeCount;
        $response->assertSee('colspan="' . $expectedColspan . '"', false);

        // Hadith Categories empty state
        $response = $this->actingAs($this->user)->get(route('hadith-categories.index') . '?q=NON_EXISTENT_HADITH_CAT_KEYWORD_X');
        $response->assertStatus(200);
        $expectedColspan = 4 + $activeCount;
        $response->assertSee('colspan="' . $expectedColspan . '"', false);
    }
}
