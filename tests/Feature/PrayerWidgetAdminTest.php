<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\User;
use App\Models\WidgetSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrayerWidgetAdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->seed(\Database\Seeders\LanguageSeeder::class);
    }

    public function test_admin_can_view_widget_settings_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.prayer-widget-settings.index'));

        $response->assertStatus(200);
        $response->assertViewHas('settings');
        $response->assertViewHas('cities');
    }

    public function test_admin_can_update_widget_settings()
    {
        $city = City::firstOrCreate(
            ['name' => 'Erbil'],
            [
                'lat' => 36.1912,
                'lng' => 44.0091,
                'timezone' => 'Asia/Baghdad',
            ]
        );

        $response = $this->actingAs($this->admin)
            ->post(route('admin.prayer-widget-settings.update'), [
                'widget_enabled' => 0,
                'widget_visibility' => 'only_authenticated',
                'widget_refresh_interval' => 600,
                'widget_default_city_id' => $city->id,
                'widget_display_order' => 5,
                'hijri_source' => 'umm_al_qura',
            ]);

        $response->assertRedirect(route('admin.prayer-widget-settings.index'));
        $response->assertSessionHas('success');

        $settings = WidgetSetting::firstOrFail();
        $this->assertFalse($settings->widget_enabled);
        $this->assertEquals('only_authenticated', $settings->widget_visibility);
        $this->assertEquals(600, $settings->widget_refresh_interval);
        $this->assertEquals($city->id, $settings->widget_default_city_id);
        $this->assertEquals(5, $settings->widget_display_order);
        $this->assertEquals('umm_al_qura', $settings->hijri_source);
    }
}
