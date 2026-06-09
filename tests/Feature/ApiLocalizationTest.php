<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Surah;

class ApiLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * Test surah list localization based on Accept-Language header or query param.
     */
    public function test_surah_api_returns_localized_name(): void
    {
        // Default locale (en)
        $response = $this->getJson('/api/v1/surahs');
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', 'Al-Fatihah');

        // Kurdish locale via query param
        $response = $this->getJson('/api/v1/surahs?locale=ku');
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', 'کرانەوە / دەستپێک');

        // Arabic locale via Accept-Language header
        $response = $this->getJson('/api/v1/surahs', [
            'Accept-Language' => 'ar'
        ]);
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', 'سُورَةُ ٱلْفَاتِحَةِ');
    }

    /**
     * Test ayah translations API filters dynamically by request locale by default.
     */
    public function test_ayah_translations_filtered_by_locale(): void
    {
        // Fetch translations for Ayah 1 (Al-Fatihah, Ayah 1)
        // Default English
        $response = $this->getJson('/api/v1/ayahs/1/translations');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.language_code', 'en');

        // Kurdish query param
        $response = $this->getJson('/api/v1/ayahs/1/translations?locale=ku');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.language_code', 'ku');
    }
}
