<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Surah;
use App\Models\SurahTranslation;
use App\Models\TajweedRule;
use App\Models\TajweedRuleTranslation;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Models\Ayah;
use App\Models\Translation as AyahTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TranslationAuditTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure clean test file environment
        @unlink(storage_path('app/translation_coverage_report.md'));
    }

    public function test_translations_audit_command_runs_successfully(): void
    {
        $exitCode = Artisan::call('translations:audit');
        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Active locales', $output);
        $this->assertStringContainsString('COVERAGE AUDIT SUMMARY', $output);
    }

    public function test_translations_audit_saves_markdown_report(): void
    {
        $reportPath = storage_path('app/translation_coverage_report.md');
        $this->assertFileDoesNotExist($reportPath);

        $exitCode = Artisan::call('translations:audit', ['--save' => true]);
        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Audit report successfully saved to', $output);

        $this->assertFileExists($reportPath);
        $content = File::get($reportPath);
        $this->assertStringContainsString('# Translation Coverage Audit Report', $content);
        $this->assertStringContainsString('Coverage Summary Matrix', $content);
        $this->assertStringContainsString('Detailed Audit & Action Items', $content);
    }

    public function test_translations_audit_outputs_json_format(): void
    {
        $exitCode = Artisan::call('translations:audit', ['--format' => 'json']);
        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $data = json_decode($output, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('content', $data);
        $this->assertArrayHasKey('ayahs', $data);
        $this->assertArrayHasKey('ui', $data);
    }

    public function test_translations_audit_detects_missing_and_empty_values(): void
    {
        // Create an active language with no translations at all (e.g. Spanish 'es')
        $esLang = Language::create([
            'code' => 'es',
            'name' => 'Spanish',
            'native_name' => 'Español',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => false,
            'order' => 12
        ]);

        // Create a custom translation record but leave translatable fields blank
        $surah = Surah::firstOrFail();
        SurahTranslation::create([
            'surah_id' => $surah->id,
            'locale' => 'es',
            'name' => '' // Empty translation value
        ]);

        $exitCode = Artisan::call('translations:audit');
        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        
        // Assert output contains warning messages for missing and empty items
        $this->assertStringContainsString('Locale "es" has 113 missing and 1 empty translations', $output);
        $this->assertStringContainsString('Locale "es" has 10 missing and 0 empty translations', $output);
        $this->assertStringContainsString('Locale "es" has 36 missing and 0 empty translations', $output);
    }
}
