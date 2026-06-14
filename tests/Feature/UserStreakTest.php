<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserTasbihStreak;
use App\Services\StreakService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStreakTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test first ever activity starts a streak at 1.
     */
    public function test_first_activity_starts_streak_at_one(): void
    {
        $user = User::factory()->create();
        $service = app(StreakService::class);

        $streak = $service->updateStreak($user);

        $this->assertEquals(1, $streak->current_streak);
        $this->assertEquals(1, $streak->longest_streak);
        $this->assertEquals(Carbon::now('UTC')->toDateString(), $streak->last_activity_date->toDateString());
    }

    /**
     * Test idempotency on same day activity.
     */
    public function test_same_day_activity_is_idempotent(): void
    {
        $user = User::factory()->create();
        $service = app(StreakService::class);

        // Perform first activity today
        $service->updateStreak($user);

        // Perform second activity today
        $streak = $service->updateStreak($user);

        $this->assertEquals(1, $streak->current_streak);
        $this->assertEquals(1, $streak->longest_streak);
    }

    /**
     * Test consecutive day activity increments streak.
     */
    public function test_consecutive_day_activity_increments_streak(): void
    {
        $user = User::factory()->create();
        $service = app(StreakService::class);

        // Mock yesterday's activity
        Carbon::setTestNow(Carbon::now('UTC')->subDay());
        $service->updateStreak($user);

        // Perform today's activity
        Carbon::setTestNow(Carbon::now('UTC')->addDay()); // Back to today
        $streak = $service->updateStreak($user);

        $this->assertEquals(2, $streak->current_streak);
        $this->assertEquals(2, $streak->longest_streak);
        
        Carbon::setTestNow(); // Reset test time
    }

    /**
     * Test gap in activity resets streak to 1.
     */
    public function test_gap_resets_streak_to_one(): void
    {
        $user = User::factory()->create();
        $service = app(StreakService::class);

        // Mock activity 3 days ago
        Carbon::setTestNow(Carbon::now('UTC')->subDays(3));
        $service->updateStreak($user);

        // Perform today's activity
        Carbon::setTestNow(); // Back to today
        $streak = $service->updateStreak($user);

        $this->assertEquals(1, $streak->current_streak);
        // Longest streak should remain 1 from previous activity
        $this->assertEquals(1, $streak->longest_streak);
    }

    /**
     * Test mobile sync resolution.
     */
    public function test_mobile_sync_overrides_with_higher_offline_values(): void
    {
        $user = User::factory()->create();
        $service = app(StreakService::class);

        $today = Carbon::now('UTC')->toDateString();

        // User performed a 5-day streak offline, syncing now
        $streak = $service->updateStreak($user, 5, 7, $today);

        $this->assertEquals(5, $streak->current_streak);
        $this->assertEquals(7, $streak->longest_streak);
        $this->assertEquals($today, $streak->last_activity_date->toDateString());
    }

    /**
     * Test API endpoint updates streak.
     */
    public function test_api_endpoint_updates_streak(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/streak/update');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'current_streak' => 1,
                    'longest_streak' => 1,
                    'last_activity_date' => Carbon::now('UTC')->toDateString(),
                ]
            ]);
    }

    /**
     * Test Admin user can access streaks dashboard.
     */
    public function test_admin_can_access_streaks_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get('/user-streaks');

        $response->assertStatus(200);
    }

    /**
     * Test non-admin cannot access streaks dashboard.
     */
    public function test_non_admin_cannot_access_streaks_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->get('/user-streaks');

        $response->assertStatus(403);
    }

    /**
     * Test guest updates streak.
     */
    public function test_guest_streak_update_returns_echoed_stats(): void
    {
        $today = Carbon::now('UTC')->toDateString();

        $response = $this->postJson('/api/v1/streak/update', [
            'current_streak' => 3,
            'longest_streak' => 5,
            'last_activity_date' => $today,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'current_streak' => 3,
                    'longest_streak' => 5,
                    'last_activity_date' => $today,
                ]
            ]);
    }
}

