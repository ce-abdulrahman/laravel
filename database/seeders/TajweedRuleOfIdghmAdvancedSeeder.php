<?php

namespace Database\Seeders;

use App\Models\AyahTajweedSegment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TajweedRuleOfIdghmAdvancedSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/6_Idghm_Advanced.json');

        if (!File::exists($path)) {
            $this->command->error('6_Idghm_Advanced.json file not found in database/data/');
            return;
        }

        $json = File::get($path);
        $segments = json_decode($json, true);

        if (!is_array($segments) || empty($segments)) {
            $this->command->error('6_Idghm_Advanced.json is empty or invalid.');
            return;
        }

        DB::transaction(function () use ($segments) {
            foreach (array_chunk($segments, 500) as $chunk) {
                $mappedChunk = array_map(function ($item) {
                    if (isset($item['text_segment'])) {
                        $item['matched_text'] = $item['text_segment'];
                        unset($item['text_segment']);
                    }
                    if (!isset($item['metadata'])) {
                        $item['metadata'] = json_encode([]);
                    }
                    $item['created_at'] = now();
                    $item['updated_at'] = now();
                    return $item;
                }, $chunk);
                AyahTajweedSegment::insert($mappedChunk);
            }
        });

        $this->command->info('Tajweed rule of Idghm Advanced relations seeded successfully.');
    }
}
