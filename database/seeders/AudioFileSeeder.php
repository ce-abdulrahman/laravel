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
        $alafasy = Reciter::where('slug', 'mishary-alafasy')->first();
        // 2. Maher Al-Muaiqly
        $maher = Reciter::where('slug', 'maher-almuaiqly')->first();
        // 3. Abdul Basit Abdus Samad
        $abdulbasit = Reciter::where('slug', 'abdul-basit')->first();
        // 4. Saud Shuraim
        $shuraim = Reciter::where('slug', 'saud-shuraim')->first();
        // 5. Yasser Al Dosari
        $dosari = Reciter::where('slug', 'yasser-dosari')->first();
        // 6. Peshawa Qader Al-Kurdi
        $peshawa = Reciter::where('slug', 'peshawa-kurdi')->first();
        // 7. Raad Mohammad Al-Kurdi
        $raad = Reciter::where('slug', 'raad-kurdi')->first();

        $reciterConfigs = [];

        if ($alafasy) {
            $reciterConfigs[] = [
                'reciter_id' => $alafasy->id,
                'base_url' => 'https://download.quranicaudio.com/quran/mishari_alafasy/',
                'quality' => '128',
            ];
        }

        if ($maher) {
            $reciterConfigs[] = [
                'reciter_id' => $maher->id,
                'base_url' => 'https://download.quranicaudio.com/quran/maher_almuaiqly/',
                'quality' => '128',
            ];
        }

        if ($abdulbasit) {
            $reciterConfigs[] = [
                'reciter_id' => $abdulbasit->id,
                'base_url' => 'https://download.quranicaudio.com/quran/abdul_basit_murattal/',
                'quality' => '128',
            ];
        }

        if ($shuraim) {
            $reciterConfigs[] = [
                'reciter_id' => $shuraim->id,
                'base_url' => 'https://download.quranicaudio.com/quran/sa3ood_ash_shuraym/',
                'quality' => '128',
            ];
        }

        if ($dosari) {
            $reciterConfigs[] = [
                'reciter_id' => $dosari->id,
                'base_url' => 'https://download.quranicaudio.com/quran/yasser_ad-dussary/',
                'quality' => '128',
            ];
        }

        if ($peshawa) {
            $reciterConfigs[] = [
                'reciter_id' => $peshawa->id,
                'base_url' => 'https://server14.mp3quran.net/peshawa/',
                'quality' => '128',
            ];
        }

        if ($raad) {
            $reciterConfigs[] = [
                'reciter_id' => $raad->id,
                'base_url' => 'https://server12.mp3quran.net/kurd/',
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
