<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Reciter;
use App\Models\Surah;
use App\Models\AudioFavorite;
use App\Models\AudioDownload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AudioIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    private function createReciter(string $name = 'Test Reciter', string $slug = 'test-reciter'): Reciter
    {
        return Reciter::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'riwayah' => 'Hafs',
                'country' => 'Kuwait',
                'language' => 'ar',
                'audio_base_url' => 'https://audio.example.com/' . $slug . '/',
                'supports_ayah_audio' => true,
                'is_active' => true,
            ]
        );
    }

    private function createSurah(int $number = 1, string $nameAr = 'سورة الفاتحة'): Surah
    {
        return Surah::firstOrCreate(
            ['number' => $number],
            [
                'revelation_type' => 'meccan',
                'ayah_count' => 7,
                'name_ar' => $nameAr,
                'is_active' => true,
            ]
        );
    }

    public function test_user_can_toggle_reciter_favorite(): void
    {
        $user = User::factory()->create();
        $reciter = $this->createReciter();

        // 1. Add favorite
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/favorites/toggle', [
                'favoritable_type' => 'reciter',
                'favoritable_id' => $reciter->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Favorite added',
                'data' => [
                    'is_favorite' => true,
                    'favoritable_type' => 'reciter',
                    'favoritable_id' => $reciter->id,
                ]
            ]);

        $this->assertDatabaseHas('audio_favorites', [
            'user_id' => $user->id,
            'favoritable_type' => Reciter::class,
            'favoritable_id' => $reciter->id,
        ]);

        // 2. Remove favorite
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/favorites/toggle', [
                'favoritable_type' => 'reciter',
                'favoritable_id' => $reciter->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Favorite removed',
                'data' => [
                    'is_favorite' => false,
                    'favoritable_type' => 'reciter',
                    'favoritable_id' => $reciter->id,
                ]
            ]);

        $this->assertDatabaseMissing('audio_favorites', [
            'user_id' => $user->id,
            'favoritable_type' => Reciter::class,
            'favoritable_id' => $reciter->id,
        ]);
    }

    public function test_user_can_toggle_surah_favorite(): void
    {
        $user = User::factory()->create();
        $surah = $this->createSurah();

        // Add favorite
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/favorites/toggle', [
                'favoritable_type' => 'surah',
                'favoritable_id' => $surah->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Favorite added',
                'data' => [
                    'is_favorite' => true,
                    'favoritable_type' => 'surah',
                    'favoritable_id' => $surah->id,
                ]
            ]);

        $this->assertDatabaseHas('audio_favorites', [
            'user_id' => $user->id,
            'favoritable_type' => Surah::class,
            'favoritable_id' => $surah->id,
        ]);
    }

    public function test_toggle_fails_for_non_existent_entity(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/favorites/toggle', [
                'favoritable_type' => 'surah',
                'favoritable_id' => 9999,
            ]);

        $response->assertStatus(404);
    }

    public function test_user_can_fetch_favorites_grouped_by_type(): void
    {
        $user = User::factory()->create();
        $reciter = $this->createReciter();
        $surah = $this->createSurah();

        AudioFavorite::create([
            'user_id' => $user->id,
            'favoritable_type' => Reciter::class,
            'favoritable_id' => $reciter->id,
        ]);

        AudioFavorite::create([
            'user_id' => $user->id,
            'favoritable_type' => Surah::class,
            'favoritable_id' => $surah->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/favorites');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'reciter_ids' => [$reciter->id],
                    'surah_ids' => [$surah->id],
                ]
            ]);
    }

    public function test_user_can_store_and_update_audio_download_status(): void
    {
        $user = User::factory()->create();
        $reciter = $this->createReciter();
        $surah = $this->createSurah();

        // 1. Store as downloading
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/audio-downloads', [
                'reciter_id' => $reciter->id,
                'surah_id' => $surah->id,
                'status' => 'downloading',
                'progress' => 45.5,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Download status updated successfully',
                'data' => [
                    'user_id' => $user->id,
                    'reciter_id' => $reciter->id,
                    'surah_id' => $surah->id,
                    'status' => 'downloading',
                    'progress' => 45.5,
                ]
            ]);

        $this->assertDatabaseHas('audio_downloads', [
            'user_id' => $user->id,
            'reciter_id' => $reciter->id,
            'surah_id' => $surah->id,
            'status' => 'downloading',
            'progress' => 45.5,
        ]);

        // 2. Update to completed
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/audio-downloads', [
                'reciter_id' => $reciter->id,
                'surah_id' => $surah->id,
                'status' => 'completed',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'status' => 'completed',
                    'progress' => 100.0,
                ]
            ]);
    }

    public function test_user_can_fetch_their_audio_downloads(): void
    {
        $user = User::factory()->create();
        $reciter = $this->createReciter();
        $surah = $this->createSurah();

        AudioDownload::create([
            'user_id' => $user->id,
            'reciter_id' => $reciter->id,
            'surah_id' => $surah->id,
            'status' => 'completed',
            'progress' => 100.0,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/audio-downloads');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    [
                        'user_id' => $user->id,
                        'reciter_id' => $reciter->id,
                        'surah_id' => $surah->id,
                        'status' => 'completed',
                        'progress' => 100.0,
                    ]
                ]
            ]);
    }
}
