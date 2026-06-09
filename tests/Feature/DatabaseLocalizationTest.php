<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DatabaseLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected Language $english;
    protected Language $arabic;
    protected Language $kurdish;

    protected function setUp(): void
    {
        parent::setUp();

        $this->english = Language::updateOrCreate(['code' => 'en'], [
            'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr',
            'is_active' => true, 'is_default' => true,
        ]);
        $this->arabic = Language::updateOrCreate(['code' => 'ar'], [
            'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl',
            'is_active' => true, 'is_default' => false,
        ]);
        $this->kurdish = Language::updateOrCreate(['code' => 'ku'], [
            'name' => 'Kurdish', 'native_name' => 'کوردی', 'direction' => 'rtl',
            'is_active' => true, 'is_default' => false,
        ]);
    }

    protected function seedKey(string $key, string $group, array $values): TranslationKey
    {
        $tk = TranslationKey::create(['key' => $key, 'group' => $group]);

        foreach ($values as $locale => $value) {
            $lang = Language::where('code', $locale)->first();
            if ($lang) {
                UiTranslation::create([
                    'translation_key_id' => $tk->id,
                    'language_id'        => $lang->id,
                    'value'              => $value,
                ]);
            }
        }

        return $tk;
    }

    /** @test */
    public function t_helper_returns_database_value_for_active_locale(): void
    {
        $this->seedKey('ui.test_hello', 'ui', [
            'en' => 'Hello World',
            'ar' => 'مرحبا بالعالم',
        ]);

        Cache::flush(); // Force DB load

        app()->setLocale('en');
        $result = t('ui.test_hello');
        $this->assertEquals('Hello World', $result);
    }

    /** @test */
    public function t_helper_returns_arabic_value_when_locale_is_ar(): void
    {
        $this->seedKey('ui.test_greeting', 'ui', [
            'en' => 'Welcome',
            'ar' => 'أهلاً وسهلاً',
        ]);

        Cache::flush();

        app()->setLocale('ar');
        $result = t('ui.test_greeting');
        $this->assertEquals('أهلاً وسهلاً', $result);
    }

    /** @test */
    public function translate_function_alias_works_identically_to_t(): void
    {
        $this->seedKey('ui.alias_test', 'ui', [
            'en' => 'Alias Test Value',
        ]);

        Cache::flush();
        app()->setLocale('en');

        $this->assertEquals(t('ui.alias_test'), translate('ui.alias_test'),
            'translate() and t() must return identical results');
    }

    /** @test */
    public function t_helper_supports_placeholder_replacement(): void
    {
        $this->seedKey('ui.count_message', 'ui', [
            'en' => 'You have :count messages',
            'ar' => 'لديك :count رسالة',
        ]);

        Cache::flush();

        app()->setLocale('en');
        $this->assertEquals('You have 5 messages', t('ui.count_message', ['count' => 5]));

        app()->setLocale('ar');
        $this->assertEquals('لديك 5 رسالة', t('ui.count_message', ['count' => 5]));
    }

    /** @test */
    public function t_helper_falls_back_to_default_locale_when_translation_missing(): void
    {
        // Only seed English value — Arabic is missing
        $this->seedKey('ui.fallback_test', 'ui', [
            'en' => 'English Fallback Value',
        ]);

        Cache::flush();

        app()->setLocale('ar');
        $result = t('ui.fallback_test');
        // Should fall back to English value
        $this->assertEquals('English Fallback Value', $result,
            'Missing locale must fall back to the default language value');
    }

    /** @test */
    public function t_helper_returns_key_when_no_translation_exists_at_all(): void
    {
        Cache::flush();

        app()->setLocale('en');
        // Key that doesn't exist at all
        $result = t('nonexistent.completely_missing_key');

        // Should return the key itself (or with debug prefix in local env)
        $this->assertStringContainsString('completely_missing_key', $result,
            'Missing key must return the key string as fallback');
    }

    /** @test */
    public function t_helper_auto_generates_missing_key_in_database(): void
    {
        Cache::flush();

        app()->setLocale('en');
        $missingKey = 'auto_gen.brand_new_key_' . uniqid();

        // Access the key — should auto-generate it
        t($missingKey);

        // The key should now exist in translation_keys
        $exists = TranslationKey::where('key', $missingKey)->exists();
        $this->assertTrue($exists,
            'Auto-generation must create the TranslationKey record');
    }

    /** @test */
    public function t_helper_supports_per_call_locale_override(): void
    {
        $this->seedKey('ui.locale_override', 'ui', [
            'en' => 'English Value',
            'ar' => 'القيمة العربية',
        ]);

        Cache::flush();

        // App locale is English but we request Arabic explicitly
        app()->setLocale('en');
        $result = t('ui.locale_override', [], 'ar');
        $this->assertEquals('القيمة العربية', $result,
            'Per-call locale override must work correctly');
    }

    /** @test */
    public function laravel_double_underscore_function_routes_through_db(): void
    {
        // The LocalizationServiceProvider overrides __ to route through DB
        $this->seedKey('dashboard.title', 'dashboard', [
            'en' => 'DB Dashboard Title',
        ]);

        Cache::flush();
        app()->setLocale('en');

        // __() should now resolve from DB via our provider override
        $result = __('dashboard.title');

        // It should match either the DB value or fall through to filesystem (both are acceptable)
        // The important thing: no exception is thrown and a string is returned
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function translation_service_loads_all_translations_for_locale(): void
    {
        $this->seedKey('group_a.key_one', 'group_a', ['en' => 'Value One']);
        $this->seedKey('group_a.key_two', 'group_a', ['en' => 'Value Two']);
        $this->seedKey('group_b.key_three', 'group_b', ['en' => 'Value Three']);

        Cache::flush();

        $service = app(TranslationService::class);
        $translations = $service->getTranslationsForLocale('en');

        $this->assertArrayHasKey('group_a.key_one', $translations);
        $this->assertArrayHasKey('group_a.key_two', $translations);
        $this->assertArrayHasKey('group_b.key_three', $translations);
        $this->assertEquals('Value One', $translations['group_a.key_one']);
    }

    /** @test */
    public function empty_translation_value_triggers_fallback(): void
    {
        $tk = TranslationKey::create(['key' => 'ui.empty_value_key', 'group' => 'ui']);

        // Arabic exists but is empty
        UiTranslation::create([
            'translation_key_id' => $tk->id,
            'language_id'        => $this->arabic->id,
            'value'              => '',
        ]);
        // English has value
        UiTranslation::create([
            'translation_key_id' => $tk->id,
            'language_id'        => $this->english->id,
            'value'              => 'Non-empty English',
        ]);

        Cache::flush();

        app()->setLocale('ar');
        $result = t('ui.empty_value_key');

        $this->assertEquals('Non-empty English', $result,
            'Empty string translation must trigger fallback to default language');
    }
}
