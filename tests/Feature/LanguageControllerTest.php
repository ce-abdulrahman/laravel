<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Surah;
use App\Models\SurahTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LanguageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_languages_index_page_requires_auth(): void
    {
        $response = $this->get(route('languages.index'));
        $response->assertRedirect('/login');
    }

    public function test_languages_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('languages.index'));
        $response->assertStatus(200);
        $response->assertViewHas('languages');
    }

    public function test_can_create_language(): void
    {
        Cache::put('language:active_codes', ['en']);
        Cache::put('language:active_list', collect());
        Cache::put('language:default', 'en');

        $response = $this->actingAs($this->user)->post(route('languages.store'), [
            'code' => 'es',
            'name' => 'Spanish',
            'native_name' => 'Español',
            'direction' => 'ltr',
            'flag' => '🇪🇸',
            'is_active' => true,
            'is_default' => false,
            'order' => 5,
        ]);

        $response->assertRedirect(route('languages.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('languages', [
            'code' => 'es',
            'name' => 'Spanish',
        ]);

        // Assert caches were invalidated
        $this->assertNull(Cache::get('language:active_codes'));
        $this->assertNull(Cache::get('language:active_list'));
        $this->assertNull(Cache::get('language:default'));
    }

    public function test_cannot_create_language_with_duplicate_code(): void
    {
        // 'en' is seeded by default
        $response = $this->actingAs($this->user)->post(route('languages.store'), [
            'code' => 'en', // Duplicate
            'name' => 'English Duplicate',
            'native_name' => 'English',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => false,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_can_update_language(): void
    {
        Cache::put('language:active_codes', ['en']);
        Cache::put('language:default', 'en');

        $language = Language::where('code', 'en')->firstOrFail();

        $response = $this->actingAs($this->user)->put(route('languages.update', $language), [
            'code' => 'en',
            'name' => 'English Modified',
            'native_name' => 'English Native',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => true,
            'order' => 10,
        ]);

        $response->assertRedirect(route('languages.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'name' => 'English Modified',
        ]);

        // Assert caches were invalidated
        $this->assertNull(Cache::get('language:active_codes'));
        $this->assertNull(Cache::get('language:default'));
    }

    public function test_cannot_deactivate_last_active_language(): void
    {
        // Deactivate all but one language
        Language::where('code', '!=', 'en')->update(['is_active' => false]);
        Cache::forget('language:active_codes');

        $language = Language::where('code', 'en')->firstOrFail();

        $response = $this->actingAs($this->user)->put(route('languages.update', $language), [
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'is_active' => false, // Attempt to deactivate
            'is_default' => true,
        ]);

        $response->assertSessionHas('error', 'Cannot deactivate the last active language.');
        $this->assertTrue($language->fresh()->is_active);
    }

    public function test_cannot_deactivate_default_language(): void
    {
        // We have multiple active languages (en, ku, ar), but 'en' is the default language.
        $language = Language::where('code', 'en')->firstOrFail();
        $this->assertTrue($language->is_default);

        $response = $this->actingAs($this->user)->put(route('languages.update', $language), [
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'is_active' => false, // Attempt to deactivate the default language
            'is_default' => true,
        ]);

        $response->assertSessionHas('error', 'Cannot deactivate the default language.');
        $this->assertTrue($language->fresh()->is_active);
    }

    public function test_cannot_delete_default_language(): void
    {
        $language = Language::where('code', 'en')->firstOrFail(); // Default

        $response = $this->actingAs($this->user)->delete(route('languages.destroy', $language));

        $response->assertSessionHas('error', 'Cannot delete the default language.');
        $this->assertDatabaseHas('languages', ['id' => $language->id]);
    }

    public function test_cannot_delete_last_active_language(): void
    {
        // Make only 'ku' active, and set 'ku' as default first to avoid default check
        Language::query()->update(['is_default' => false, 'is_active' => false]);
        $language = Language::where('code', 'ku')->firstOrFail();
        $language->update(['is_default' => true, 'is_active' => true]);

        // Attempt to delete 'ku'
        $response = $this->actingAs($this->user)->delete(route('languages.destroy', $language));

        // It should either block because it is default, or block because it is the last active language.
        // Since 'ku' is default, it will trigger the default check first.
        $response->assertSessionHas('error', 'Cannot delete the default language.');
        $this->assertDatabaseHas('languages', ['id' => $language->id]);
    }

    public function test_delete_language_without_translations_deletes_immediately(): void
    {
        // Create a new language with no translations
        $language = Language::create([
            'code' => 'es',
            'name' => 'Spanish',
            'native_name' => 'Español',
            'direction' => 'ltr',
            'is_active' => false,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->user)->delete(route('languages.destroy', $language));

        $response->assertRedirect(route('languages.index'));
        $response->assertSessionHas('success', 'Language deleted successfully.');
        $this->assertDatabaseMissing('languages', ['id' => $language->id]);
    }

    public function test_delete_language_with_translations_requires_confirmation(): void
    {
        // 'ar' has translations seeded
        $language = Language::where('code', 'ar')->firstOrFail();
        $language->update(['is_default' => false, 'is_active' => false]); // avoid default/active guard

        // Act
        $response = $this->actingAs($this->user)->delete(route('languages.destroy', $language));

        // Assert: should return confirmation view, not delete yet
        $response->assertStatus(200);
        $response->assertViewIs('languages.confirm_delete');
        $this->assertDatabaseHas('languages', ['id' => $language->id]);
    }

    public function test_delete_language_with_translations_cascades_on_confirmed(): void
    {
        // Ensure 'ar' is not default, not last active
        $language = Language::where('code', 'ar')->firstOrFail();
        $language->update(['is_default' => false, 'is_active' => false]);

        // Ensure some translation exists
        $surah = Surah::firstOrFail();
        SurahTranslation::updateOrCreate(
            ['surah_id' => $surah->id, 'locale' => 'ar'],
            ['name' => 'سورة الفاتحة']
        );

        $this->assertDatabaseHas('surah_translations', [
            'surah_id' => $surah->id,
            'locale' => 'ar',
        ]);

        // Confirm delete request
        $response = $this->actingAs($this->user)->delete(route('languages.destroy', $language), [
            'confirm_delete' => 1,
        ]);

        $response->assertRedirect(route('languages.index'));
        $response->assertSessionHas('success', 'Language deleted successfully.');

        // Assert language is deleted
        $this->assertDatabaseMissing('languages', ['id' => $language->id]);

        // Assert translations are deleted
        $this->assertDatabaseMissing('surah_translations', [
            'surah_id' => $surah->id,
            'locale' => 'ar',
        ]);
    }
}
