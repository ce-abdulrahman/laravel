<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Models\User;
use App\Services\TranslationRegistryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TranslationDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected TranslationRegistryService $registryService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->registryService = app(TranslationRegistryService::class);

        // Seed languages
        Language::updateOrCreate(['code' => 'en'], [
            'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr',
            'is_active' => true, 'is_default' => true,
        ]);
        Language::updateOrCreate(['code' => 'ku'], [
            'name' => 'Kurdish', 'native_name' => 'کوردی', 'direction' => 'rtl',
            'is_active' => true, 'is_default' => false,
        ]);
    }

    /** @test */
    public function scanner_registers_new_keys_discovered_in_files(): void
    {
        $this->assertDatabaseCount('translation_keys', 0);

        // Create temporary blade file
        $tempPath = resource_path('views/temp_discovery_test.blade.php');
        File::put($tempPath, '<div>{{ __("discovered_scan_key.title") }}</div>');

        $exitCode = Artisan::call('localization:scan');
        $this->assertEquals(0, $exitCode);

        // Clean up immediately
        File::delete($tempPath);

        $this->assertDatabaseHas('translation_keys', [
            'key' => 'discovered_scan_key.title',
            'group' => 'discovered_scan_key',
        ]);

        // English default translation should be created
        $keyRecord = TranslationKey::where('key', 'discovered_scan_key.title')->first();
        $enLang = Language::where('code', 'en')->first();
        $this->assertDatabaseHas('ui_translations', [
            'translation_key_id' => $keyRecord->id,
            'language_id' => $enLang->id,
            'value' => 'Discovered Scan Key Title',
            'is_auto_generated' => 0,
        ]);

        // Kurdish translation row should exist but value is null/empty
        $kuLang = Language::where('code', 'ku')->first();
        $this->assertDatabaseHas('ui_translations', [
            'translation_key_id' => $keyRecord->id,
            'language_id' => $kuLang->id,
            'value' => null,
            'is_auto_generated' => 1,
        ]);
    }

    /** @test */
    public function english_default_generator_formats_correctly(): void
    {
        $this->assertEquals('Dashboard Title', $this->registryService->generateDefaultEnglish('dashboard.title'));
        $this->assertEquals('Name', $this->registryService->generateDefaultEnglish('surah.fields.name'));
        $this->assertEquals('Create', $this->registryService->generateDefaultEnglish('tajweed_rules.actions.create'));
        $this->assertEquals('Name', $this->registryService->generateDefaultEnglish('surah.fields.name_ar'));
        $this->assertEquals('Login', $this->registryService->generateDefaultEnglish('auth.actions.login'));
    }

    /** @test */
    public function runtime_auto_registration_registers_missing_keys_on_encounter(): void
    {
        $this->assertDatabaseCount('translation_keys', 0);

        // Call the translate/double underscore function with a non-existent key
        $resolved = __('runtime_encountered_key.welcome_back');

        // It should return the key (or debug prefixed key)
        $this->assertStringContainsString('runtime_encountered_key.welcome_back', $resolved);

        // The key should now be in the database
        $this->assertDatabaseHas('translation_keys', [
            'key' => 'runtime_encountered_key.welcome_back',
            'group' => 'runtime_encountered_key',
        ]);

        $keyRecord = TranslationKey::where('key', 'runtime_encountered_key.welcome_back')->first();
        $enLang = Language::where('code', 'en')->first();

        // English translation must have auto-generated value
        $this->assertDatabaseHas('ui_translations', [
            'translation_key_id' => $keyRecord->id,
            'language_id' => $enLang->id,
            'value' => 'Runtime Encountered Key Welcome Back',
        ]);
    }

    /** @test */
    public function sync_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('localization:sync');
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function admin_can_access_coverage_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create some sample keys and translations in the DB
        $key = TranslationKey::create(['key' => 'dashboard.title', 'group' => 'dashboard']);
        $lang = Language::where('code', 'en')->first();
        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $lang->id,
            'value' => 'Dashboard Title',
        ]);

        $response = $this->actingAs($admin)->get('/translations-manager/report');

        $response->assertStatus(200);
        $response->assertSee('Translation Coverage');
        $response->assertSee('Health Diagnostics');
        $response->assertSee('Overall Coverage');
        $response->assertSee('Coverage per Language');
        $response->assertSee('Coverage per UI Dotted Group');
    }
}
