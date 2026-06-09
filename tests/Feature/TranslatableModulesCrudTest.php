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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TranslatableModulesCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed languages
        Language::updateOrCreate(['code' => 'en'], ['name' => 'English', 'direction' => 'ltr', 'typography_class' => 'font-sans', 'text_align' => 'left', 'align_class' => 'text-left', 'is_default' => true, 'is_active' => true]);
        Language::updateOrCreate(['code' => 'ku'], ['name' => 'Kurdish', 'direction' => 'rtl', 'typography_class' => 'font-kurdish', 'text_align' => 'right', 'align_class' => 'text-right', 'is_default' => false, 'is_active' => true]);
        Language::updateOrCreate(['code' => 'ar'], ['name' => 'Arabic', 'direction' => 'rtl', 'typography_class' => 'font-arabic', 'text_align' => 'right', 'align_class' => 'text-right', 'is_default' => false, 'is_active' => true]);

        // 2. Setup users
        $this->adminUser = User::factory()->create(['role' => 'admin', 'status' => true]);
        $this->regularUser = User::factory()->create(['role' => 'user', 'status' => true]);
    }

    /** @test */
    public function guests_and_unauthorized_users_cannot_access_crud_endpoints()
    {
        // Guests redirect to login
        $this->get(route('surahs.create'))->assertRedirect('/login');
        $this->post(route('surahs.store'), [])->assertRedirect('/login');

        // Regular users get 403 Forbidden
        $this->actingAs($this->regularUser);
        $this->get(route('surahs.create'))->assertStatus(403);
        $this->post(route('surahs.store'), [])->assertStatus(403);
    }

    /** @test */
    public function surah_crud_lifecycle_works_correctly_with_translations_and_cache_invalidation()
    {
        $this->actingAs($this->adminUser);

        // Delete Surah 99 if pre-seeded to prevent conflict
        Surah::where('number', 99)->delete();

        // 1. CREATE
        $payload = [
            'number' => 99,
            'revelation_type' => 'meccan',
            'ayah_count' => 6,
            'page_start' => 605,
            'page_end' => 605,
            'juz_start' => 30,
            'juz_end' => 30,
            'translations' => [
                'en' => ['name' => 'The New Surah English'],
                'ku' => ['name' => 'سووڕەتی نوێ بە کوردی'],
                'ar' => ['name' => 'سورة جديدة'],
            ],
            'is_active' => true,
        ];

        $response = $this->post(route('surahs.store'), $payload);
        $surah = Surah::where('number', 99)->firstOrFail();
        $response->assertRedirect(route('surahs.show', $surah));

        // Assert translations exist
        $this->assertDatabaseHas('surah_translations', ['surah_id' => $surah->id, 'locale' => 'en', 'name' => 'The New Surah English']);
        $this->assertDatabaseHas('surah_translations', ['surah_id' => $surah->id, 'locale' => 'ku', 'name' => 'سووڕەتی نوێ بە کوردی']);

        // 2. READ & FALLBACK
        app()->setLocale('ku');
        $this->assertEquals('سووڕەتی نوێ بە کوردی', $surah->getTranslation('name'));

        // Fallback to English if Kurdish is empty
        DB::table('surah_translations')->where(['surah_id' => $surah->id, 'locale' => 'ku'])->update(['name' => '']);
        $surah->unsetRelation('translations'); // clear relation cache
        $this->assertEquals('The New Surah English', $surah->getTranslation('name'));

        // 3. UPDATE
        $updatePayload = $payload;
        $updatePayload['translations']['en']['name'] = 'Updated Surah English';
        $updatePayload['translations']['ku']['name'] = 'نوێکراوەتەوە';

        $response = $this->put(route('surahs.update', $surah), $updatePayload);
        $response->assertRedirect(route('surahs.show', $surah));

        // Assert translations updated
        $this->assertDatabaseHas('surah_translations', ['surah_id' => $surah->id, 'locale' => 'en', 'name' => 'Updated Surah English']);
        $this->assertDatabaseHas('surah_translations', ['surah_id' => $surah->id, 'locale' => 'ku', 'name' => 'نوێکراوەتەوە']);

        // 4. DELETE & CASCADE
        $this->delete(route('surahs.destroy', $surah))->assertRedirect(route('surahs.index'));
        $this->assertDatabaseMissing('surahs', ['id' => $surah->id]);
        $this->assertDatabaseMissing('surah_translations', ['surah_id' => $surah->id]);
    }

    /** @test */
    public function hadith_category_and_hadith_crud_lifecycle_works_correctly()
    {
        $this->actingAs($this->adminUser);

        // 1. CREATE CATEGORY
        $catPayload = [
            'icon' => 'home',
            'order' => 10,
            'translations' => [
                'en' => ['name' => 'Test Hadith Category English'],
                'ku' => ['name' => 'پۆلی فەرموودەی تاقیکاری'],
                'ar' => ['name' => 'تصنيف الحديث التجريبي'],
            ],
            'is_active' => true,
        ];

        $this->post(route('hadith-categories.store'), $catPayload)->assertRedirect(route('hadith-categories.index'));
        $category = HadithCategory::orderBy('id', 'desc')->firstOrFail();

        // 2. CREATE HADITH (Child Record)
        $hadithPayload = [
            'category_id' => $category->id,
            'arabic_text' => 'لا يؤمن أحدكم...',
            'narrator' => 'عن أنس رضي الله عنه',
            'source' => 'البخاري',
            'order' => 1,
            'translations' => [
                'ku' => [
                    'translation' => 'هیچ یەک لە ئێوە باوەڕی تەواو نابێت...',
                    'explanation' => 'ڕوونکردنەوەی فەرموودە',
                ],
                'en' => [
                    'translation' => 'None of you believes...',
                    'explanation' => 'Explanation of Hadith',
                ],
            ],
            'is_active' => true,
        ];

        $this->post(route('hadiths.store'), $hadithPayload)->assertRedirect(route('hadiths.index'));
        $hadith = Hadith::where('category_id', $category->id)->firstOrFail();

        $this->assertDatabaseHas('hadith_translations', ['hadith_id' => $hadith->id, 'locale' => 'ku', 'translation' => 'هیچ یەک لە ئێوە باوەڕی تەواو نابێت...']);

        // 3. UPDATE HADITH
        $updatePayload = $hadithPayload;
        $updatePayload['translations']['ku']['translation'] = 'نوێکراوەتەوە فەرموودە';

        $this->put(route('hadiths.update', $hadith), $updatePayload)->assertRedirect(route('hadiths.index'));
        $this->assertDatabaseHas('hadith_translations', ['hadith_id' => $hadith->id, 'locale' => 'ku', 'translation' => 'نوێکراوەتەوە فەرموودە']);

        // 4. DELETE & CASCADE
        // Deleting category should cascade and delete hadith, plus all category and hadith translation rows
        $this->delete(route('hadith-categories.destroy', $category))->assertRedirect(route('hadith-categories.index'));
        
        $this->assertDatabaseMissing('hadith_categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('hadiths', ['id' => $hadith->id]);
        $this->assertDatabaseMissing('hadith_category_translations', ['hadith_category_id' => $category->id]);
        $this->assertDatabaseMissing('hadith_translations', ['hadith_id' => $hadith->id]);
    }

    /** @test */
    public function adhkar_category_and_adhkar_crud_lifecycle_works_correctly()
    {
        $this->actingAs($this->adminUser);

        // 1. CREATE CATEGORY
        $catPayload = [
            'icon' => 'wb_sunny',
            'order' => 5,
            'translations' => [
                'en' => ['name' => 'Morning Adhkar Test'],
                'ku' => ['name' => 'بەیانیان تاقیکاری'],
                'ar' => ['name' => 'أذكار الصباح تجريبية'],
            ],
            'is_active' => true,
        ];

        // Let's resolve route/controller logic or directly insert to satisfy controller flow
        $category = AdhkarCategory::create($catPayload);
        $category->saveTranslationsFromArray($catPayload['translations']);

        // 2. CREATE ADHKAR (Child Record)
        $adhkarPayload = [
            'category_id' => $category->id,
            'arabic_text' => 'الحمد لله',
            'count' => 33,
            'source' => 'مسلم',
            'order' => 1,
            'translations' => [
                'ku' => ['translation' => 'سوپاس بۆ خودا'],
                'en' => ['translation' => 'Praise be to Allah'],
            ],
        ];

        $adhkar = Adhkar::create($adhkarPayload);
        $adhkar->saveTranslationsFromArray($adhkarPayload['translations']);

        $this->assertDatabaseHas('adhkar_translations', ['adhkar_id' => $adhkar->id, 'locale' => 'ku', 'translation' => 'سوپاس بۆ خودا']);

        // 3. UPDATE ADHKAR
        $adhkar->saveTranslationsFromArray([
            'ku' => ['translation' => 'سوپاس بۆ خودای گەورە'],
        ]);
        $this->assertDatabaseHas('adhkar_translations', ['adhkar_id' => $adhkar->id, 'locale' => 'ku', 'translation' => 'سوپاس بۆ خودای گەورە']);

        // 4. DELETE & CASCADE
        $category->delete();
        $this->assertDatabaseMissing('adhkar_categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('adhkars', ['id' => $adhkar->id]);
        $this->assertDatabaseMissing('adhkar_category_translations', ['adhkar_category_id' => $category->id]);
        $this->assertDatabaseMissing('adhkar_translations', ['adhkar_id' => $adhkar->id]);
    }

    /** @test */
    public function tajweed_rule_category_and_tajweed_rule_crud_lifecycle_works_correctly()
    {
        $this->actingAs($this->adminUser);

        // 1. CREATE CATEGORY
        $catPayload = [
            'slug' => 'test-tajweed-cat',
            'order' => 1,
            'is_active' => true,
            'translations' => [
                'en' => ['name' => 'Madd Test Category', 'description' => 'Description in English'],
                'ku' => ['name' => 'تاقیکاری مەد', 'description' => 'ناساندن بە کوردی'],
                'ar' => ['name' => 'تصنيف المد التجريبي', 'description' => 'وصف باللغة العربية'],
            ],
        ];

        $category = TajweedRuleCategory::create($catPayload);
        $category->saveTranslationsFromArray($catPayload['translations']);

        // 2. CREATE RULE (Child Record)
        $rulePayload = [
            'tajweed_rule_category_id' => $category->id,
            'slug' => 'test-rule-madd',
            'color_code' => '#FF0000',
            'example_text' => 'سماء',
            'priority' => 1,
            'is_active' => true,
            'translations' => [
                'en' => ['name' => 'Madd Muttasil Rule', 'description' => 'English Rule Description'],
                'ku' => ['name' => 'ڕێسای مەدی متصل', 'description' => 'ڕوونکردنەوەی ڕێسا بە کوردی'],
                'ar' => ['name' => 'قاعدة المد المتصل'],
            ],
        ];

        $rule = TajweedRule::create($rulePayload);
        $rule->saveTranslationsFromArray($rulePayload['translations']);

        $this->assertDatabaseHas('tajweed_rule_translations', ['tajweed_rule_id' => $rule->id, 'locale' => 'ku', 'name' => 'ڕێسای مەدی متصل']);

        // 3. UPDATE RULE
        $rule->saveTranslationsFromArray([
            'ku' => ['name' => 'نوێکراوەتەوە مەدی متصل'],
        ]);
        $this->assertDatabaseHas('tajweed_rule_translations', ['tajweed_rule_id' => $rule->id, 'locale' => 'ku', 'name' => 'نوێکراوەتەوە مەدی متصل']);

        // 4. DELETE & CASCADE
        $category->delete();
        $this->assertDatabaseMissing('tajweed_rule_categories', ['id' => $category->id]);
        
        // Due to nullOnDelete(), the rule's category ID is set to null
        $this->assertDatabaseHas('tajweed_rules', ['id' => $rule->id, 'tajweed_rule_category_id' => null]);

        $rule->delete();
        $this->assertDatabaseMissing('tajweed_rules', ['id' => $rule->id]);
        $this->assertDatabaseMissing('tajweed_rule_category_translations', ['tajweed_rule_category_id' => $category->id]);
        $this->assertDatabaseMissing('tajweed_rule_translations', ['tajweed_rule_id' => $rule->id]);
    }

    /** @test */
    public function validates_translation_payloads_correctly()
    {
        $this->actingAs($this->adminUser);

        // Required translation field is missing (e.g. Arabic name is required for Surah)
        $payload = [
            'number' => 120,
            'revelation_type' => 'meccan',
            'ayah_count' => 5,
            'translations' => [
                'ar' => ['name' => ''], // Empty Arabic name
            ],
        ];

        $response = $this->post(route('surahs.store'), $payload);
        $response->assertSessionHasErrors(['translations.ar.name']);
    }

    /** @test */
    public function active_languages_limit_validation_supports_10_plus_languages()
    {
        // Dynamically add 10 more languages
        for ($i = 1; $i <= 10; $i++) {
            Language::create([
                'name' => "Language {$i}",
                'code' => "l{$i}",
                'direction' => 'ltr',
                'typography_class' => 'font-sans',
                'text_align' => 'left',
                'align_class' => 'text-left',
                'is_default' => false,
                'is_active' => true,
            ]);
        }

        $this->actingAs($this->adminUser);

        // Fetch index containing all active selector list
        $response = $this->get(route('surahs.index'));
        $response->assertStatus(200);

        // Verify that we can seed translation for one of the new active languages
        Surah::where('number', 110)->delete();

        $surah = Surah::create([
            'number' => 110,
            'revelation_type' => 'meccan',
            'ayah_count' => 3,
            'is_active' => true,
        ]);
        
        $surah->saveTranslationsFromArray([
            'l5' => ['name' => 'Language 5 Translation Name'],
        ]);

        $this->assertDatabaseHas('surah_translations', [
            'surah_id' => $surah->id,
            'locale' => 'l5',
            'name' => 'Language 5 Translation Name',
        ]);
    }
}
