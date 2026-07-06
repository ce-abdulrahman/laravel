<?php

namespace Database\Seeders;

use App\Models\Reciter;
use Illuminate\Database\Seeder;

class ReciterSeeder extends Seeder
{
    public function run(): void
    {
        $reciters = [
            [
                'id' => 1,
                'name' => 'Mishary Rashid Alafasy',
                'slug' => 'mishary-alafasy',
                'riwayah' => 'Hafs',
                'country' => 'Kuwait',
                'language' => 'ar',
                'audio_base_url' => 'https://download.quranicaudio.com/quran/mishaari_raashid_al_3afaasee/',
                'supports_ayah_audio' => true,
                'image' => null,
                'is_active' => true,
            ],
            [
                'id' => 2,
                'name' => 'Maher Al Muaiqly',
                'slug' => 'maher-almuaiqly',
                'riwayah' => 'Hafs',
                'country' => 'Saudi Arabia',
                'language' => 'ar',
                'audio_base_url' => 'https://download.quranicaudio.com/quran/maher_almuaiqly/',
                'supports_ayah_audio' => true,
                'image' => null,
                'is_active' => true,
            ],
            [
                'id' => 3,
                'name' => 'Abdul Basit Abdus Samad',
                'slug' => 'abdul-basit',
                'riwayah' => 'Hafs',
                'country' => 'Egypt',
                'language' => 'ar',
                'audio_base_url' => 'https://download.quranicaudio.com/quran/abdul_basit_murattal/',
                'supports_ayah_audio' => true,
                'image' => null,
                'is_active' => true,
            ],
            [
                'id' => 4,
                'name' => 'Saud Shuraim',
                'slug' => 'saud-shuraim',
                'riwayah' => 'Hafs',
                'country' => 'Saudi Arabia',
                'language' => 'ar',
                'audio_base_url' => 'https://download.quranicaudio.com/quran/sa3ood_ash_shuraym/',
                'supports_ayah_audio' => true,
                'image' => null,
                'is_active' => true,
            ],
            [
                'id' => 5,
                'name' => 'Yasser Al Dosari',
                'slug' => 'yasser-dosari',
                'riwayah' => 'Hafs',
                'country' => 'Saudi Arabia',
                'language' => 'ar',
                'audio_base_url' => 'https://download.quranicaudio.com/quran/yasser_ad-dussary/',
                'supports_ayah_audio' => true,
                'image' => null,
                'is_active' => true,
            ],
            [
                'id' => 6,
                'name' => 'Peshawa Qader Al-Kurdi',
                'slug' => 'peshawa-kurdi',
                'riwayah' => 'Hafs',
                'country' => 'Kurdistan',
                'language' => 'ku',
                'audio_base_url' => 'https://server14.mp3quran.net/peshawa/',
                'supports_ayah_audio' => true,
                'image' => null,
                'is_active' => true,
            ],
            [
                'id' => 7,
                'name' => 'Raad Mohammad Al-Kurdi',
                'slug' => 'raad-kurdi',
                'riwayah' => 'Hafs',
                'country' => 'Kurdistan',
                'language' => 'ku',
                'audio_base_url' => 'https://server12.mp3quran.net/kurd/',
                'supports_ayah_audio' => true,
                'image' => null,
                'is_active' => true,
            ],
        ];

        foreach ($reciters as $reciter) {
            Reciter::updateOrCreate(
                ['id' => $reciter['id']],
                $reciter
            );
        }
    }
}
