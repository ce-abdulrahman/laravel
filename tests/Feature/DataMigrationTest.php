<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Surah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DataMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed required languages
        Language::updateOrCreate(['code' => 'en'], ['name' => 'English', 'direction' => 'ltr', 'typography_class' => 'font-sans', 'text_align' => 'left', 'align_class' => 'text-left', 'is_default' => true, 'is_active' => true]);
        Language::updateOrCreate(['code' => 'ku'], ['name' => 'Kurdish', 'direction' => 'rtl', 'typography_class' => 'font-kurdish', 'text_align' => 'right', 'align_class' => 'text-right', 'is_default' => false, 'is_active' => true]);
        Language::updateOrCreate(['code' => 'ar'], ['name' => 'Arabic', 'direction' => 'rtl', 'typography_class' => 'font-arabic', 'text_align' => 'right', 'align_class' => 'text-right', 'is_default' => false, 'is_active' => true]);

        // Clear tables to prevent UNIQUE constraint violations with pre-seeded data
        DB::table('surahs')->delete();
        DB::table('surah_translations')->delete();
    }

    /** @test */
    public function migration_transfers_surah_names_accurately_and_reverts_symmetrically()
    {
        $migration = require database_path('migrations/2026_06_06_300000_drop_old_multilingual_columns.php');

        // 1. Setup: down() restores columns
        $migration->down();

        // Verify that the columns were re-added to surahs
        $this->assertTrue(Schema::hasColumn('surahs', 'name_ar'));
        $this->assertTrue(Schema::hasColumn('surahs', 'name_ku'));
        $this->assertTrue(Schema::hasColumn('surahs', 'name_en'));

        // Clear translations to start clean
        DB::table('surah_translations')->truncate();

        // 2. Populate legacy mock data
        $surahId1 = DB::table('surahs')->insertGetId([
            'number' => 1,
            'name_ar' => 'سُورَةُ ٱلْفَاتِحَةِ',
            'name_ku' => 'کرانەوە / دەستپێک',
            'name_en' => 'Al-Fatihah',
            'revelation_type' => 'Meccan',
            'ayah_count' => 7,
            'page_start' => 1,
            'page_end' => 1,
            'juz_start' => 1,
            'juz_end' => 1,
            'is_active' => true,
        ]);

        // Add a second record with some null fields to test partial translations
        $surahId2 = DB::table('surahs')->insertGetId([
            'number' => 2,
            'name_ar' => 'سُورَةُ البَقَرَةِ',
            'name_ku' => null,
            'name_en' => 'Al-Baqarah',
            'revelation_type' => 'Medinan',
            'ayah_count' => 286,
            'page_start' => 2,
            'page_end' => 49,
            'juz_start' => 1,
            'juz_end' => 3,
            'is_active' => true,
        ]);

        // 3. Run migration: up() moves data and drops columns
        $migration->up();

        // Verify legacy columns were dropped
        $this->assertFalse(Schema::hasColumn('surahs', 'name_ar'));
        $this->assertFalse(Schema::hasColumn('surahs', 'name_ku'));
        $this->assertFalse(Schema::hasColumn('surahs', 'name_en'));

        // Verify translation entries exist in surah_translations
        $this->assertDatabaseHas('surah_translations', [
            'surah_id' => $surahId1,
            'locale' => 'ar',
            'name' => 'سُورَةُ ٱلْفَاتِحَةِ',
        ]);
        $this->assertDatabaseHas('surah_translations', [
            'surah_id' => $surahId1,
            'locale' => 'ku',
            'name' => 'کرانەوە / دەستپێک',
        ]);
        $this->assertDatabaseHas('surah_translations', [
            'surah_id' => $surahId1,
            'locale' => 'en',
            'name' => 'Al-Fatihah',
        ]);

        // Second surah should have ar and en but no ku
        $this->assertDatabaseHas('surah_translations', [
            'surah_id' => $surahId2,
            'locale' => 'ar',
            'name' => 'سُورَةُ البَقَرَةِ',
        ]);
        $this->assertDatabaseMissing('surah_translations', [
            'surah_id' => $surahId2,
            'locale' => 'ku',
        ]);
        $this->assertDatabaseHas('surah_translations', [
            'surah_id' => $surahId2,
            'locale' => 'en',
            'name' => 'Al-Baqarah',
        ]);

        // Check if model resolves properly through HasTranslations
        $surahModel = Surah::find($surahId1);
        $this->assertEquals('Al-Fatihah', $surahModel->getTranslation('name', 'en'));
        $this->assertEquals('سُورَةُ ٱلْفَاتِحَةِ', $surahModel->getTranslation('name', 'ar'));

        // 4. Test symmetric Down (rollback): restores columns and repopulates them
        $migration->down();

        $this->assertTrue(Schema::hasColumn('surahs', 'name_ar'));
        $this->assertDatabaseHas('surahs', [
            'id' => $surahId1,
            'name_ar' => 'سُورَةُ ٱلْفَاتِحَةِ',
            'name_ku' => 'کرانەوە / دەستپێک',
            'name_en' => 'Al-Fatihah',
        ]);

        $this->assertDatabaseHas('surahs', [
            'id' => $surahId2,
            'name_ar' => 'سُورَةُ البَقَرَةِ',
            'name_ku' => null,
            'name_en' => 'Al-Baqarah',
        ]);
    }

    /** @test */
    public function migration_fails_when_integrity_check_fails()
    {
        $migration = require database_path('migrations/2026_06_06_300000_drop_old_multilingual_columns.php');

        // Setup legacy columns
        $migration->down();

        // Populate a surah
        $surahId = DB::table('surahs')->insertGetId([
            'number' => 10,
            'name_ar' => 'سُورَةُ يُونُسَ',
            'name_ku' => 'يونس',
            'name_en' => 'Yunus',
            'revelation_type' => 'Meccan',
            'ayah_count' => 109,
            'is_active' => true,
        ]);

        // Register listener to mutate the database translation during integrity verification phase
        DB::listen(function ($query) {
            if (str_contains($query->sql, 'select') && str_contains($query->sql, 'surah_translations')) {
                // Mutate record on select, triggering a checksum mismatch!
                DB::table('surah_translations')->update(['name' => 'mismatch']);
            }
        });

        // Expect Exception because integrity checksum verification will mismatch
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('integrity check failed');

        try {
            $migration->up();
        } finally {
            // Assert that the migration rolled back and preserved legacy columns due to transaction safety
            $this->assertTrue(Schema::hasColumn('surahs', 'name_ar'));
        }
    }

    /** @test */
    public function migration_aborts_when_required_languages_are_missing()
    {
        $migration = require database_path('migrations/2026_06_06_300000_drop_old_multilingual_columns.php');

        // Re-add legacy columns
        $migration->down();

        // Populate a surah so we have data to migrate, triggering language validation
        DB::table('surahs')->insertGetId([
            'number' => 20,
            'name_ar' => 'طه',
            'name_ku' => 'طه',
            'name_en' => 'Ta-Ha',
            'revelation_type' => 'Meccan',
            'ayah_count' => 135,
            'is_active' => true,
        ]);

        // Delete required language
        Language::where('code', 'ku')->delete();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required language records not found');

        $migration->up();
    }
}
