<?php

namespace Database\Seeders;

use App\Models\AyahTajweedSegment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TajweedSegmentSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/ayah_tajweed_segments.json');

        if (!File::exists($path)) {
            $this->command->error('ayah_tajweed_segments.json file not found in database/data/');
            return;
        }

        $json = File::get($path);
        $segments = json_decode($json, true);

        if (!is_array($segments) || empty($segments)) {
            $this->command->error('ayah_tajweed_segments.json is empty or invalid.');
            return;
        }

        DB::transaction(function () use ($segments) {
            AyahTajweedSegment::truncate();

            foreach (array_chunk($segments, 500) as $chunk) {
                AyahTajweedSegment::insert($chunk);
            }
        });

        $this->command->info('Tajweed Segments seeded successfully.');
    }
}
