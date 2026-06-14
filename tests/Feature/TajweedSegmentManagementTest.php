<?php

namespace Tests\Feature;

use App\Models\Ayah;
use App\Models\AyahTajweedSegment;
use App\Models\Language;
use App\Models\Surah;
use App\Models\TajweedRule;
use App\Models\TajweedRuleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TajweedSegmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup an admin user for CRUD authentication
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Test database columns exist.
     */
    public function test_database_table_has_correct_renamed_and_new_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('ayah_tajweed_segments', 'matched_text'));
        $this->assertTrue(Schema::hasColumn('ayah_tajweed_segments', 'metadata'));
        $this->assertFalse(Schema::hasColumn('ayah_tajweed_segments', 'text_segment')); // Renamed
    }

    /**
     * Test model backward compatibility mapping text_segment to matched_text.
     */
    public function test_model_maps_text_segment_for_backward_compatibility(): void
    {
        $surah = Surah::firstOrFail();
        $ayah = Ayah::firstOrFail();
        $rule = TajweedRule::firstOrFail();

        // Save using text_segment
        $segment = new AyahTajweedSegment([
            'surah_id' => $surah->id,
            'ayah_id' => $ayah->id,
            'tajweed_rule_id' => $rule->id,
            'text_segment' => 'تختبر', // Will set matched_text
            'start_index' => 2,
            'end_index' => 5,
        ]);
        $segment->save();

        $this->assertEquals('تختبر', $segment->matched_text);
        $this->assertEquals('تختبر', $segment->text_segment);

        // Fetch fresh copy
        $fresh = AyahTajweedSegment::findOrFail($segment->id);
        $this->assertEquals('تختبر', $fresh->matched_text);
        $this->assertEquals('تختبر', $fresh->text_segment);

        // Update using text_segment
        $fresh->update(['text_segment' => 'معدل']);
        $this->assertEquals('معدل', $fresh->fresh()->matched_text);
    }

    /**
     * Test model casts metadata correctly.
     */
    public function test_model_casts_metadata_to_array(): void
    {
        $surah = Surah::firstOrFail();
        $ayah = Ayah::firstOrFail();
        $rule = TajweedRule::firstOrFail();

        $segment = AyahTajweedSegment::create([
            'surah_id' => $surah->id,
            'ayah_id' => $ayah->id,
            'tajweed_rule_id' => $rule->id,
            'matched_text' => 'تختبر',
            'start_index' => 2,
            'end_index' => 5,
            'metadata' => ['confidence' => 99, 'version' => '1.0'],
        ]);

        $this->assertIsArray($segment->metadata);
        $this->assertEquals(99, $segment->metadata['confidence']);
        $this->assertEquals('1.0', $segment->metadata['version']);
    }

    /**
     * Test index page filters and search.
     */
    public function test_index_filters_and_search_operate_correctly(): void
    {
        $surah = Surah::firstOrFail();
        $ayah = Ayah::firstOrFail();
        $rule = TajweedRule::firstOrFail();

        // Create specific segment
        AyahTajweedSegment::create([
            'surah_id' => $surah->id,
            'ayah_id' => $ayah->id,
            'tajweed_rule_id' => $rule->id,
            'matched_text' => 'ناراً حامية',
            'start_index' => 0,
            'end_index' => 10,
        ]);

        $this->actingAs($this->adminUser);

        // Test search query
        $response = $this->get(route('tajweed-segments.index', ['search' => 'ناراً']));
        $response->assertStatus(200);
        $response->assertSee('ناراً حامية');

        // Test filter by surah
        $responseSurah = $this->get(route('tajweed-segments.index', ['surah_id' => $surah->id]));
        $responseSurah->assertStatus(200);
        $responseSurah->assertSee('ناراً حامية');
    }

    /**
     * Test Form Request validation.
     */
    public function test_segment_creation_requires_valid_data(): void
    {
        $this->actingAs($this->adminUser);

        // Validation fail on empty inputs
        $response = $this->post(route('tajweed-segments.store'), []);
        $response->assertSessionHasErrors(['ayah_id', 'tajweed_rule_id', 'matched_text']);

        // Validation fail on invalid JSON metadata
        $responseMeta = $this->post(route('tajweed-segments.store'), [
            'ayah_id' => Ayah::firstOrFail()->id,
            'tajweed_rule_id' => TajweedRule::firstOrFail()->id,
            'matched_text' => 'مثال',
            'metadata' => '{invalid-json}',
        ]);
        $responseMeta->assertSessionHasErrors(['metadata']);
    }

    /**
     * Test Import action from JSON dataset.
     */
    public function test_import_segments_from_json_supports_deduplication(): void
    {
        $ayah = Ayah::firstOrFail();
        $rule = TajweedRule::firstOrFail();

        $importJson = json_encode([
            [
                'ayah_id' => $ayah->id,
                'tajweed_rule_id' => $rule->id,
                'matched_text' => 'نْعَ',
                'start_index' => 20,
                'end_index' => 24,
                'metadata' => ['confidence' => 100]
            ],
            [
                'ayah_id' => $ayah->id,
                'tajweed_rule_id' => $rule->id,
                'matched_text' => 'نْعَ',
                'start_index' => 20,
                'end_index' => 24, // Duplicate row
                'metadata' => ['confidence' => 100]
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('segments.json', $importJson);

        $this->actingAs($this->adminUser);
        
        $response = $this->post(route('tajweed-segments.import'), [
            'file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check deduplication (only 1 row must be imported)
        $count = AyahTajweedSegment::where('ayah_id', $ayah->id)
            ->where('tajweed_rule_id', $rule->id)
            ->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Test Rebuild action safely clears and imports.
     */
    public function test_rebuild_action_truncates_and_seeds_fresh(): void
    {
        $ayah = Ayah::firstOrFail();
        $rule = TajweedRule::firstOrFail();

        // Seed some initial segments
        AyahTajweedSegment::create([
            'surah_id' => $ayah->surah_id,
            'ayah_id' => $ayah->id,
            'tajweed_rule_id' => $rule->id,
            'matched_text' => 'ماتت سابقتها',
            'start_index' => 0,
            'end_index' => 10,
        ]);

        $rebuildJson = json_encode([
            [
                'ayah_id' => $ayah->id,
                'tajweed_rule_id' => $rule->id,
                'matched_text' => 'جديدة تماماً',
                'start_index' => 5,
                'end_index' => 12,
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('fresh_segments.json', $rebuildJson);

        $this->actingAs($this->adminUser);

        $response = $this->post(route('tajweed-segments.rebuild'), [
            'file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check initial segment is deleted
        $this->assertDatabaseMissing('ayah_tajweed_segments', [
            'matched_text' => 'ماتت سابقتها'
        ]);

        // Check fresh segment is present
        $this->assertDatabaseHas('ayah_tajweed_segments', [
            'matched_text' => 'جديدة تماماً',
            'start_index' => 5,
            'end_index' => 12
        ]);
    }

    /**
     * Test Export returns correct file format.
     */
    public function test_export_endpoints_generate_downloadable_content(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('tajweed-segments.export', ['format' => 'json']));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
        
        $responseCsv = $this->get(route('tajweed-segments.export', ['format' => 'csv']));
        $responseCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $responseCsv->headers->get('Content-Type'));
    }
}
