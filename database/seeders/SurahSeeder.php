<?php

namespace Database\Seeders;

use App\Models\Surah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
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
            $translations = [
                'ar' => ['name' => $surah['name_ar'] ?? null],
                'ku' => ['name' => $surah['name_ku'] ?? null],
                'en' => ['name' => $surah['name_en'] ?? null],
            ];

            $surahData = Arr::except($surah, ['name_ar', 'name_ku', 'name_en']);
            $surahData['is_active'] = true;

            $surahModel = Surah::updateOrCreate(
                ['number' => $surah['number']],
                $surahData
            );

            $surahModel->saveTranslationsFromArray(array_filter($translations, function ($payload) {
                return isset($payload['name']) && $payload['name'] !== null && $payload['name'] !== '';
            }));
        }

        $this->command->info('Surahs seeded successfully.');
    }
}
