<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Models\UiTranslationVersion;
use App\Models\User;
use App\Services\TranslationAnalyticsService;
use App\Services\TranslationMetricsService;
use App\Services\TranslationPerformanceService;
use App\Services\AiTranslationAnalyticsService;
use App\Services\MissingTranslationAnalyticsService;
use App\Jobs\FlushTranslationAnalyticsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TranslationAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected User $admin;
    protected User $member;
    protected Language $langEn;
    protected Language $langKu;
    protected TranslationAnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->member = User::factory()->create(['role' => 'member']);
        $this->langEn = Language::where('code', 'en')->firstOrFail();
        $this->langKu = Language::where('code', 'ku')->firstOrFail();
        $this->analyticsService = app(TranslationAnalyticsService::class);

        // Clear cache before each test
        Cache::forget('translation_analytics_buffer');
        Cache::forget('translation_performance_metrics');
    }

    /**
     * Test lookup tracking inside TranslationService::get triggers cache buffers.
     */
    public function test_translation_helper_logs_to_cache_buffer(): void
    {
        $key = TranslationKey::create(['key' => 'analytics.test_key', 'group' => 'analytics']);
        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'Analytics Enabled',
        ]);

        // Trigger lookup
        t('analytics.test_key', [], 'en');

        // Assert cache buffer is populated
        $buffer = Cache::get('translation_analytics_buffer', []);
        $this->assertCount(1, $buffer);
        $this->assertEquals('analytics.test_key', $buffer[0]['key_name']);
        $this->assertEquals('en', $buffer[0]['locale']);
        $this->assertFalse($buffer[0]['is_missing']);
    }

    /**
     * Test FlushTranslationAnalyticsJob inserts pre-aggregated hits correctly.
     */
    public function test_flush_analytics_job_aggregates_and_inserts(): void
    {
        $key = TranslationKey::create(['key' => 'analytics.test_key', 'group' => 'analytics']);
        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'Analytics Enabled',
        ]);

        // Push duplicate hits into the buffer manually
        $this->analyticsService->logHit('analytics.test_key', 'en', false);
        $this->analyticsService->logHit('analytics.test_key', 'en', false);
        $this->analyticsService->logHit('analytics.test_key', 'en', false);

        // Assert 3 hits are in cache
        $this->assertCount(3, Cache::get('translation_analytics_buffer'));

        // Run Flush job
        FlushTranslationAnalyticsJob::dispatchSync();

        // Assert cache buffer is cleared
        $this->assertEmpty(Cache::get('translation_analytics_buffer', []));

        // Assert DB has record with pre-aggregated count = 3
        $this->assertDatabaseHas('translation_analytics', [
            'key_name' => 'analytics.test_key',
            'locale' => 'en',
            'hit_count' => 3,
            'is_missing' => false,
        ]);
    }

    /**
     * Test metrics computation (computeDailyMetrics) and heatmap aggregations.
     */
    public function test_daily_metrics_computation_and_heatmaps(): void
    {
        $key = TranslationKey::create(['key' => 'analytics.popular_key', 'group' => 'analytics']);

        // Insert mock data dated yesterday
        $yesterday = now()->subDay()->format('Y-m-d H:i:s');
        \DB::table('translation_analytics')->insert([
            'translation_key_id' => $key->id,
            'key_name' => 'analytics.popular_key',
            'locale' => 'en',
            'hit_count' => 10,
            'is_missing' => false,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        \DB::table('translation_analytics')->insert([
            'translation_key_id' => null,
            'key_name' => 'analytics.missing_key',
            'locale' => 'en',
            'hit_count' => 5,
            'is_missing' => true,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        // Compute metrics
        app(TranslationMetricsService::class)->computeDailyMetrics();

        // Assert daily usage summary table contains summary for yesterday
        $this->assertDatabaseHas('translation_usage_summary', [
            'date' => now()->subDay()->format('Y-m-d'),
            'locale' => 'en',
            'total_requests' => 15,
            'missing_keys_count' => 5,
            'top_key_id' => $key->id,
        ]);

        // Check heatmap data
        $heatmap = app(TranslationMetricsService::class)->generateHeatmapData();
        $this->assertNotEmpty($heatmap['top_keys']);
        $this->assertNotEmpty($heatmap['top_modules']);
        $this->assertEquals('analytics', $heatmap['top_modules'][0]['module']);
    }

    /**
     * Test performance telemetry tracking.
     */
    public function test_performance_telemetry_tracking(): void
    {
        $perfService = app(TranslationPerformanceService::class);

        $perfService->logPerformance(1.5, true, false, false);
        $perfService->logPerformance(4.2, false, true, false);

        $stats = $perfService->getPerformanceStats();

        $this->assertEquals(2.85, $stats['avg_lookup_ms']);
        $this->assertEquals(50.0, $stats['cache_hit_rate']);
        $this->assertEquals(50.0, $stats['db_fallback_rate']);
        $this->assertEquals(0.0, $stats['ai_usage_rate']);
    }

    /**
     * Test AI acceptance rate calculations based on translation version histories.
     */
    public function test_ai_acceptance_rate_and_revisions(): void
    {
        $key1 = TranslationKey::create(['key' => 'ai.key1', 'group' => 'ai']);
        $key2 = TranslationKey::create(['key' => 'ai.key2', 'group' => 'ai']);

        // 1. Translation generated by AI, never revised
        $t1 = UiTranslation::create([
            'translation_key_id' => $key1->id,
            'language_id' => $this->langEn->id,
            'value' => 'AI Generated Text',
            'is_auto_generated' => true,
        ]);
        UiTranslationVersion::create([
            'ui_translation_id' => $t1->id,
            'old_value' => null,
            'new_value' => 'AI Generated Text',
            'change_source' => 'ai',
        ]);

        // 2. Translation generated by AI, then manually revised
        $t2 = UiTranslation::create([
            'translation_key_id' => $key2->id,
            'language_id' => $this->langEn->id,
            'value' => 'Revised Text',
            'is_auto_generated' => false,
        ]);
        UiTranslationVersion::create([
            'ui_translation_id' => $t2->id,
            'old_value' => null,
            'new_value' => 'AI Text',
            'change_source' => 'ai',
        ]);
        UiTranslationVersion::create([
            'ui_translation_id' => $t2->id,
            'old_value' => 'AI Text',
            'new_value' => 'Revised Text',
            'change_source' => 'manual',
        ]);

        $aiStats = app(AiTranslationAnalyticsService::class)->getAiAnalytics();

        $this->assertEquals(2, $aiStats['ai_generated']);
        $this->assertEquals(1, $aiStats['edited_after_ai']);
        $this->assertEquals(1, $aiStats['accepted_as_is']);
        $this->assertEquals(50.0, $aiStats['acceptance_rate']);
    }

    /**
     * Simulate high load (100+ hits) to check queue batch performance.
     */
    public function test_high_load_buffer_flush(): void
    {
        for ($i = 0; $i < 120; $i++) {
            $this->analyticsService->logHit("analytics.key.{$i}", 'en', false);
        }

        // Flush
        $this->analyticsService->flush();

        // All 120 should be inserted
        $this->assertEquals(120, \DB::table('translation_analytics')->sum('hit_count'));
    }

    /**
     * Test admin-only authorization middleware rules.
     */
    public function test_analytics_dashboard_admin_authorization(): void
    {
        // Member should be forbidden
        $this->actingAs($this->member)
            ->get(route('translations-manager.analytics'))
            ->assertStatus(403);

        // Admin should be allowed
        $this->actingAs($this->admin)
            ->get(route('translations-manager.analytics'))
            ->assertStatus(200);
    }

    /**
     * Test manual buffer flush via controller action.
     */
    public function test_admin_can_flush_analytics_buffer(): void
    {
        $this->analyticsService->logHit('test.key', 'en', false);

        $response = $this->actingAs($this->admin)
            ->post(route('translations-manager.analytics.flush'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('translation_analytics', [
            'key_name' => 'test.key',
            'locale' => 'en',
            'hit_count' => 1,
        ]);
    }

    /**
     * Test manual AI fix for missing translation.
     */
    public function test_admin_can_trigger_ai_fix(): void
    {
        $key = TranslationKey::create(['key' => 'home.welcome', 'group' => 'home', 'description' => 'home.welcome']);

        // Log missing translation in analytics DB
        \DB::table('translation_analytics')->insert([
            'translation_key_id' => $key->id,
            'key_name' => 'home.welcome',
            'locale' => 'ku',
            'hit_count' => 5,
            'is_missing' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('translations-manager.analytics.ai-fix'), [
                'key' => 'home.welcome',
                'locale' => 'ku',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'fixed_locales' => ['Kurdish'],
        ]);

        // Assert database has Kurdish UI translation populated
        $this->assertDatabaseHas('ui_translations', [
            'translation_key_id' => $key->id,
            'language_id' => $this->langKu->id,
            'value' => 'بەخێربێن بۆ بەرنامەی قورئان', // Stub translation from AiTranslationService stub dict
        ]);

        // Assert missing log record is removed
        $this->assertDatabaseMissing('translation_analytics', [
            'key_name' => 'home.welcome',
            'is_missing' => true,
        ]);
    }
}
