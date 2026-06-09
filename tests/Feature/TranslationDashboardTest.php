<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_dashboard_redirects_guests(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_normal_user_cannot_see_translation_widget(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('Translation Coverage & Status');
        $response->assertDontSee('Total Languages');
        $response->assertDontSee('Translation Records');
        
        // Ensure stats array doesn't leak translation metrics to normal users
        $stats = $response->viewData('stats');
        $this->assertArrayNotHasKey('total_languages', $stats);
        $this->assertArrayNotHasKey('total_translation_records', $stats);
    }

    public function test_admin_user_can_see_translation_widget_with_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Translation Coverage & Status');
        $response->assertSee('Total Languages');
        $response->assertSee('Translation Records');
        $response->assertSee('Missing Count');
        $response->assertSee('Coverage');

        $stats = $response->viewData('stats');
        $this->assertArrayHasKey('total_languages', $stats);
        $this->assertArrayHasKey('total_translation_records', $stats);
        $this->assertArrayHasKey('missing_translations', $stats);
        $this->assertArrayHasKey('translation_coverage', $stats);
        $this->assertArrayHasKey('active_locales', $stats);

        $this->assertGreaterThanOrEqual(1, $stats['total_languages']);
        $this->assertGreaterThanOrEqual(0, $stats['total_translation_records']);
        $this->assertGreaterThanOrEqual(0, $stats['missing_translations']);
        $this->assertGreaterThanOrEqual(0.0, $stats['translation_coverage']);
        $this->assertLessThanOrEqual(100.0, $stats['translation_coverage']);
    }
}
