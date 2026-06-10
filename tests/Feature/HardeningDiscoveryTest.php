<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Models\DynamicTranslationWarning;
use App\Services\TranslationRegistryService;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HardeningDiscoveryTest extends TestCase
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
        Language::updateOrCreate(['code' => 'ar'], [
            'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl',
            'is_active' => true, 'is_default' => false,
        ]);
    }

    /** @test */
    public function literal_json_key_extraction_works(): void
    {
        $tempPath = resource_path('views/temp_json_key_test.blade.php');
        File::put($tempPath, '<div>{{ __("Reset Password") }}</div><div>{{ __(\'Save changes\') }}</div>');

        $exitCode = Artisan::call('localization:scan');
        $this->assertEquals(0, $exitCode);
        File::delete($tempPath);

        $this->assertDatabaseHas('translation_keys', [
            'key' => 'Reset Password',
            'group' => 'general',
        ]);
        $this->assertDatabaseHas('translation_keys', [
            'key' => 'Save changes',
            'group' => 'general',
        ]);

        $keyRecord = TranslationKey::where('key', 'Reset Password')->firstOrFail();
        $enLang = Language::where('code', 'en')->firstOrFail();
        $this->assertDatabaseHas('ui_translations', [
            'translation_key_id' => $keyRecord->id,
            'language_id' => $enLang->id,
            'value' => 'Reset Password',
        ]);
    }

    /** @test */
    public function blade_attributes_extraction_works(): void
    {
        $tempPath = resource_path('views/temp_blade_attrs_test.blade.php');
        File::put($tempPath, '<x-input :label="__(\'user.name\')" placeholder="{{ __(\'enter_name\') }}"></x-input>');

        $exitCode = Artisan::call('localization:scan');
        $this->assertEquals(0, $exitCode);
        File::delete($tempPath);

        $this->assertDatabaseHas('translation_keys', ['key' => 'user.name']);
        $this->assertDatabaseHas('translation_keys', ['key' => 'enter_name']);
    }

    /** @test */
    public function dynamic_key_warning_detection_and_logging_works(): void
    {
        // Clear previous entries
        DynamicTranslationWarning::truncate();

        $tempPath = resource_path('views/temp_dynamic_test.blade.php');
        File::put($tempPath, '<div>{{ __($dynamicVar) }}</div><div>{{ __("prefix." . $variable) }}</div>');

        $exitCode = Artisan::call('localization:scan');
        $this->assertEquals(0, $exitCode);
        File::delete($tempPath);

        $this->assertDatabaseHas('dynamic_translation_warnings', [
            'expression' => '__($dynamicVar)',
        ]);
        $this->assertDatabaseHas('dynamic_translation_warnings', [
            'expression' => '__("prefix." . $variable)',
        ]);
    }

    /** @test */
    public function suffix_stripping_logic_works(): void
    {
        $this->assertEquals('Name', $this->registryService->generateDefaultEnglish('surah.fields.name_ar'));
        $this->assertEquals('First Name', $this->registryService->generateDefaultEnglish('user.fields.first_name_en'));
        $this->assertEquals('Menu Title', $this->registryService->generateDefaultEnglish('menu.title_ar'));
    }

    /** @test */
    public function language_sync_command_creates_missing_rows(): void
    {
        // Create key manually without translations
        $key = TranslationKey::create([
            'key' => 'custom.sync_test_key',
            'group' => 'custom',
        ]);

        // Delete translations to simulate missing state
        UiTranslation::where('translation_key_id', $key->id)->delete();

        $exitCode = Artisan::call('localization:sync-languages');
        $this->assertEquals(0, $exitCode);

        // Verify sync created rows for all active languages
        $activeLanguages = Language::active()->get();
        foreach ($activeLanguages as $lang) {
            $this->assertDatabaseHas('ui_translations', [
                'translation_key_id' => $key->id,
                'language_id' => $lang->id,
            ]);
        }
    }

    /** @test */
    public function cache_invalidation_on_ui_translation_save_and_delete(): void
    {
        $key = TranslationKey::create(['key' => 'cache.invalidate_test', 'group' => 'cache']);
        $lang = Language::where('code', 'en')->firstOrFail();

        // Ensure key-specific cache is cleared
        Cache::put('translation:coverage', 'old_coverage_value');
        Cache::put('translation:missing_report', 'old_report_value');

        $trans = UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $lang->id,
            'value' => 'Original Value',
        ]);

        $this->assertNull(Cache::get('translation:coverage'));
        $this->assertNull(Cache::get('translation:missing_report'));

        Cache::put('translation:coverage', 'old_coverage_value');
        $trans->value = 'Updated Value';
        $trans->save();
        $this->assertNull(Cache::get('translation:coverage'));

        Cache::put('translation:coverage', 'old_coverage_value');
        $trans->delete();
        $this->assertNull(Cache::get('translation:coverage'));
    }

    /** @test */
    public function export_format_parameters_on_localization_missing_works(): void
    {
        $jsonFile = base_path('temp_missing_report.json');
        $csvFile = base_path('temp_missing_report.csv');

        @unlink($jsonFile);
        @unlink($csvFile);

        $exitCode = Artisan::call('localization:missing', [
            '--json' => $jsonFile,
            '--csv' => $csvFile,
        ]);

        $this->assertEquals(0, $exitCode);

        $this->assertTrue(File::exists($jsonFile));
        $this->assertTrue(File::exists($csvFile));

        $jsonContent = json_decode(File::get($jsonFile), true);
        $this->assertArrayHasKey('stats', $jsonContent);
        $this->assertArrayHasKey('missing_keys', $jsonContent);

        $csvContent = File::get($csvFile);
        $this->assertStringContainsString('Issue Type', $csvContent);
        $this->assertStringContainsString('Key', $csvContent);
        $this->assertStringContainsString('Group', $csvContent);
        $this->assertStringContainsString('Detail/Value', $csvContent);

        @unlink($jsonFile);
        @unlink($csvFile);
    }
}
