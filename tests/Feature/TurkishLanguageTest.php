<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Models\User;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TurkishLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected Language $turkish;
    protected Language $english;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->english = Language::updateOrCreate(['code' => 'en'], [
            'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr',
            'is_active' => true, 'is_default' => true,
        ]);

        // Turkish is created via config syncFromConfig() in production
        // In tests, create it directly
        $this->turkish = Language::updateOrCreate(['code' => 'tr'], [
            'name'        => 'Turkish',
            'native_name' => 'Türkçe',
            'direction'   => 'ltr',
            'is_active'   => true,
            'is_default'  => false,
        ]);

        Language::updateOrCreate(['code' => 'ku'], [
            'name' => 'Kurdish', 'native_name' => 'کوردی', 'direction' => 'rtl',
            'is_active' => true, 'is_default' => false,
        ]);
        Language::updateOrCreate(['code' => 'ar'], [
            'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl',
            'is_active' => true, 'is_default' => false,
        ]);

        $this->admin = User::factory()->create(['role' => 'admin']);

        Cache::flush();
    }

    /** @test */
    public function turkish_language_exists_in_database_without_filesystem_file(): void
    {
        // Verify no lang/tr directory exists
        $trLangDir = resource_path('lang/tr');
        $trJsonFile = resource_path('lang/tr.json');

        $this->assertFalse(File::isDirectory($trLangDir),
            'There must be NO lang/tr directory (Turkish is DB-only)');
        $this->assertFalse(File::isFile($trJsonFile),
            'There must be NO lang/tr.json file (Turkish is DB-only)');

        // But Turkish must exist in the DB
        $this->assertDatabaseHas('languages', [
            'code'      => 'tr',
            'name'      => 'Turkish',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function turkish_is_in_languages_config(): void
    {
        $supported = config('languages.supported');
        $this->assertArrayHasKey('tr', $supported, 'Turkish must be in config/languages.php');
        $this->assertEquals('Turkish', $supported['tr']['name']);
        $this->assertEquals('ltr', $supported['tr']['direction']);
    }

    /** @test */
    public function turkish_language_appears_in_language_switcher(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        // The welcome page or header should mention Turkish in the language switcher
        // Check that the language endpoint exists for Turkish
        $switchResponse = $this->get('/language/tr');
        // Should redirect (language switch) not 404
        $this->assertNotEquals(404, $switchResponse->status(),
            '/language/tr must not return 404');
    }

    /** @test */
    public function switching_to_turkish_sets_app_locale(): void
    {
        $response = $this->get('/language/tr');
        // After switching, Turkish session locale should be set
        $response->assertSessionHas('locale', 'tr');
    }

    /** @test */
    public function t_helper_returns_fallback_for_turkish_if_no_tr_value(): void
    {
        $tk = TranslationKey::create(['key' => 'ui.tr_fallback', 'group' => 'ui']);

        // Only English value — Turkish is empty
        UiTranslation::create([
            'translation_key_id' => $tk->id,
            'language_id'        => $this->english->id,
            'value'              => 'English Fallback for Turkish',
        ]);
        UiTranslation::create([
            'translation_key_id' => $tk->id,
            'language_id'        => $this->turkish->id,
            'value'              => null, // no Turkish translation yet
        ]);

        Cache::flush();
        app()->setLocale('tr');

        $result = t('ui.tr_fallback');
        $this->assertEquals('English Fallback for Turkish', $result,
            'When Turkish value is null, must fall back to English');
    }

    /** @test */
    public function admin_can_add_turkish_translation_via_admin_ui(): void
    {
        $key = TranslationKey::create(['key' => 'ui.turkish_value', 'group' => 'ui']);

        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id'        => $this->english->id,
            'value'              => 'English Original',
        ]);
        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id'        => $this->turkish->id,
            'value'              => null,
        ]);

        // Admin adds Turkish translation via inline update
        $response = $this->actingAs($this->admin)
            ->put('/translations-manager/update-inline', [
                'translation_key_id' => $key->id,
                'language_id'        => $this->turkish->id,
                'value'              => 'Türkçe değer',
            ]);

        $response->assertStatus($response->isRedirect() ? 302 : 200);

        $this->assertDatabaseHas('ui_translations', [
            'translation_key_id' => $key->id,
            'language_id'        => $this->turkish->id,
            'value'              => 'Türkçe değer',
        ]);
    }

    /** @test */
    public function t_helper_returns_turkish_value_when_set_directly_in_db(): void
    {
        $key = TranslationKey::create(['key' => 'ui.turkish_direct', 'group' => 'ui']);

        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id'        => $this->english->id,
            'value'              => 'English Value',
        ]);
        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id'        => $this->turkish->id,
            'value'              => 'Türkçe doğrudan değer',
        ]);

        Cache::flush();
        app()->setLocale('tr');

        $result = t('ui.turkish_direct');
        $this->assertEquals('Türkçe doğrudan değer', $result,
            'Turkish value stored in DB must be returned when locale is tr');
    }

    /** @test */
    public function import_command_creates_empty_turkish_rows_for_all_keys(): void
    {
        // Run the real import with Turkish language in DB
        Artisan::call('localization:import');

        $keyCount  = TranslationKey::count();
        $trRows    = UiTranslation::where('language_id', $this->turkish->id)->count();

        $this->assertEquals($keyCount, $trRows,
            "Turkish must have {$keyCount} ui_translation rows (one per key), got {$trRows}");

        // None should have values (no lang/tr/*.php files)
        $withValue = UiTranslation::where('language_id', $this->turkish->id)
            ->whereNotNull('value')
            ->count();

        $this->assertEquals(0, $withValue,
            'Turkish must have no pre-filled values since there are no lang/tr source files');
    }

    /** @test */
    public function adding_turkish_required_no_new_migration(): void
    {
        // Verify no new migration file was needed for Turkish
        $migrations = collect(File::files(database_path('migrations')));
        $trMigration = $migrations->first(fn($f) => str_contains($f->getFilename(), 'turkish'));

        $this->assertNull($trMigration,
            'Adding Turkish language must NOT require any new migration file');
    }

    /** @test */
    public function turkish_language_does_not_require_blade_modification(): void
    {
        // Verify no Turkish-specific blade file exists
        $trBladeFiles = collect(File::allFiles(resource_path('views')))
            ->filter(fn($f) => str_contains($f->getPathname(), '_tr'))
            ->count();

        $this->assertEquals(0, $trBladeFiles,
            'Adding Turkish must not require any new blade files');
    }

    /** @test */
    public function turkish_language_is_listed_in_languages_admin_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/languages');

        $response->assertOk();
        $this->assertStringContainsString('Turkish', $response->getContent(),
            'Languages admin page must list Turkish');
    }
}
