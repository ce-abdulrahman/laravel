<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDailyGoal;
use App\Services\DailyGoalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGoalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test first ever access initializes today's goal with default 100.
     */
    public function test_initial_goal_defaults_to_one_hundred(): void
    {
        $user = User::factory()->create();
        $service = new DailyGoalService();

        $goal = $service->getTodayGoal($user);

        $this->assertEquals(100, $goal->goal_value);
        $this->assertEquals(0, $goal->today_progress);
        $this->assertFalse($goal->is_completed);
        $this->assertEquals(Carbon::now('UTC')->toDateString(), $goal->goal_date->toDateString());
    }

    /**
     * Test subsequent days carry over the last goal value.
     */
    public function test_subsequent_days_carry_over_previous_goal_value(): void
    {
        $user = User::factory()->create();
        $service = new DailyGoalService();

        // Mock a goal from 2 days ago with value 500
        $twoDaysAgo = Carbon::now('UTC')->subDays(2)->toDateString();
        $user->dailyGoals()->create([
            'goal_value' => 500,
            'today_progress' => 10,
            'goal_date' => $twoDaysAgo,
            'is_completed' => false,
        ]);

        $goal = $service->getTodayGoal($user);

        $this->assertEquals(500, $goal->goal_value);
        $this->assertEquals(0, $goal->today_progress);
        $this->assertFalse($goal->is_completed);
    }

    /**
     * Test incrementing progress update.
     */
    public function test_incrementing_progress_updates_db(): void
    {
        $user = User::factory()->create();
        $service = new DailyGoalService();

        $service->updateProgress($user, 50);
        $goal = $service->getTodayGoal($user);

        $this->assertEquals(50, $goal->today_progress);
        $this->assertFalse($goal->is_completed);

        // Completion
        $service->updateProgress($user, 60);
        $goal = $goal->fresh();

        $this->assertEquals(110, $goal->today_progress);
        $this->assertTrue($goal->is_completed);
    }

    /**
     * Test setting custom goal target.
     */
    public function test_setting_custom_goal_updates_and_recalculates_completion(): void
    {
        $user = User::factory()->create();
        $service = new DailyGoalService();

        $service->updateProgress($user, 150); // Progress is 150, Goal is 100 -> Completed
        $goal = $service->getTodayGoal($user);
        $this->assertTrue($goal->is_completed);

        // Increase goal to 200 -> In Progress
        $service->setGoal($user, 200);
        $goal = $goal->fresh();

        $this->assertEquals(200, $goal->goal_value);
        $this->assertFalse($goal->is_completed);
    }

    /**
     * Test API endpoint for today goal.
     */
    public function test_api_today_goal_endpoint_for_auth_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/daily-goal/today');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'goal_value' => 100,
                    'today_progress' => 0,
                    'percentage' => 0.0,
                    'is_completed' => false,
                ]
            ]);
    }

    /**
     * Test API endpoint for updating progress.
     */
    public function test_api_update_progress_endpoint_for_auth_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/daily-goal/update', [
                'increment_value' => 30
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'goal_value' => 100,
                    'today_progress' => 30,
                    'percentage' => 30.0,
                    'is_completed' => false,
                ]
            ]);
    }

    /**
     * Test guest API returns echoed data.
     */
    public function test_api_guest_endpoints_echo_calculations(): void
    {
        // 1. Get Today
        $response = $this->getJson('/api/v1/daily-goal/today?goal_value=300&today_progress=150');
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'goal_value' => 300,
                    'today_progress' => 150,
                    'percentage' => 50.0,
                    'is_completed' => false,
                ]
            ]);

        // 2. Update Progress
        $response = $this->postJson('/api/v1/daily-goal/update', [
            'increment_value' => 20,
            'goal_value' => 200,
            'today_progress' => 80
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'goal_value' => 200,
                    'today_progress' => 100,
                    'percentage' => 50.0,
                    'is_completed' => false,
                ]
            ]);

        // 3. Set Goal
        $response = $this->postJson('/api/v1/daily-goal/set', [
            'goal_value' => 400,
            'today_progress' => 400
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'goal_value' => 400,
                    'today_progress' => 400,
                    'percentage' => 100.0,
                    'is_completed' => true,
                ]
            ]);
    }

    /**
     * Test Admin user access.
     */
    public function test_admin_can_access_goals_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get('/user-goals');

        $response->assertStatus(200);
    }

    /**
     * Test Non-Admin user is forbidden.
     */
    public function test_non_admin_cannot_access_goals_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->get('/user-goals');

        $response->assertStatus(403);
    }
}
