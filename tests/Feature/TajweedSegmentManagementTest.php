<?php

namespace Tests\Feature;

use App\Models\Ayah;
use App\Models\AyahTajweedSegment;
use App\Models\Surah;
use App\Models\TajweedRule;
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
    protected Surah $surah;
    protected Ayah $ayah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['role' => 'admin']);

        // Create a minimal Surah + Ayah directly — no AyahSeeder needed.
        $this->surah = Surah::firstOrCreate(
            ['number' => 1],
            ['revelation_type' => 'Meccan', 'ayah_count' => 7, 'is_active' => true]
        );

        $this->ayah = Ayah::firstOrCreate(
            ['surah_id' => $this->surah->id, 'ayah_number' => 1],
            [
                'text_uthmani' => 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ',
                'page_number'  => 1,
                'juz_number'   => 1,
                'hizb_number'  => 1,
                'sajda_flag'   => false,
                'is_active'    => true,
            ]
        );
    }

    /** Test database columns exist. */
    public function test_database_table_has_correct_renamed_and_new_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('ayah_tajweed_segments', 'matched_text'));
        $this->assertTrue(Schema::hasColumn('ayah_tajweed_segments', 'metadata'));
        $this->assertFalse(Schema::hasColumn('ayah_tajweed_segments', 'text_segment'));
    }

    /** Test model backward compatibility: text_segment → matched_text. */
    public function test_model_maps_text_segment_for_backward_compatibility(): void
    {
        $rule = TajweedRule::firstOrFail();

        $segment = new AyahTajweedSegment([
            'surah_id'        => $this->surah->id,
            'ayah_id'         => $this->ayah->id,
            'tajweed_rule_id' => $rule->id,
            'text_segment'    => 'تختبر',
            'start_index'     => 2,
            'end_index'       => 5,
        ]);
        $segment->save();

        $this->assertEquals('تختبر', $segment->matched_text);
        $this->assertEquals('تختبر', $segment->text_segment);

        $fresh = AyahTajweedSegment::findOrFail($segment->id);
        $this->assertEquals('تختبر', $fresh->matched_text);

        $fresh->update(['text_segment' => 'معدل']);
        $this->assertEquals('معدل', $fresh->fresh()->matched_text);
    }

    /** Test model casts metadata to array. */
    public function test_model_casts_metadata_to_array(): void
    {
        $rule    = TajweedRule::firstOrFail();
        $segment = AyahTajweedSegment::create([
            'surah_id'        => $this->surah->id,
            'ayah_id'         => $this->ayah->id,
            'tajweed_rule_id' => $rule->id,
            'matched_text'    => 'تختبر',
            'start_index'     => 2,
            'end_index'       => 5,
            'metadata'        => ['confidence' => 99, 'version' => '1.0'],
        ]);

        $this->assertIsArray($segment->metadata);
        $this->assertEquals(99, $segment->metadata['confidence']);
        $this->assertEquals('1.0', $segment->metadata['version']);
    }

    /** Test index page filters and search. */
    public function test_index_filters_and_search_operate_correctly(): void
    {
        $rule = TajweedRule::firstOrFail();

        AyahTajweedSegment::create([
            'surah_id'        => $this->surah->id,
            'ayah_id'         => $this->ayah->id,
            'tajweed_rule_id' => $rule->id,
            'matched_text'    => 'ناراً حامية',
            'start_index'     => 0,
            'end_index'       => 10,
        ]);

        $this->actingAs($this->adminUser);

        $this->get(route('tajweed-segments.index', ['search' => 'ناراً']))
             ->assertStatus(200)
             ->assertSee('ناراً حامية');

        $this->get(route('tajweed-segments.index', ['surah_id' => $this->surah->id]))
             ->assertStatus(200)
             ->assertSee('ناراً حامية');
    }

    /** Test Form Request validation. */
    public function test_segment_creation_requires_valid_data(): void
    {
        $this->actingAs($this->adminUser);

        $this->post(route('tajweed-segments.store'), [])
             ->assertSessionHasErrors(['ayah_id', 'tajweed_rule_id', 'matched_text']);

        $this->post(route('tajweed-segments.store'), [
            'ayah_id'         => $this->ayah->id,
            'tajweed_rule_id' => TajweedRule::firstOrFail()->id,
            'matched_text'    => 'مثال',
            'metadata'        => '{invalid-json}',
        ])->assertSessionHasErrors(['metadata']);
    }

    /** Test multi-file import (files[]) with deduplication. */
    public function test_import_segments_from_json_supports_deduplication(): void
    {
        $rule = TajweedRule::firstOrFail();

        $importJson = json_encode([
            [
                'ayah_id'         => $this->ayah->id,
                'tajweed_rule_id' => $rule->id,
                'matched_text'    => 'نْعَ',
                'start_index'     => 20,
                'end_index'       => 24,
                'metadata'        => ['confidence' => 100],
            ],
            [
                'ayah_id'         => $this->ayah->id,
                'tajweed_rule_id' => $rule->id,
                'matched_text'    => 'نْعَ',
                'start_index'     => 20,
                'end_index'       => 24, // duplicate — must be skipped
                'metadata'        => ['confidence' => 100],
            ],
        ]);

        // Multi-file upload: files[]
        $file = UploadedFile::fake()->createWithContent('segments.json', $importJson);

        $this->actingAs($this->adminUser)
             ->post(route('tajweed-segments.import'), ['files' => [$file]])
             ->assertRedirect()
             ->assertSessionHas('success');

        $this->assertEquals(
            1,
            AyahTajweedSegment::where('ayah_id', $this->ayah->id)
                ->where('tajweed_rule_id', $rule->id)
                ->count()
        );
    }

    /** Test Rebuild clears existing and loads fresh dataset. */
    public function test_rebuild_action_truncates_and_seeds_fresh(): void
    {
        $rule = TajweedRule::firstOrFail();

        AyahTajweedSegment::create([
            'surah_id'        => $this->ayah->surah_id,
            'ayah_id'         => $this->ayah->id,
            'tajweed_rule_id' => $rule->id,
            'matched_text'    => 'ماتت سابقتها',
            'start_index'     => 0,
            'end_index'       => 10,
        ]);

        $rebuildJson = json_encode([[
            'ayah_id'         => $this->ayah->id,
            'tajweed_rule_id' => $rule->id,
            'matched_text'    => 'جديدة تماماً',
            'start_index'     => 5,
            'end_index'       => 12,
        ]]);

        $file = UploadedFile::fake()->createWithContent('fresh_segments.json', $rebuildJson);

        $this->actingAs($this->adminUser)
             ->post(route('tajweed-segments.rebuild'), ['file' => $file])
             ->assertRedirect()
             ->assertSessionHas('success');

        $this->assertDatabaseMissing('ayah_tajweed_segments', ['matched_text' => 'ماتت سابقتها']);
        $this->assertDatabaseHas('ayah_tajweed_segments', ['matched_text' => 'جديدة تماماً', 'start_index' => 5]);
    }

    /** Test Export returns correct file formats. */
    public function test_export_endpoints_generate_downloadable_content(): void
    {
        $this->actingAs($this->adminUser);

        $this->get(route('tajweed-segments.export', ['format' => 'json']))
             ->assertStatus(200)
             ->assertHeader('Content-Type', 'application/json');

        $this->get(route('tajweed-segments.export', ['format' => 'csv']))
             ->assertStatus(200)
             ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
