<?php

namespace Database\Seeders;

use App\Models\AyahTajweedSegment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TajweedRuleOfMaddSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/3_madd_tajweed_results.json');

        if (!File::exists($path)) {
            $this->command->error('3_madd_tajweed_results.json file not found in database/data/');
            return;
        }

        $json = File::get($path);
        $segments = json_decode($json, true);

        if (!is_array($segments) || empty($segments)) {
            $this->command->error('3_madd_tajweed_results.json is empty or invalid.');
            return;
        }

        $ayahs = DB::table('ayahs')->select('id', 'surah_id', 'ayah_number')->get();
        $ayahMap = [];
        foreach ($ayahs as $ayah) {
            $ayahMap[$ayah->surah_id][$ayah->ayah_number] = $ayah->id;
        }

        DB::transaction(function () use ($segments, $ayahMap) {
            foreach (array_chunk($segments, 500) as $chunk) {
                $mappedChunk = array_map(function ($item) use ($ayahMap) {
                    $surahId = $item['surah_id'];
                    $ayahNumber = $item['ayah_id']; // This is actually the ayah_number in the json!
                    $realAyahId = $ayahMap[$surahId][$ayahNumber] ?? null;

                    return [
                        'surah_id' => $surahId,
                        'ayah_id' => $realAyahId,
                        'tajweed_rule_id' => $item['tajweed_rule_id'],
                        'matched_text' => $item['text_segment'] ?? $item['matched_text'] ?? '',
                        'start_index' => $item['start_index'] ?? null,
                        'end_index' => $item['end_index'] ?? null,
                        'waqf_assumed' => $item['waqf_assumed'] ?? false,
                        'metadata' => isset($item['metadata'])
                            ? (is_array($item['metadata']) ? json_encode($item['metadata']) : $item['metadata'])
                            : json_encode([]),
                        'note' => $item['note'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $chunk);
                AyahTajweedSegment::insert($mappedChunk);
            }
        });

        $this->command->info('Tajweed rule of Madd relations seeded successfully.');
    }
}
