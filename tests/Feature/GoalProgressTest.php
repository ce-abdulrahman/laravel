<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\DailyGoalTemplate;
use App\Models\UserGoalProgress;
use App\Models\UserGoalProgressEvent;
use App\Models\UserBadge;
use Carbon\Carbon;

class GoalProgressTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $template;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a default test user
        $this->user = User::factory()->create([
            'role' => 'user'
        ]);

        // Create a default goal template (ID: 1, Target: 100)
        $this->template = DailyGoalTemplate::create([
            'value' => 100,
            'is_active' => true,
        ]);

        // Seed template translations
        $this->template->translations()->create([
            'locale' => 'en',
            'title' => 'Daily Test Goal',
            'description' => 'Perform 100 tasbihs today',
        ]);
    }

    /**
     * Test basic progress updating works and computes percentages correctly.
     */
    public function test_progress_updates_safely_and_calculates_percentage()
    {
        $response = $this->actingAs($this->user)->postJson('/api/goals/progress/update', [
            'goal_id' => $this->template->id,
            'increment_value' => 30,
            'event_id' => 'evt_test_1',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.current_progress', 30)
            ->assertJsonPath('data.percentage', 30)
            ->assertJsonPath('data.is_completed', false);

        $this->assertDatabaseHas('user_goal_progress', [
            'user_id' => $this->user->id,
            'goal_id' => $this->template->id,
            'current_progress' => 30,
            'percentage' => 30.00,
            'is_completed' => false,
        ]);
    }

    /**
     * Test idempotency deduplication logic prevents duplicate increments.
     */
    public function test_idempotency_prevents_duplicate_increments_for_same_event_id()
    {
        // First increment
        $this->actingAs($this->user)->postJson('/api/goals/progress/update', [
            'goal_id' => $this->template->id,
            'increment_value' => 25,
            'event_id' => 'same_evt_id_123',
        ]);

        // Second duplicate increment
        $response = $this->actingAs($this->user)->postJson('/api/goals/progress/update', [
            'goal_id' => $this->template->id,
            'increment_value' => 25,
            'event_id' => 'same_evt_id_123', // Same event ID
        ]);

        // Progress must remain 25, not 50
        $response->assertStatus(200)
            ->assertJsonPath('data.current_progress', 25);

        $this->assertEquals(1, UserGoalProgressEvent::count());
        $this->assertEquals(25, UserGoalProgress::first()->current_progress);
    }

    /**
     * Test that progress completion caps at 100% and triggers bronze badge award.
     */
    public function test_completion_caps_progress_at_target_and_awards_bronze_badge()
    {
        $response = $this->actingAs($this->user)->postJson('/api/goals/progress/update', [
            'goal_id' => $this->template->id,
            'increment_value' => 150, // Exceeds target value of 100
            'event_id' => 'evt_exceed_1',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.current_progress', 100)
            ->assertJsonPath('data.percentage', 100)
            ->assertJsonPath('data.is_completed', true);

        // Verify total completed count and badge award
        $this->user->refresh();
        $this->assertEquals(1, $this->user->total_completed_goals);
        $this->assertDatabaseHas('user_badges', [
            'user_id' => $this->user->id,
            'badge_type' => 'bronze',
        ]);
    }

    /**
     * Test completed goal becomes read-only and ignores subsequent progress updates.
     */
    public function test_completed_progress_is_read_only_until_reset()
    {
        // 1. Force complete goal
        $this->actingAs($this->user)->postJson('/api/goals/progress/update', [
            'goal_id' => $this->template->id,
            'increment_value' => 100,
            'event_id' => 'evt_complete_1',
        ]);

        // 2. Subsequent update
        $response = $this->actingAs($this->user)->postJson('/api/goals/progress/update', [
            'goal_id' => $this->template->id,
            'increment_value' => 10,
            'event_id' => 'evt_extra_1',
        ]);

        // Value must still be capped at 100
        $response->assertStatus(200)
            ->assertJsonPath('data.current_progress', 100);
    }

    /**
     * Test badge thresholds award Silver (10) and Gold (50) badges.
     */
    public function test_badge_thresholds_silver_and_gold_award()
    {
        // Directly set completed goals count to 9 and complete another goal
        $this->user->total_completed_goals = 9;
        $this->user->save();
        
        $this->actingAs($this->user)->postJson('/api/goals/progress/update', [
            'goal_id' => $this->template->id,
            'increment_value' => 100,
            'event_id' => 'evt_badge_silver',
        ]);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $this->user->id,
            'badge_type' => 'silver',
        ]);

        // Set completed to 49 and complete another goal
        $this->user->total_completed_goals = 49;
        $this->user->save();

        // Reset progress record to allow completing it again
        UserGoalProgress::query()->delete();
        
        $this->actingAs($this->user)->postJson('/api/goals/progress/update', [
            'goal_id' => $this->template->id,
            'increment_value' => 100,
            'event_id' => 'evt_badge_gold',
        ]);

        $this->assertDatabaseHas('user_badges', [
            'user_id' => $this->user->id,
            'badge_type' => 'gold',
        ]);
    }

    /**
     * Test console cleanup command deletes old events.
     */
    public function test_ttl_cleanup_removes_old_events()
    {
        // Current event
        UserGoalProgressEvent::create([
            'user_id' => $this->user->id,
            'event_id' => 'current_event',
            'created_at' => Carbon::now('UTC'),
        ]);

        // Old event (created 8 days ago)
        UserGoalProgressEvent::create([
            'user_id' => $this->user->id,
            'event_id' => 'old_event',
            'created_at' => Carbon::now('UTC')->subDays(8),
        ]);

        $this->assertEquals(2, UserGoalProgressEvent::count());

        // Run the console command
        $this->artisan('GoalProgress:cleanup')
            ->assertSuccessful();

        $this->assertEquals(1, UserGoalProgressEvent::count());
        $this->assertDatabaseHas('user_goal_progress_events', [
            'event_id' => 'current_event',
        ]);
        $this->assertDatabaseMissing('user_goal_progress_events', [
            'event_id' => 'old_event',
        ]);
    }

    /**
     * Test administrative permissions block normal users from dashboard.
     */
    public function test_non_admin_cannot_access_goals_progress_dashboard()
    {
        $response = $this->actingAs($this->user)->get('/user-goal-progress');
        $response->assertStatus(403);
    }

    /**
     * Test admin users can access the dashboard.
     */
    public function test_admin_can_access_goals_progress_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/user-goal-progress');
        $response->assertStatus(200)
            ->assertViewIs('user-goal-progress.index');
    }
}
