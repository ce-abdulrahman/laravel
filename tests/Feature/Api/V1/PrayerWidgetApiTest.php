<?php

namespace Tests\Feature\Api\V1;

use App\Models\City;
use App\Models\PrayerMethod;
use App\Models\WidgetSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrayerWidgetApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\LanguageSeeder::class);
        $this->seed(\Database\Seeders\PrayerMethodSeeder::class);
    }

    public function test_can_fetch_prayer_widget_payload()
    {
        $city = City::firstOrCreate(
            ['name' => 'Erbil'],
            [
                'lat' => 36.1912,
                'lng' => 44.0091,
                'timezone' => 'Asia/Baghdad',
            ]
        );

        WidgetSetting::create([
            'widget_enabled' => true,
            'widget_visibility' => 'always_visible',
            'widget_refresh_interval' => 300,
            'widget_default_city_id' => $city->id,
            'widget_display_order' => 1,
            'hijri_source' => 'tabular',
        ]);

        $response = $this->get('/api/v1/prayer-widget');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'success',
            'data' => [
                'next_prayer',
                'next_prayer_time',
                'next_prayer_remaining',
                'current_city',
                'hijri_date',
                'gregorian_date',
                'active_prayer_method',
                'timezone',
                'utc_offset',
                'dst_active',
                'prayer_times' => [
                    'fajr',
                    'sunrise',
                    'dhuhr',
                    'asr',
                    'maghrib',
                    'isha',
                ],
                'widget_settings' => [
                    'enabled',
                    'visibility',
                    'refresh_interval',
                    'display_order',
                ],
                'version_hash',
            ]
        ]);

        $this->assertEquals('Erbil', $response->json('data.current_city'));
    }

    public function test_coordinates_fallback_gps_over_settings()
    {
        $city = City::firstOrCreate(
            ['name' => 'Erbil'],
            [
                'lat' => 36.1912,
                'lng' => 44.0091,
                'timezone' => 'Asia/Baghdad',
            ]
        );

        WidgetSetting::create([
            'widget_default_city_id' => $city->id,
        ]);

        // Pass GPS coords in request
        $response = $this->get('/api/v1/prayer-widget?latitude=35.5619&longitude=45.4375&timezone=Asia/Baghdad');

        $response->assertStatus(200);
        // Should calculate using GPS instead of default city (so city name is 'Custom Location')
        $this->assertEquals('Custom Location', $response->json('data.current_city'));
    }

    public function test_coordinates_fallback_city_id_over_gps()
    {
        $erbil = City::firstOrCreate(
            ['name' => 'Erbil'],
            [
                'lat' => 36.1912,
                'lng' => 44.0091,
                'timezone' => 'Asia/Baghdad',
            ]
        );

        $sulaymaniyah = City::firstOrCreate(
            ['name' => 'Sulaymaniyah'],
            [
                'lat' => 35.5619,
                'lng' => 45.4375,
                'timezone' => 'Asia/Baghdad',
            ]
        );

        // Pass both city_id and GPS coords in request. city_id has higher priority.
        $response = $this->get('/api/v1/prayer-widget?city_id=' . $sulaymaniyah->id . '&latitude=36.1912&longitude=44.0091');

        $response->assertStatus(200);
        $this->assertEquals('Sulaymaniyah', $response->json('data.current_city'));
    }

    public function test_cache_etag_and_version_hash_validation()
    {
        $city = City::firstOrCreate(
            ['name' => 'Erbil'],
            [
                'lat' => 36.1912,
                'lng' => 44.0091,
                'timezone' => 'Asia/Baghdad',
            ]
        );

        WidgetSetting::create([
            'widget_default_city_id' => $city->id,
        ]);

        // Initial request
        $response = $this->get('/api/v1/prayer-widget');
        $response->assertStatus(200);
        
        $etag = $response->headers->get('ETag');
        $versionHash = $response->json('data.version_hash');

        $this->assertNotEmpty($etag);
        $this->assertNotEmpty($versionHash);

        // Call again with ETag in If-None-Match header -> 304
        $response2 = $this->get('/api/v1/prayer-widget', [
            'If-None-Match' => $etag
        ]);
        $response2->assertStatus(304);

        // Call again with version_hash in query -> 304
        $response3 = $this->get('/api/v1/prayer-widget?version_hash=' . $versionHash);
        $response3->assertStatus(304);
    }
}
