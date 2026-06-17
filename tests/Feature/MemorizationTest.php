<?php

namespace Tests\Feature;

use App\Models\Ayah;
use App\Models\Surah;
use App\Models\User;
use App\Models\UserAyahProgress;
use App\Services\SpacedRepetitionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MemorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Surah $surah;
    protected Ayah $ayah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        $this->surah = Surah::create([
            'number' => 1,
            'revelation_type' => 'Meccan',
            'ayah_count' => 7,
        ]);

        $this->ayah = Ayah::create([
            'surah_id' => $this->surah->id,
            'ayah_number' => 1,
            'text_uthmani' => 'الحمد لله رب العالمين',
            'text_simple' => 'الحمد لله رب العالمين',
            'page_number' => 1,
            'juz_number' => 1,
        ]);
    }

    public function test_spaced_repetition_perfect_result()
    {
        $service = app(SpacedRepetitionService::class);
        $progress = $service->logReview($this->user->id, $this->ayah->id, 'perfect');

        $this->assertEquals(1, $progress->review_count);
        $this->assertEquals(1, $progress->current_interval_days);
        $this->assertEquals(Carbon::today()->addDays(1)->toDateString(), $progress->next_review_date->toDateString());
        $this->assertEquals(\App\Enums\MasteryLevel::Learning, $progress->mastery_level);
        $this->assertEquals(100, $progress->strength_score);
    }

    public function test_spaced_repetition_fair_result_halves_interval()
    {
        $progress = UserAyahProgress::create([
            'user_id' => $this->user->id,
            'ayah_id' => $this->ayah->id,
            'memorize_status' => 'memorized',
            'current_interval_days' => 14,
            'review_count' => 4,
            'mastery_level' => \App\Enums\MasteryLevel::Memorized,
        ]);

        $service = app(SpacedRepetitionService::class);
        $progress = $service->logReview($this->user->id, $this->ayah->id, 'fair');

        // Fair reduces interval by 50% safely: 14 * 0.5 = 7
        $this->assertEquals(7, $progress->current_interval_days);
        $this->assertEquals(Carbon::today()->addDays(7)->toDateString(), $progress->next_review_date->toDateString());
    }

    public function test_due_reviews_sorting_overdue_first()
    {
        $ayah2 = Ayah::create([
            'surah_id' => $this->surah->id,
            'ayah_number' => 2,
            'text_uthmani' => 'الرحمن الرحيم',
            'text_simple' => 'الرحمن الرحيم',
            'page_number' => 1,
            'juz_number' => 1,
        ]);

        // Overdue review (next_review_date < today)
        UserAyahProgress::create([
            'user_id' => $this->user->id,
            'ayah_id' => $this->ayah->id,
            'memorize_status' => 'memorized',
            'next_review_date' => Carbon::yesterday(),
            'strength_score' => 80,
            'mastery_level' => \App\Enums\MasteryLevel::Memorized,
        ]);

        // Today's due review (next_review_date == today)
        UserAyahProgress::create([
            'user_id' => $this->user->id,
            'ayah_id' => $ayah2->id,
            'memorize_status' => 'memorized',
            'next_review_date' => Carbon::today(),
            'strength_score' => 90,
            'mastery_level' => \App\Enums\MasteryLevel::Memorized,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/reviews/due');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        // Assert that the overdue one (ayah_id) comes before the today's due one (ayah2->id)
        $this->assertCount(2, $data);
        $this->assertEquals($this->ayah->id, $data[0]['ayah_id']);
        $this->assertEquals($ayah2->id, $data[1]['ayah_id']);
    }

    public function test_store_session_records_status_correctly()
    {
        // Interrupted session
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/memorization/sessions', [
                'session_type' => 'quiz',
                'status' => 'interrupted',
                'started_at' => Carbon::now()->subMinutes(10)->toDateTimeString(),
                'ended_at' => Carbon::now()->toDateTimeString(),
                'completed_at' => Carbon::now()->toDateTimeString(),
                'duration_seconds' => 600,
                'ayahs_reviewed' => 5,
                'ayahs_memorized' => 1,
                'score' => 70,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('memorization_sessions', [
            'user_id' => $this->user->id,
            'status' => 'interrupted',
            'ended_at' => null,
            'completed_at' => null,
        ]);
    }

    public function test_export_memorization_plans_json()
    {
        $plan = \App\Models\MemorizationPlan::create([
            'user_id' => $this->user->id,
            'title' => 'Test Plan JSON',
            'plan_type' => 'surah',
            'start_date' => Carbon::today(),
            'daily_target_type' => 'ayahs',
            'daily_target_value' => 5,
            'status' => 'active',
        ]);

        $item = \App\Models\MemorizationPlanItem::create([
            'memorization_plan_id' => $plan->id,
            'surah_id' => $this->surah->id,
            'from_ayah_id' => $this->ayah->id,
            'to_ayah_id' => $this->ayah->id,
            'day_number' => 1,
            'target_date' => Carbon::today(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/memorization-plans/export/json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        
        $data = json_decode($response->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertEquals('Test Plan JSON', $data[0]['title']);
        $this->assertCount(1, $data[0]['items']);
        $this->assertEquals($this->ayah->id, $data[0]['items'][0]['from_ayah_id']);
    }

    public function test_import_memorization_plans_json()
    {
        $payload = [
            'plans' => [
                [
                    'title' => 'Imported Plan',
                    'plan_type' => 'surah',
                    'start_date' => Carbon::today()->toDateString(),
                    'daily_target_type' => 'ayahs',
                    'daily_target_value' => 5,
                    'status' => 'active',
                    'items' => [
                        [
                            'surah_id' => $this->surah->id,
                            'from_ayah_id' => $this->ayah->id,
                            'to_ayah_id' => $this->ayah->id,
                            'day_number' => 1,
                            'target_date' => Carbon::today()->toDateString(),
                            'status' => 'pending',
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/memorization-plans/import', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('memorization_plans', [
            'user_id' => $this->user->id,
            'title' => 'Imported Plan',
        ]);
        $this->assertDatabaseHas('memorization_plan_items', [
            'surah_id' => $this->surah->id,
            'from_ayah_id' => $this->ayah->id,
            'status' => 'pending',
        ]);
    }

    public function test_export_memorization_reviews_json()
    {
        \App\Models\MemorizationReview::create([
            'user_id' => $this->user->id,
            'ayah_id' => $this->ayah->id,
            'review_date' => Carbon::today(),
            'review_level' => 'learning',
            'result' => 'good',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/memorization-reviews/export/json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        
        $data = json_decode($response->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertEquals($this->ayah->id, $data[0]['ayah_id']);
        $this->assertEquals('learning', $data[0]['review_level']);
    }

    public function test_import_memorization_reviews_json()
    {
        $payload = [
            'reviews' => [
                [
                    'ayah_id' => $this->ayah->id,
                    'review_date' => Carbon::today()->toDateString(),
                    'review_level' => 'reviewing',
                    'result' => 'perfect',
                    'notes' => 'Imported Review Note',
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/memorization-reviews/import', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('memorization_reviews', [
            'user_id' => $this->user->id,
            'ayah_id' => $this->ayah->id,
            'review_level' => 'reviewing',
            'result' => 'perfect',
            'notes' => 'Imported Review Note',
        ]);
    }
}
