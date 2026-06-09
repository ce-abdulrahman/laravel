<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Models\User;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class TranslationEngineTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected TranslationService $service;
    protected Language $langEn;
    protected Language $langKu;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(TranslationService::class);
        $this->langEn = Language::where('code', 'en')->firstOrFail();
        $this->langKu = Language::where('code', 'ku')->firstOrFail();
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Set config for test stability
        Config::set('translations.auto_generate', true);
    }

    public function test_translation_helper_retrieves_translation(): void
    {
        $key = TranslationKey::create([
            'key' => 'home.welcome',
            'group' => 'home',
        ]);

        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'Welcome to Quran App',
        ]);

        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langKu->id,
            'value' => 'بەخێربێن بۆ بەرنامەی قورئان',
        ]);

        // Assert English translation works
        $this->assertEquals('Welcome to Quran App', t('home.welcome', [], 'en'));
        
        // Assert Kurdish translation works
        $this->assertEquals('بەخێربێن بۆ بەرنامەی قورئان', t('home.welcome', [], 'ku'));
    }

    public function test_translation_helper_fallback_to_default_language(): void
    {
        $key = TranslationKey::create([
            'key' => 'home.fallback_test',
            'group' => 'home',
        ]);

        // Default language (English) has translation
        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'Fallback English Text',
        ]);

        // Kurdish translation is empty/missing
        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langKu->id,
            'value' => '', // Empty value triggers fallback
        ]);

        // Query Kurdish - should fall back to English
        $this->assertEquals('Fallback English Text', t('home.fallback_test', [], 'ku'));
    }

    public function test_translation_helper_returns_key_when_both_missing(): void
    {
        // Set environment to testing to avoid local debug prefix '⚠'
        App::detectEnvironment(fn() => 'testing');

        // Retrieve key that does not exist at all in DB
        $this->assertEquals('missing.key.name', t('missing.key.name', [], 'en'));
    }

    public function test_translation_placeholder_replacement(): void
    {
        $key = TranslationKey::create([
            'key' => 'auth.welcome_user',
            'group' => 'auth',
        ]);

        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'Welcome, :name! Your email is {{email}}.',
        ]);

        $result = t('auth.welcome_user', [
            'name' => 'Ahmad',
            'email' => 'ahmad@example.com',
        ], 'en');

        $this->assertEquals('Welcome, Ahmad! Your email is ahmad@example.com.', $result);
    }

    public function test_auto_generated_option_is_respected(): void
    {
        Config::set('translations.auto_generate', true);

        // Before request, key does not exist
        $this->assertDatabaseMissing('translation_keys', ['key' => 'auto.generated.key']);

        // Call t() with missing key
        t('auto.generated.key', [], 'en');

        // Check key was generated
        $this->assertDatabaseHas('translation_keys', [
            'key' => 'auto.generated.key',
            'group' => 'auto',
        ]);

        // Check empty records created for all languages
        $keyRecord = TranslationKey::where('key', 'auto.generated.key')->firstOrFail();
        $this->assertDatabaseHas('ui_translations', [
            'translation_key_id' => $keyRecord->id,
            'language_id' => $this->langEn->id,
            'value' => null,
            'is_auto_generated' => true,
        ]);
        $this->assertDatabaseHas('ui_translations', [
            'translation_key_id' => $keyRecord->id,
            'language_id' => $this->langKu->id,
            'value' => null,
            'is_auto_generated' => true,
        ]);
    }

    public function test_no_auto_generation_when_disabled(): void
    {
        Config::set('translations.auto_generate', false);

        // Call t() with missing key
        t('no.generation.key', [], 'en');

        // Check key was NOT generated
        $this->assertDatabaseMissing('translation_keys', ['key' => 'no.generation.key']);
    }

    public function test_visual_debug_mode_in_local_environment(): void
    {
        // Mock app environment to local
        App::detectEnvironment(fn() => 'local');

        $this->assertEquals('⚠debug.missing.key', t('debug.missing.key', [], 'en'));

        // Reset to testing
        App::detectEnvironment(fn() => 'testing');
    }

    public function test_locale_middleware_resolves_from_session(): void
    {
        Session::put('locale', 'ku');
        $this->actingAs($this->admin)->get(route('dashboard'));
        $this->assertEquals('ku', App::getLocale());
    }

    public function test_locale_middleware_resolves_from_user_preference(): void
    {
        $user = User::factory()->create(['preferred_locale' => 'ar']);
        $this->actingAs($user)->get(route('dashboard'));
        $this->assertEquals('ar', App::getLocale());
    }

    public function test_locale_middleware_resolves_from_database_default(): void
    {
        // Default is English 'en'
        $this->get(route('dashboard'));
        $this->assertEquals('en', App::getLocale());
    }

    public function test_switch_lang_route_updates_session_and_user(): void
    {
        $user = User::factory()->create(['preferred_locale' => 'en']);

        $response = $this->actingAs($user)->get(route('lang.switch', ['code' => 'ku']));

        $response->assertRedirect();
        $this->assertEquals('ku', session('locale'));
        $this->assertEquals('ku', $user->fresh()->preferred_locale);
    }

    public function test_cache_invalidation_works(): void
    {
        $key = TranslationKey::create([
            'key' => 'cache.test',
            'group' => 'cache',
        ]);

        $trans = UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'First Value',
        ]);

        // Retrieve once to cache it
        $this->assertEquals('First Value', t('cache.test', [], 'en'));

        // Update inline via controller endpoint
        $response = $this->actingAs($this->admin)->put(route('translations-manager.update-inline'), [
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'Updated Cache Value',
        ]);

        $response->assertStatus(200);

        // Retrieve again - cache should be cleared and new value fetched
        $this->assertEquals('Updated Cache Value', t('cache.test', [], 'en'));
    }
}
