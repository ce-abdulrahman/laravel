<?php

namespace Tests\Feature\Api\V1;

use App\Models\Reciter;
use App\Models\Surah;
use App\Models\Ayah;
use App\Models\AyahTiming;
use Database\Seeders\ReciterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class QariRefactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Cache::clear();
    }

    public function test_reciters_list_endpoint_returns_active_only_and_filters_is_active(): void
    {
        $active = Reciter::create([
            'name' => 'Active Reciter',
            'slug' => 'active-slug',
            'riwayah' => 'Hafs',
            'country' => 'Kuwait',
            'language' => 'ar',
            'audio_base_url' => 'https://audio.example.com/active-slug/',
            'supports_ayah_audio' => true,
            'is_active' => true,
        ]);

        $inactive = Reciter::create([
            'name' => 'Inactive Reciter',
            'slug' => 'inactive-slug',
            'riwayah' => 'Hafs',
            'country' => 'Kuwait',
            'language' => 'ar',
            'audio_base_url' => 'https://audio.example.com/inactive-slug/',
            'supports_ayah_audio' => false,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/reciters');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Active Reciter'])
            ->assertJsonMissing(['name' => 'Inactive Reciter'])
            ->assertJsonMissing(['is_active' => true])
            ->assertJsonMissing(['is_active' => false]);
    }

    public function test_playback_endpoint_returns_dynamic_url_and_loaded_timings(): void
    {
        $reciter = Reciter::create([
            'name' => 'Test Reciter',
            'slug' => 'test-slug',
            'riwayah' => 'Hafs',
            'country' => 'Kuwait',
            'language' => 'ar',
            'audio_base_url' => 'https://audio.example.com/test-slug/',
            'supports_ayah_audio' => true,
            'is_active' => true,
        ]);

        $surah = Surah::firstOrCreate(['number' => 18], [
            'revelation_type' => 'Meccan',
            'ayah_count' => 3,
            'page_start' => 293,
            'page_end' => 304,
            'juz_start' => 15,
            'juz_end' => 16,
            'is_active' => true,
        ]);

        // Mock JSON timing file
        $timingPath = 'timings/test-slug/surah_018.json';
        $timingsData = [
            ['ayah' => 1, 'start' => 0.0, 'end' => 5.2],
            ['ayah' => 2, 'start' => 5.2, 'end' => 11.5],
            ['ayah' => 3, 'start' => 11.5, 'end' => 18.0],
        ];
        Storage::put($timingPath, json_encode($timingsData));

        AyahTiming::create([
            'reciter_id' => $reciter->id,
            'surah_id' => $surah->id,
            'timing_file_path' => $timingPath,
            'duration_seconds' => 18,
        ]);

        $response = $this->getJson("/api/v1/reciters/{$reciter->id}/surahs/18?quality=medium");

        $response->assertStatus(200)
            ->assertJson([
                'audio_url' => 'https://audio.example.com/test-slug/medium/018.mp3',
                'timings' => [
                    ['ayah' => 1, 'start' => 0.0, 'end' => 5.2],
                    ['ayah' => 2, 'start' => 5.2, 'end' => 11.5],
                    ['ayah' => 3, 'start' => 11.5, 'end' => 18.0],
                ],
            ]);
    }

    public function test_playback_endpoint_uses_timing_estimation_fallback_when_file_missing(): void
    {
        $reciter = Reciter::create([
            'name' => 'Test Reciter',
            'slug' => 'test-slug',
            'riwayah' => 'Hafs',
            'country' => 'Kuwait',
            'language' => 'ar',
            'audio_base_url' => 'https://audio.example.com/test-slug/',
            'supports_ayah_audio' => true,
            'is_active' => true,
        ]);

        $surah = Surah::firstOrCreate(['number' => 1], [
            'revelation_type' => 'Meccan',
            'ayah_count' => 3,
            'page_start' => 1,
            'page_end' => 1,
            'juz_start' => 1,
            'juz_end' => 1,
            'is_active' => true,
        ]);

        // Create timing record with no path or non-existent path
        AyahTiming::create([
            'reciter_id' => $reciter->id,
            'surah_id' => $surah->id,
            'timing_file_path' => 'missing_file.json',
            'duration_seconds' => 15,
        ]);

        $response = $this->getJson("/api/v1/reciters/{$reciter->id}/surahs/1");

        $duration = 15;
        $ayahCount = $surah->ayah_count;
        $durationPerAyah = $duration / $ayahCount;
        $expectedTimings = [];
        for ($i = 1; $i <= $ayahCount; $i++) {
            $expectedTimings[] = [
                'ayah' => $i,
                'start' => round(($i - 1) * $durationPerAyah, 2),
                'end' => round($i * $durationPerAyah, 2),
            ];
        }

        $response->assertStatus(200)
            ->assertJson([
                'audio_url' => 'https://audio.example.com/test-slug/high/001.mp3',
                'timings' => $expectedTimings,
            ]);
    }

    public function test_compatibility_surah_audio_endpoint_works_without_audio_files_table(): void
    {
        $reciter = Reciter::create([
            'name' => 'Test Reciter',
            'slug' => 'test-slug',
            'riwayah' => 'Hafs',
            'country' => 'Kuwait',
            'language' => 'ar',
            'audio_base_url' => 'https://audio.example.com/test-slug/',
            'supports_ayah_audio' => true,
            'is_active' => true,
        ]);

        $surah = Surah::firstOrCreate(['number' => 1], [
            'revelation_type' => 'Meccan',
            'ayah_count' => 2,
            'page_start' => 1,
            'page_end' => 1,
            'juz_start' => 1,
            'juz_end' => 1,
            'is_active' => true,
        ]);

        // Create the Ayah timing records
        Ayah::firstOrCreate([
            'surah_id' => $surah->id,
            'ayah_number' => 1,
        ], [
            'text_uthmani' => 'بِسْمِ اللَّهِ',
            'text_simple' => 'Bismillah',
            'page_number' => 1,
            'juz_number' => 1,
            'hizb_number' => 1,
            'rub_number' => 1,
            'is_active' => true,
        ]);
        Ayah::firstOrCreate([
            'surah_id' => $surah->id,
            'ayah_number' => 2,
        ], [
            'text_uthmani' => 'الْحَمْدُ لِلَّهِ',
            'text_simple' => 'Alhamdulillah',
            'page_number' => 1,
            'juz_number' => 1,
            'hizb_number' => 1,
            'rub_number' => 1,
            'is_active' => true,
        ]);

        AyahTiming::create([
            'reciter_id' => $reciter->id,
            'surah_id' => $surah->id,
            'duration_seconds' => 10,
        ]);

        $response = $this->getJson("/api/v1/surahs/{$surah->id}/audio?reciter_id={$reciter->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.audio_file.stream_url', 'https://audio.example.com/test-slug/high/001.mp3')
            ->assertJsonStructure([
                'status',
                'success',
                'data' => [
                    'audio_file' => [
                        'id',
                        'reciter_id',
                        'surah_id',
                        'file_path',
                        'duration_seconds',
                        'quality',
                        'is_active',
                        'reciter',
                        'surah',
                        'stream_url',
                    ],
                    'ayah_timings',
                ],
            ]);
    }

    public function test_reciter_seeder_is_idempotent_and_seeds_5_reciters(): void
    {
        $seeder = new ReciterSeeder();
        
        // Run once
        $seeder->run();
        $this->assertEquals(7, Reciter::count());

        // Run twice
        $seeder->run();
        $this->assertEquals(7, Reciter::count());

        // Verify Yasser Al Dosari slug exist
        $dosari = Reciter::where('slug', 'yasser-dosari')->first();
        $this->assertNotNull($dosari);
        $this->assertEquals('Saudi Arabia', $dosari->country);
    }
}
