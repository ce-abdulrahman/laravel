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
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederIdempotencyAndTranslationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function seeders_are_idempotent_and_correctly_populate_translations()
    {
        // 1. Run seeders first time
        $this->seed(DatabaseSeeder::class);

        // Capture initial counts of base tables and translations tables
        $surahCount = Surah::count();
        $surahTranslationsCount = \DB::table('surah_translations')->count();

        $tajweedRuleCategoryCount = TajweedRuleCategory::count();
        $tajweedRuleCategoryTranslationsCount = \DB::table('tajweed_rule_category_translations')->count();

        $tajweedRuleCount = TajweedRule::count();
        $tajweedRuleTranslationsCount = \DB::table('tajweed_rule_translations')->count();

        $hadithCategoryCount = HadithCategory::count();
        $hadithCategoryTranslationsCount = \DB::table('hadith_category_translations')->count();

        $hadithCount = Hadith::count();
        $hadithTranslationsCount = \DB::table('hadith_translations')->count();

        $adhkarCategoryCount = AdhkarCategory::count();
        $adhkarCategoryTranslationsCount = \DB::table('adhkar_category_translations')->count();

        $adhkarCount = Adhkar::count();
        $adhkarTranslationsCount = \DB::table('adhkar_translations')->count();

        // Assert that we seeded models and translations exist
        $this->assertGreaterThan(0, $surahCount);
        $this->assertGreaterThan(0, $surahTranslationsCount);
        $this->assertGreaterThan(0, $tajweedRuleCategoryCount);
        $this->assertGreaterThan(0, $tajweedRuleCategoryTranslationsCount);
        $this->assertGreaterThan(0, $tajweedRuleCount);
        $this->assertGreaterThan(0, $hadithCategoryCount);
        $this->assertGreaterThan(0, $hadithCount);
        $this->assertGreaterThan(0, $adhkarCategoryCount);
        $this->assertGreaterThan(0, $adhkarCount);

        // Assert translation correctness
        $hadith = Hadith::first();
        $this->assertNotNull($hadith->getTranslation('translation', 'ku'));

        $adhkar = Adhkar::first();
        $this->assertNotNull($adhkar->getTranslation('translation', 'ku'));

        $ruleCat = TajweedRuleCategory::where('slug', 'noon-sakinah-tanween')->first();
        $this->assertEquals('Noon Sakinah & Tanween', $ruleCat->getTranslation('name', 'en'));
        $this->assertEquals('نوونی ساکین و تەنوین', $ruleCat->getTranslation('name', 'ku'));
        $this->assertEquals('أحكام النون الساكنة والتنوين', $ruleCat->getTranslation('name', 'ar'));

        // 2. Run seeders a second time
        $this->seed(DatabaseSeeder::class);

        // Verify counts did not change (Idempotency)
        $this->assertEquals($surahCount, Surah::count(), 'Surah count changed on re-seed');
        $this->assertEquals($surahTranslationsCount, \DB::table('surah_translations')->count(), 'Surah translations duplicated');

        $this->assertEquals($tajweedRuleCategoryCount, TajweedRuleCategory::count(), 'TajweedRuleCategory count changed on re-seed');
        $this->assertEquals($tajweedRuleCategoryTranslationsCount, \DB::table('tajweed_rule_category_translations')->count(), 'TajweedRuleCategory translations duplicated');

        $this->assertEquals($tajweedRuleCount, TajweedRule::count(), 'TajweedRule count changed on re-seed');
        $this->assertEquals($tajweedRuleTranslationsCount, \DB::table('tajweed_rule_translations')->count(), 'TajweedRule translations duplicated');

        $this->assertEquals($hadithCategoryCount, HadithCategory::count(), 'HadithCategory count changed on re-seed');
        $this->assertEquals($hadithCategoryTranslationsCount, \DB::table('hadith_category_translations')->count(), 'HadithCategory translations duplicated');

        $this->assertEquals($hadithCount, Hadith::count(), 'Hadith count changed on re-seed');
        $this->assertEquals($hadithTranslationsCount, \DB::table('hadith_translations')->count(), 'Hadith translations duplicated');

        $this->assertEquals($adhkarCategoryCount, AdhkarCategory::count(), 'AdhkarCategory count changed on re-seed');
        $this->assertEquals($adhkarCategoryTranslationsCount, \DB::table('adhkar_category_translations')->count(), 'AdhkarCategory translations duplicated');

        $this->assertEquals($adhkarCount, Adhkar::count(), 'Adhkar count changed on re-seed');
        $this->assertEquals($adhkarTranslationsCount, \DB::table('adhkar_translations')->count(), 'Adhkar translations duplicated');

        // 3. Dynamically add a new language and verify seeders run without crashing
        Language::firstOrCreate(['code' => 'tr'], [
            'name' => 'Turkish',
            'direction' => 'ltr',
            'typography_class' => 'font-sans',
            'text_align' => 'left',
            'align_class' => 'text-left',
            'is_default' => false,
            'is_active' => true
        ]);

        // Re-run seeders with Turkish registered
        $this->seed(DatabaseSeeder::class);

        $this->assertEquals($surahCount, Surah::count(), 'Surah count changed after adding dynamic language');
    }
}
