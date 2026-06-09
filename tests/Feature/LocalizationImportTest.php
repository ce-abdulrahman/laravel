<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LocalizationImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the 4 languages that are in config/languages.php
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
        Language::updateOrCreate(['code' => 'tr'], [
            'name' => 'Turkish', 'native_name' => 'Türkçe', 'direction' => 'ltr',
            'is_active' => true, 'is_default' => false,
        ]);
    }

    /** @test */
    public function import_command_scans_all_lang_files_and_inserts_keys(): void
    {
        $this->assertDatabaseCount('translation_keys', 0);
        $this->assertDatabaseCount('ui_translations', 0);

        $exitCode = Artisan::call('localization:import');
        $this->assertEquals(0, $exitCode, 'Import command must exit with 0');

        // We have 34 PHP files per locale × 3 locales + 3 JSON files = 105 files
        // Keys should be > 1000
        $keyCount = TranslationKey::count();
        $this->assertGreaterThan(1000, $keyCount,
            "Expected >1000 translation keys, got {$keyCount}");
    }

    /** @test */
    public function import_creates_ui_translation_rows_for_all_active_languages(): void
    {
        Artisan::call('localization:import');

        $activeLanguageCount = Language::where('is_active', true)->count();
        $this->assertEquals(4, $activeLanguageCount);

        // Every translation key must have a ui_translation row for each active language
        $keyCount   = TranslationKey::count();
        $transCount = UiTranslation::count();

        $this->assertEquals(
            $keyCount * $activeLanguageCount,
            $transCount,
            "Expected {$keyCount} keys × {$activeLanguageCount} languages = " .
            ($keyCount * $activeLanguageCount) . " ui_translation rows, got {$transCount}"
        );
    }

    /** @test */
    public function import_stores_correct_english_value_for_known_key(): void
    {
        Artisan::call('localization:import');

        $this->assertDatabaseHas('translation_keys', ['key' => 'dashboard.title']);

        $key  = TranslationKey::where('key', 'dashboard.title')->first();
        $lang = Language::where('code', 'en')->first();

        $trans = UiTranslation::where('translation_key_id', $key->id)
            ->where('language_id', $lang->id)
            ->first();

        $this->assertNotNull($trans);
        $this->assertEquals('Dashboard', $trans->value);
    }

    /** @test */
    public function import_stores_correct_arabic_value_for_known_key(): void
    {
        Artisan::call('localization:import');

        $key  = TranslationKey::where('key', 'auth.login')->first();
        $lang = Language::where('code', 'ar')->first();

        $this->assertNotNull($key, 'auth.login key must exist after import');

        $trans = UiTranslation::where('translation_key_id', $key->id)
            ->where('language_id', $lang->id)
            ->first();

        $this->assertNotNull($trans);
        $this->assertNotEmpty($trans->value, 'Arabic value for auth.login must not be empty');
    }

    /** @test */
    public function import_stores_correct_kurdish_value_for_known_key(): void
    {
        Artisan::call('localization:import');

        $key  = TranslationKey::where('key', 'common.save')->first();
        $lang = Language::where('code', 'ku')->first();

        $this->assertNotNull($key, 'common.save key must exist after import');

        $trans = UiTranslation::where('translation_key_id', $key->id)
            ->where('language_id', $lang->id)
            ->first();

        $this->assertNotNull($trans);
        $this->assertNotEmpty($trans->value, 'Kurdish value for common.save must not be empty');
    }

    /** @test */
    public function import_is_idempotent_running_twice_does_not_duplicate_keys(): void
    {
        Artisan::call('localization:import');
        $keyCountAfterFirst = TranslationKey::count();
        $transCountAfterFirst = UiTranslation::count();

        // Run again — must produce same counts
        Artisan::call('localization:import');
        $keyCountAfterSecond = TranslationKey::count();
        $transCountAfterSecond = UiTranslation::count();

        $this->assertEquals($keyCountAfterFirst, $keyCountAfterSecond,
            'Running import twice must not create duplicate translation keys');
        $this->assertEquals($transCountAfterFirst, $transCountAfterSecond,
            'Running import twice must not create duplicate ui_translation rows');
    }

    /** @test */
    public function import_groups_php_file_keys_correctly(): void
    {
        Artisan::call('localization:import');

        // dashboard.php keys should be in group 'dashboard'
        $key = TranslationKey::where('key', 'dashboard.title')->first();
        $this->assertNotNull($key);
        $this->assertEquals('dashboard', $key->group);

        // auth.php keys should be in group 'auth'
        $authKey = TranslationKey::where('key', 'auth.login')->first();
        $this->assertNotNull($authKey);
        $this->assertEquals('auth', $authKey->group);
    }

    /** @test */
    public function import_handles_json_files_under_json_group(): void
    {
        Artisan::call('localization:import');

        // JSON keys go into the _json group
        $jsonKeys = TranslationKey::where('group', '_json')->get();
        $this->assertGreaterThan(0, $jsonKeys->count(),
            'JSON language keys must be imported under the _json group');
    }

    /** @test */
    public function dry_run_does_not_write_to_database(): void
    {
        Artisan::call('localization:import', ['--dry-run' => true]);

        $this->assertEquals(0, TranslationKey::count(),
            'Dry-run must not insert any translation_keys rows');
        $this->assertEquals(0, UiTranslation::count(),
            'Dry-run must not insert any ui_translations rows');
    }

    /** @test */
    public function import_creates_empty_translation_for_turkish_no_filesystem_file(): void
    {
        // Turkish is in config but has NO lang/tr/*.php files
        Artisan::call('localization:import');

        $trLang = Language::where('code', 'tr')->first();
        $this->assertNotNull($trLang);

        $trTranslations = UiTranslation::where('language_id', $trLang->id)->count();
        $keyCount = TranslationKey::count();

        // Turkish rows should exist (one per key, with null value)
        $this->assertEquals($keyCount, $trTranslations,
            'Turkish must have ui_translation rows for every key even with no lang/tr files');

        // All Turkish values should be null (no source)
        $withValues = UiTranslation::where('language_id', $trLang->id)
            ->whereNotNull('value')
            ->count();
        $this->assertEquals(0, $withValues,
            'Turkish values must be null/empty since there are no lang/tr files');
    }
}
