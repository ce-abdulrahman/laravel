<?php

namespace Database\Seeders;

use App\Models\AudioFile;
use App\Models\Reciter;
use App\Models\Surah;
use Illuminate\Database\Seeder;

class AudioFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Mishary Rashid Alafasy
        $alafasy = Reciter::where('name', 'Mishary Rashid Alafasy')->first();
        // 2. Maher Al-Muaiqly
        $maher = Reciter::where('name', 'Maher Al-Muaiqly')->first();
        // 3. Abdul Basit Abdus Samad
        $abdulbasit = Reciter::where('name', 'Abdul Basit Abdus Samad')->first();

        $reciterConfigs = [];

        if ($alafasy) {
            $reciterConfigs[] = [
                'reciter_id' => $alafasy->id,
                'base_url' => 'https://download.quranicaudio.com/quran/mishaari_raashid_al_3afaasee/',
                'quality' => '128',
            ];
        }

        if ($maher) {
            $reciterConfigs[] = [
                'reciter_id' => $maher->id,
                'base_url' => 'https://download.quranicaudio.com/quran/maher_256/',
                'quality' => '256',
            ];
        }

        if ($abdulbasit) {
            $reciterConfigs[] = [
                'reciter_id' => $abdulbasit->id,
                'base_url' => 'https://download.quranicaudio.com/quran/abdul_basit_murattal/',
                'quality' => '128',
            ];
        }

        $surahs = Surah::orderBy('number')->get();

        foreach ($reciterConfigs as $config) {
            foreach ($surahs as $surah) {
                $fileName = sprintf('%03d.mp3', $surah->number);
                $filePath = $config['base_url'] . $fileName;

                // Estimate duration as approx 6 seconds per ayah
                $duration = $surah->ayah_count * 6;

                AudioFile::updateOrCreate(
                    [
                        'reciter_id' => $config['reciter_id'],
                        'surah_id' => $surah->id,
                        'ayah_id' => null,
                    ],
                    [
                        'file_path' => $filePath,
                        'duration_seconds' => $duration,
                        'quality' => $config['quality'],
                        'source_type' => 'url',
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
