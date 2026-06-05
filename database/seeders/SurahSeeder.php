<?php

namespace Database\Seeders;

use App\Models\Surah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SurahSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/surahs.json');

        if (! File::exists($path)) {
            $this->command->error('surahs.json file not found in database/data/');
            return;
        }

        $json = File::get($path);
        $surahs = json_decode($json, true);

        if (! is_array($surahs) || empty($surahs)) {
            $this->command->error('surahs.json is empty or invalid.');
            return;
        }

        foreach ($surahs as $surah) {
            Surah::updateOrCreate(
                ['number' => $surah['number']],
                array_merge($surah, ['is_active' => true])
            );
        }

        $this->command->info('Surahs seeded successfully.');
    }
}
