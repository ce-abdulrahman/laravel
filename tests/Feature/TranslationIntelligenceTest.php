<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Models\User;
use App\Services\TranslationSemanticService;
use App\Services\TranslationSearchService;
use App\Services\TranslationGroupingService;
use App\Services\TranslationSuggestionService;
use App\Services\TranslationConsistencyService;
use App\Services\AiTranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TranslationIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected User $admin;
    protected Language $langEn;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->langEn = Language::where('code', 'en')->firstOrFail();
    }

    /**
     * Test semantic key parser parses components correctly.
     */
    public function test_semantic_key_parsing_accuracy(): void
    {
        $semanticService = app(TranslationSemanticService::class);

        // 3-segment key
        $p1 = $semanticService->parseKey('auth.login.button');
        $this->assertEquals('auth', $p1['module']);
        $this->assertEquals('login', $p1['context']);
        $this->assertEquals('button', $p1['element']);

        // 2-segment key
        $p2 = $semanticService->parseKey('settings.title');
        $this->assertEquals('settings', $p2['module']);
        $this->assertEquals('settings', $p2['context']);
        $this->assertEquals('title', $p2['element']);

        // 1-segment key
        $p3 = $semanticService->parseKey('welcome');
        $this->assertEquals('general', $p3['module']);
        $this->assertEquals('general', $p3['context']);
        $this->assertEquals('welcome', $p3['element']);
    }

    /**
     * Test semantic search matches context and ranks by score.
     */
    public function test_semantic_search_matches_query_and_ranks(): void
    {
        $keyBtn = TranslationKey::create(['key' => 'auth.login.button', 'group' => 'auth']);
        UiTranslation::create([
            'translation_key_id' => $keyBtn->id,
            'language_id' => $this->langEn->id,
            'value' => 'Login submit button'
        ]);

        $keyTitle = TranslationKey::create(['key' => 'home.welcome.title', 'group' => 'home']);
        UiTranslation::create([
            'translation_key_id' => $keyTitle->id,
            'language_id' => $this->langEn->id,
            'value' => 'Welcome home header title'
        ]);

        $searchService = app(TranslationSearchService::class);
        
        // Search: "login button" - should rank auth.login.button higher
        $results = $searchService->search('login button');

        $this->assertNotEmpty($results);
        $this->assertEquals('auth.login.button', $results[0]['key']->key);
    }

    /**
     * Test grouping service auto-detects and rebuilds group fields.
     */
    public function test_grouping_auto_detection_and_bulk_rebuild(): void
    {
        // Key with mismatching group
        $key = TranslationKey::create(['key' => 'auth.login.btn', 'group' => 'errors']);

        $groupingService = app(TranslationGroupingService::class);
        $this->assertEquals('auth', $groupingService->autoDetectGroup($key->key));

        // Trigger bulk rebuild
        $response = $this->actingAs($this->admin)
            ->post(route('translations-manager.intelligence.rebuild-groups'));

        $response->assertStatus(200);
        $this->assertEquals('auth', $key->fresh()->group);
    }

    /**
     * Test AI Context translation generator.
     */
    public function test_ai_context_translation_prompts(): void
    {
        $key = TranslationKey::create([
            'key' => 'auth.login.button',
            'group' => 'auth',
            'description' => 'A submit button on the user login form.'
        ]);

        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'Sign In'
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'چوونەژوورەوە']]]]
                ]
            ], 200)
        ]);

        // Temporarily put mock key to trigger fake call
        putenv('GEMINI_API_KEY=mock-key-for-test');

        $aiService = app(AiTranslationService::class);
        $translated = $aiService->translateWithContext('auth.login.button', 'ku');

        $this->assertEquals('چوونەژوورەوە', $translated);

        putenv('GEMINI_API_KEY=');
    }

    /**
     * Test Key Naming Suggestion Engine.
     */
    public function test_key_naming_suggestions(): void
    {
        $suggestionService = app(TranslationSuggestionService::class);
        $suggestions = $suggestionService->suggestKey('Submit login page');

        $this->assertNotEmpty($suggestions);
        
        // Assert suggested patterns contain auth prefix and action suffixes
        $keysList = array_column($suggestions, 'key');
        
        $this->assertTrue(
            in_array('auth.submit_page.submit', $keysList, true) ||
            in_array('auth.page_submit', $keysList, true) ||
            in_array('auth.submit_page', $keysList, true)
        );
    }

    /**
     * Test consistency diagnostic scans.
     */
    public function test_consistency_audits_returns_alert_types(): void
    {
        // Create casing inconsistency
        TranslationKey::create(['key' => 'auth.loginButton', 'group' => 'auth']);
        
        // Create duplicate value entries
        $key1 = TranslationKey::create(['key' => 'auth.login.text1', 'group' => 'auth']);
        $key2 = TranslationKey::create(['key' => 'auth.login.text2', 'group' => 'auth']);

        UiTranslation::create(['translation_key_id' => $key1->id, 'language_id' => $this->langEn->id, 'value' => 'Common Duplicate Text String']);
        UiTranslation::create(['translation_key_id' => $key2->id, 'language_id' => $this->langEn->id, 'value' => 'Common Duplicate Text String']);

        $consistencyService = app(TranslationConsistencyService::class);
        $report = $consistencyService->checkConsistency();

        $this->assertNotEmpty($report['inconsistent_keys']);
        $this->assertNotEmpty($report['duplicates']);
        $this->assertNotEmpty($report['unused_keys']);
    }

    /**
     * Dataset performance simulation.
     */
    public function test_large_dataset_performance_simulation(): void
    {
        // Insert 100 dummy keys
        $defaultLang = Language::where('is_default', true)->first() ?? Language::first();

        for ($i = 0; $i < 100; $i++) {
            $key = TranslationKey::create(['key' => "module.key_name_{$i}", 'group' => 'module']);
            UiTranslation::create([
                'translation_key_id' => $key->id,
                'language_id' => $defaultLang->id,
                'value' => "Sample english description value {$i}"
            ]);
        }

        $searchService = app(TranslationSearchService::class);

        $start = microtime(true);
        $results = $searchService->search('Sample english description value 50');
        $end = microtime(true);

        $this->assertNotEmpty($results);
        $this->assertLessThan(0.5, $end - $start, 'Performance is too slow.');
    }
}
