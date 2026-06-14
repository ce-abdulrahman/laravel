<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    /**
     * Test GET /api/v1/statistics/dashboard
     */
    public function test_user_can_access_dashboard_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/statistics/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'total_dhikr',
                    'current_streak',
                    'longest_streak',
                    'total_goals_completed',
                    'total_achievements',
                    'total_sessions',
                    'productivity_score',
                    'productivity_label',
                    'goal_completion_rate'
                ]
            ]);
    }

    /**
     * Test GET /api/v1/statistics/dhikr
     */
    public function test_user_can_access_dhikr_analytics_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/statistics/dhikr?period=7d');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'period',
                'data' => [
                    'total_current',
                    'total_previous',
                    'trend_pct',
                    'chart_data',
                    'breakdown'
                ]
            ]);
    }

    /**
     * Test GET /api/v1/statistics/sessions
     */
    public function test_user_can_access_sessions_analytics_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/statistics/sessions?period=30d');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'period',
                'data' => [
                    'total_sessions',
                    'avg_duration_seconds',
                    'longest_session_secs',
                    'avg_dhikr_per_minute',
                    'most_productive_hour',
                    'most_productive_day',
                    'sessions_trend_pct'
                ]
            ]);
    }

    /**
     * Test GET /api/v1/statistics/goals
     */
    public function test_user_can_access_goals_analytics_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/statistics/goals');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'period',
                'data' => [
                    'goals_completed',
                    'goals_missed',
                    'completion_rate',
                    'prev_rate',
                    'trend_pct',
                    'chart_data'
                ]
            ]);
    }

    /**
     * Test GET /api/v1/statistics/achievements
     */
    public function test_user_can_access_achievements_analytics_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/statistics/achievements');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'period',
                'data' => [
                    'total_earned',
                    'rare_earned',
                    'rare_total',
                    'completion_pct',
                    'timeline',
                    'next_achievement'
                ]
            ]);
    }

    /**
     * Test GET /api/v1/statistics/streaks
     */
    public function test_user_can_access_streaks_analytics_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/statistics/streaks?period=12m');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'period',
                'data' => [
                    'current_streak',
                    'longest_streak',
                    'total_streak_days',
                    'success_rate',
                    'heatmap'
                ]
            ]);
    }

    /**
     * Test GET /api/v1/statistics/leaderboard
     */
    public function test_user_can_access_leaderboard_analytics_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/statistics/leaderboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'current_rank',
                    'highest_rank',
                    'rank_history'
                ]
            ]);
    }

    /**
     * Test GET /api/v1/statistics/fingerprint
     */
    public function test_user_can_access_fingerprint_analytics_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/statistics/fingerprint');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'period',
                'data' => [
                    'total_counts',
                    'total_sessions',
                    'period_sessions',
                    'avg_session_duration',
                    'favorite_mode',
                    'avg_touch_rate_pm',
                    'blind_sessions',
                    'focus_sessions'
                ]
            ]);
    }

    /**
     * Test GET /api/v1/statistics/reminders
     */
    public function test_user_can_access_reminders_analytics_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/statistics/reminders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'period',
                'data' => [
                    'notifications_sent',
                    'notifications_opened',
                    'open_rate_pct',
                    'by_channel'
                ]
            ]);
    }

    /**
     * Test GET /api/v1/statistics/insights
     */
    public function test_user_can_access_insights_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/statistics/insights');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data'
            ]);
    }

    /**
     * Test GET /api/v1/statistics/milestones
     */
    public function test_user_can_access_milestones_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/statistics/milestones');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data'
            ]);
    }

    /**
     * Test POST /api/v1/statistics/refresh
     */
    public function test_user_can_trigger_statistics_recalculation(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/statistics/refresh');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
    }
}
