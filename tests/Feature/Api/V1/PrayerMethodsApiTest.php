<?php

namespace Tests\Feature\Api\V1;

use App\Models\PrayerMethod;
use App\Models\PrayerSetting;
use App\Models\User;
use App\Models\UserPrayerSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrayerMethodsApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        $this->seed(\Database\Seeders\LanguageSeeder::class);
        $this->seed(\Database\Seeders\PrayerMethodSeeder::class);
    }

    public function test_guest_can_list_enabled_prayer_methods()
    {
        $response = $this->getJson('/api/v1/prayer-methods');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'methods' => [
                    '*' => [
                        'id',
                        'key',
                        'translation_key_name',
                        'translation_key_desc',
                        'config',
                        'is_default',
                        'is_user_active',
                    ]
                ],
                'default_method_key',
                'user_active_method_key',
                'active_method_key',
                'version_hash',
            ]
        ]);
    }

    public function test_api_returns_304_when_hash_matches()
    {
        $response = $this->getJson('/api/v1/prayer-methods');
        $response->assertStatus(200);

        $versionHash = $response->json('data.version_hash');

        // Test with If-None-Match header
        $response304 = $this->getJson('/api/v1/prayer-methods', [
            'If-None-Match' => '"' . $versionHash . '"'
        ]);
        $response304->assertStatus(304);

        // Test with version_hash query parameter
        $responseQuery304 = $this->getJson('/api/v1/prayer-methods?version_hash=' . $versionHash);
        $responseQuery304->assertStatus(304);
    }

    public function test_authenticated_user_can_update_prayer_method_preference()
    {
        $method = PrayerMethod::where('key', 'isna')->firstOrFail();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/user/prayer-method', [
                'prayer_method_key' => 'isna'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'User calculation method preference synchronized successfully.',
            'data' => [
                'active_method_key' => 'isna'
            ]
        ]);

        $this->assertDatabaseHas('user_prayer_settings', [
            'user_id' => $this->user->id,
            'prayer_method_id' => $method->id,
        ]);
    }

    public function test_user_cannot_select_disabled_calculation_method()
    {
        $method = PrayerMethod::where('key', 'isna')->firstOrFail();
        $method->update(['is_enabled' => false]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/user/prayer-method', [
                'prayer_method_key' => 'isna'
            ]);

        $response->assertStatus(422);
    }
}
