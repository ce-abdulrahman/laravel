<?php

namespace Database\Seeders;

use App\Models\Ayah;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate existing translations to start fresh
        Translation::truncate();

        if (app()->environment('testing')) {
            $this->command->info('Testing environment detected. Seeding dummy translations...');
            $translations = [];
            foreach (Ayah::all() as $ayah) {
                $translations[] = [
                    'ayah_id' => $ayah->id,
                    'language_code' => 'en',
                    'translator_name' => 'Saheeh International',
                    'content' => "Translation content for Ayah {$ayah->ayah_number}",
                    'is_default' => true,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $translations[] = [
                    'ayah_id' => $ayah->id,
                    'language_code' => 'ku',
                    'translator_name' => 'Burhan Muhammad-Amin',
                    'content' => "تەرجەمەی ئایەتی {$ayah->ayah_number}",
                    'is_default' => true,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('translations')->insert($translations);
            $this->command->info('Dummy translations seeded successfully.');
            return;
        }

        // 1. Build Ayah Lookup Map: "surah_id:ayah_number" => id
        $this->command->info('Building Ayah lookup map...');
        $ayahMap = [];
        foreach (Ayah::select(['id', 'surah_id', 'ayah_number'])->get() as $ayah) {
            $ayahMap["{$ayah->surah_id}:{$ayah->ayah_number}"] = $ayah->id;
        }

        // 2. Fetch and seed translations for each chapter
        $this->command->info('Fetching translations from api.quran.com...');
        
        $totalChapters = 114;
        $insertCount = 0;
        
        // We will collect records and insert in chunks
        $translationsToInsert = [];

        for ($chapter = 1; $chapter <= $totalChapters; $chapter++) {
            $this->command->info("Fetching chapter {$chapter}/{$totalChapters}...");
            
            // Try fetching up to 3 times in case of network glitches
            $response = null;
            for ($attempt = 1; $attempt <= 3; $attempt++) {
                try {
                    $url = "https://api.quran.com/api/v4/verses/by_chapter/{$chapter}?translations=81,20&per_page=300";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
                    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                    $res = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($httpCode === 200 && $res) {
                        $response = json_decode($res, true);
                        break;
                    }
                } catch (\Exception $e) {
                    $this->command->warn("Attempt {$attempt} failed: " . $e->getMessage());
                }
                usleep(500000); // Wait 0.5s before retry
            }

            if (!$response || !isset($response['verses'])) {
                $this->command->error("Failed to fetch translations for chapter {$chapter}");
                continue;
            }

            foreach ($response['verses'] as $verse) {
                $verseKey = $verse['verse_key']; // e.g. "1:1"
                
                if (!isset($ayahMap[$verseKey])) {
                    continue;
                }
                
                $ayahId = $ayahMap[$verseKey];

                if (isset($verse['translations'])) {
                    foreach ($verse['translations'] as $t) {
                        $langCode = ($t['resource_id'] == 81) ? 'ku' : 'en';
                        $translator = ($t['resource_id'] == 81) ? 'Burhan Muhammad-Amin' : 'Saheeh International';
                        
                        $translationsToInsert[] = [
                            'ayah_id' => $ayahId,
                            'language_code' => $langCode,
                            'translator_name' => $translator,
                            'content' => $t['text'],
                            'is_default' => true,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // Bulk insert in chunks of 500
            if (count($translationsToInsert) >= 500) {
                DB::table('translations')->insert($translationsToInsert);
                $insertCount += count($translationsToInsert);
                $translationsToInsert = [];
            }
            
            // Minor throttle to respect api.quran.com rate limits
            usleep(100000); // 100ms
        }

        // Insert remaining
        if (count($translationsToInsert) > 0) {
            DB::table('translations')->insert($translationsToInsert);
            $insertCount += count($translationsToInsert);
        }

        $this->command->info("Seeded {$insertCount} translations successfully!");
    }
}
