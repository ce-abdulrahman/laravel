<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Surah;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $surahIsra = Surah::where('number', 17)->first();
        $surahQamar = Surah::where('number', 54)->first();

        Banner::updateOrCreate(
            ['verse' => 'ئەم قورئانە ڕێنمایی دەکات بۆ ئەوەی ڕاستترینەوە'],
            [
                'title_arabic' => 'إِنَّ هَٰذَا الْقُرْآنَ يَهْدِي لِلَّتِي هِيَ أَقْوَمُ',
                'source' => '— ئیسرا ١٧:٩',
                'surah_id' => $surahIsra?->id,
                'ayah_number' => 9,
                'is_active' => true,
                'order' => 1,
            ]
        );

        Banner::updateOrCreate(
            ['verse' => 'ئێمە قورئانەکەمان ئاسان کرد بۆ یادەوەری'],
            [
                'title_arabic' => 'وَلَقَدْ يَسَّرْنَا الْقُرْآنَ لِلذِّكْرِ',
                'source' => '— القمر ٥٤:١٧',
                'surah_id' => $surahQamar?->id,
                'ayah_number' => 17,
                'is_active' => true,
                'order' => 2,
            ]
        );
    }
}
