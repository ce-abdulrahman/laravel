<?php

namespace Tests\Feature;

use App\Models\PrayerMethod;
use App\Models\PrayerSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrayerMethodsAdminTest extends TestCase
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
        $this->seed(\Database\Seeders\PrayerMethodSeeder::class);
    }

    public function test_admin_can_view_prayer_methods_list()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.prayer-methods.index'));

        $response->assertStatus(200);
        $response->assertViewHas('methods');
        $response->assertViewHas('settings');
    }

    public function test_admin_can_update_prayer_method_config()
    {
        $method = PrayerMethod::where('key', 'isna')->firstOrFail();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.prayer-methods.update', $method->id), [
                'fajr_angle' => 16.5,
                'isha_angle' => 16.5,
                'sort_order' => 10,
            ]);

        $response->assertRedirect(route('admin.prayer-methods.index'));
        
        $method->refresh();
        $this->assertEquals(16.5, $method->config['fajr_angle']);
        $this->assertEquals(16.5, $method->config['isha_angle']);
        $this->assertEquals(10, $method->sort_order);
    }

    public function test_admin_can_toggle_prayer_method_enabled_status()
    {
        $method = PrayerMethod::where('key', 'isna')->firstOrFail();
        $this->assertTrue($method->is_enabled);

        // Toggle to disabled
        $response = $this->actingAs($this->admin)
            ->post(route('admin.prayer-methods.toggle-active', $method->id));

        $response->assertRedirect(route('admin.prayer-methods.index'));
        $method->refresh();
        $this->assertFalse($method->is_enabled);

        // Toggle back to enabled
        $response = $this->actingAs($this->admin)
            ->post(route('admin.prayer-methods.toggle-active', $method->id));

        $response->assertRedirect(route('admin.prayer-methods.index'));
        $method->refresh();
        $this->assertTrue($method->is_enabled);
    }

    public function test_admin_cannot_disable_currently_active_default_method()
    {
        $settings = PrayerSetting::firstOrCreate([]);
        $settings->update(['calculation_method' => 'kurdistan']);
        
        $method = PrayerMethod::where('key', 'kurdistan')->firstOrFail();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.prayer-methods.toggle-active', $method->id));

        $response->assertRedirect(route('admin.prayer-methods.index'));
        $response->assertSessionHas('error');
        
        $method->refresh();
        $this->assertTrue($method->is_enabled); // Status remains active
    }

    public function test_admin_can_set_active_default_fallback_method()
    {
        $settings = PrayerSetting::firstOrCreate([]);
        $settings->update(['calculation_method' => 'muslim_world_league']);
        
        $method = PrayerMethod::where('key', 'kurdistan')->firstOrFail();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.prayer-methods.set-default', $method->id));

        $response->assertRedirect(route('admin.prayer-methods.index'));
        $settings->refresh();
        $this->assertEquals('kurdistan', $settings->calculation_method);
    }

    public function test_admin_cannot_set_disabled_method_as_default()
    {
        $method = PrayerMethod::where('key', 'isna')->firstOrFail();
        $method->update(['is_enabled' => false]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.prayer-methods.set-default', $method->id));

        $response->assertRedirect(route('admin.prayer-methods.index'));
        $response->assertSessionHas('error');
    }
}
