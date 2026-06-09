<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Models\UiTranslationVersion;
use App\Models\User;
use App\Services\AiTranslationService;
use App\Services\TranslationImportExportService;
use App\Services\TranslationSyncService;
use App\Services\TranslationIntegrityService;
use App\Jobs\BatchTranslateJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TranslationAdvancedEngineTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected User $admin;
    protected User $member;
    protected Language $langEn;
    protected Language $langKu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->member = User::factory()->create(['role' => 'member']);
        $this->langEn = Language::where('code', 'en')->firstOrFail();
        $this->langKu = Language::where('code', 'ku')->firstOrFail();
    }

    /**
     * Test history logging is created on update and insert.
     */
    public function test_automatic_history_logging_on_translation_changes(): void
    {
        $key = TranslationKey::create(['key' => 'test.history_key', 'group' => 'test']);

        // 1. Log created version
        $translation = UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'Initial Text',
        ]);

        $this->assertDatabaseHas('ui_translation_versions', [
            'ui_translation_id' => $translation->id,
            'old_value' => null,
            'new_value' => 'Initial Text',
            'change_source' => 'manual'
        ]);

        // 2. Log updating version
        $translation->update(['value' => 'Modified Text']);

        $this->assertDatabaseHas('ui_translation_versions', [
            'ui_translation_id' => $translation->id,
            'old_value' => 'Initial Text',
            'new_value' => 'Modified Text',
        ]);
    }

    /**
     * Test rollback functionality.
     */
    public function test_rollback_to_previous_state(): void
    {
        $key = TranslationKey::create(['key' => 'test.rollback_key', 'group' => 'test']);
        
        $translation = UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'First State',
        ]);

        $firstVersion = UiTranslationVersion::where('ui_translation_id', $translation->id)->firstOrFail();

        $translation->update(['value' => 'Second State']);
        
        // Post to rollback endpoint as admin
        $response = $this->actingAs($this->admin)
            ->post(route('translations-manager.rollback', $firstVersion->id));

        $response->assertRedirect();
        $this->assertEquals('First State', $translation->fresh()->value);
        
        // Assert rollback version logged
        $this->assertDatabaseHas('ui_translation_versions', [
            'ui_translation_id' => $translation->id,
            'old_value' => 'Second State',
            'new_value' => 'First State',
            'change_source' => 'rollback'
        ]);
    }

    /**
     * Test export downloads valid files.
     */
    public function test_export_translations_to_json_and_csv(): void
    {
        $key = TranslationKey::create(['key' => 'test.export_key', 'group' => 'test']);
        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'Exportable text value',
        ]);

        // JSON Export
        $responseJson = $this->actingAs($this->admin)
            ->post(route('translations-manager.export'), [
                'locale' => 'en',
                'format' => 'json'
            ]);

        $responseJson->assertStatus(200);
        $responseJson->assertHeader('Content-Disposition');
        $this->assertStringContainsString('Exportable text value', $responseJson->getContent());

        // CSV Export
        $responseCsv = $this->actingAs($this->admin)
            ->post(route('translations-manager.export'), [
                'locale' => 'en',
                'format' => 'csv'
            ]);

        $responseCsv->assertStatus(200);
        $responseCsv->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Exportable text value', $responseCsv->getContent());
    }

    /**
     * Test JSON and CSV imports.
     */
    public function test_import_translations_saves_records(): void
    {
        $key = TranslationKey::create(['key' => 'test.existing_key', 'group' => 'test']);
        
        // 1. JSON Import
        $jsonData = json_encode([
            'test.existing_key' => 'Updated via JSON Import',
            'test.new_imported_key' => 'Created via JSON Import'
        ]);

        $tempJsonPath = tempnam(sys_get_temp_dir(), 'import_json_');
        file_put_contents($tempJsonPath, $jsonData);
        $jsonFile = new UploadedFile($tempJsonPath, 'translations.json', 'application/json', null, true);

        $response = $this->actingAs($this->admin)
            ->post(route('translations-manager.import'), [
                'locale' => 'en',
                'file' => $jsonFile,
                'create_keys' => '1'
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('ui_translations', [
            'value' => 'Updated via JSON Import',
        ]);

        $this->assertDatabaseHas('ui_translation_versions', [
            'change_source' => 'import',
            'new_value' => 'Updated via JSON Import'
        ]);

        $this->assertDatabaseHas('translation_keys', [
            'key' => 'test.new_imported_key'
        ]);

        // 2. CSV Import
        $csvData = "key,value,group\n"
                 . "test.existing_key,\"Updated via CSV Import\",test\n"
                 . "test.another_new_key,\"Created via CSV Import\",test\n";

        $tempCsvPath = tempnam(sys_get_temp_dir(), 'import_csv_');
        file_put_contents($tempCsvPath, $csvData);
        $csvFile = new UploadedFile($tempCsvPath, 'translations.csv', 'text/csv', null, true);

        $responseCsv = $this->actingAs($this->admin)
            ->post(route('translations-manager.import'), [
                'locale' => 'en',
                'file' => $csvFile,
                'create_keys' => '1'
            ]);

        $responseCsv->assertRedirect();
        
        $this->assertDatabaseHas('ui_translations', [
            'value' => 'Updated via CSV Import'
        ]);

        $this->assertDatabaseHas('translation_keys', [
            'key' => 'test.another_new_key'
        ]);
    }

    /**
     * Test AI Translation Service fallback stubs.
     */
    public function test_ai_translation_service_stubs_and_job(): void
    {
        Queue::fake();

        $key = TranslationKey::create(['key' => 'home.welcome', 'group' => 'home']);
        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'home.welcome',
        ]);

        $aiService = app(AiTranslationService::class);
        $translated = $aiService->translateKey('home.welcome', 'ku');

        $this->assertEquals('بەخێربێن بۆ بەرنامەی قورئان', $translated);

        // Dispatch bulk AI job via endpoint
        $response = $this->actingAs($this->admin)
            ->post(route('translations-manager.bulk-generate-ai'), [
                'keys' => [$key->id],
                'locale' => 'ku'
            ]);

        $response->assertStatus(200);
        Queue::assertPushed(BatchTranslateJob::class);
    }

    /**
     * Test Sync Pull and conflict resolution strategies.
     */
    public function test_sync_pull_conflict_strategies(): void
    {
        $key = TranslationKey::create(['key' => 'sync.test_key', 'group' => 'sync']);
        $localTrans = UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'Local Text State',
        ]);

        // Mock Remote GET pulling payload
        $remotePayload = [
            'translations' => [
                [
                    'key' => 'sync.test_key',
                    'locale' => 'en',
                    'value' => 'Remote New Text State',
                    'updated_at' => now()->addHour()->toDateTimeString()
                ]
            ]
        ];

        Http::fake([
            'https://remote.test/api/translations/sync' => Http::response($remotePayload, 200)
        ]);

        $syncService = app(TranslationSyncService::class);

        // Strategy: local_wins
        $syncService->syncPull('https://remote.test/api/translations/sync', 'local_wins');
        $this->assertEquals('Local Text State', $localTrans->fresh()->value);

        // Strategy: remote_wins
        $syncService->syncPull('https://remote.test/api/translations/sync', 'remote_wins');
        $this->assertEquals('Remote New Text State', $localTrans->fresh()->value);
    }

    /**
     * Test Sync Push.
     */
    public function test_sync_push_transmits_payload(): void
    {
        $key = TranslationKey::create(['key' => 'sync.push_key', 'group' => 'sync']);
        UiTranslation::create([
            'translation_key_id' => $key->id,
            'language_id' => $this->langEn->id,
            'value' => 'Push value',
        ]);

        Http::fake([
            'https://remote.test/api/translations/sync' => Http::response(['success' => true], 200)
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('translations-manager.sync-push'), [
                'remote_url' => 'https://remote.test/api/translations/sync'
            ]);

        $response->assertRedirect();
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Translation-Sync-Token') && 
                   count($request['translations']) > 0;
        });
    }

    /**
     * Test translation integrity scans.
     */
    public function test_integrity_audit_runs_successfully(): void
    {
        // Setup missing translation key
        TranslationKey::create(['key' => 'audit.missing_key', 'group' => 'audit']);

        $integrityService = app(TranslationIntegrityService::class);
        $report = $integrityService->runFullAudit();

        $this->assertArrayHasKey('coverage', $report);
        $this->assertArrayHasKey('missing', $report);
        $this->assertArrayHasKey('orphans', $report);
        $this->assertArrayHasKey('missing_from_db', $report);
    }

    /**
     * Test bulk editing endpoints.
     */
    public function test_bulk_editing_and_batch_deletes(): void
    {
        $key1 = TranslationKey::create(['key' => 'bulk.test1', 'group' => 'bulk']);
        $key2 = TranslationKey::create(['key' => 'bulk.test2', 'group' => 'bulk']);

        // Update bulk
        $response = $this->actingAs($this->admin)
            ->post(route('translations-manager.bulk-update'), [
                'translations' => [
                    $key1->id => [
                        $this->langEn->id => 'Bulk Value 1'
                    ],
                    $key2->id => [
                        $this->langEn->id => 'Bulk Value 2'
                    ]
                ]
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ui_translations', ['value' => 'Bulk Value 1']);

        // Delete bulk
        $responseDelete = $this->actingAs($this->admin)
            ->post(route('translations-manager.bulk-delete'), [
                'keys' => [$key1->id, $key2->id]
            ]);

        $responseDelete->assertStatus(200);
        $this->assertDatabaseMissing('translation_keys', ['id' => $key1->id]);
    }
}
