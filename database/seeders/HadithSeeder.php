<?php

namespace Database\Seeders;

use App\Models\Hadith;
use App\Models\HadithCategory;
use Illuminate\Database\Seeder;

class HadithSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Hadith Categories
        $ethics = HadithCategory::updateOrCreate(
            ['icon' => 'favorite_rounded'],
            [
                'name_en' => 'Ethics & Character',
                'name_ku' => 'ئاکار و ڕەوشت',
                'name_ar' => 'الأخلاق والآداب',
                'order' => 1,
                'is_active' => true,
            ]
        );

        $worship = HadithCategory::updateOrCreate(
            ['icon' => 'mosque_rounded'],
            [
                'name_en' => 'Worship & Actions',
                'name_ku' => 'پەرستش و کارەکان',
                'name_ar' => 'العبادات والأعمال',
                'order' => 2,
                'is_active' => true,
            ]
        );

        $creed = HadithCategory::updateOrCreate(
            ['icon' => 'shield_rounded'],
            [
                'name_en' => 'Creed & Faith',
                'name_ku' => 'بیروباوەڕ و ئیمان',
                'name_ar' => 'العقيدة والإيمان',
                'order' => 3,
                'is_active' => true,
            ]
        );

        // 2. Seed Ethics Hadiths
        $ethicsHadiths = [
            [
                'arabic_text' => 'إِنَّمَا بُعِثْتُ لِأُتَمِّمَ صَالِحَ الْأَخْلَاقِ.',
                'translation_ku' => 'بێگومان من تەنها بۆ ئەوە نێردراوم تاوەکو ڕەوشتە بەرز و چاکەکان تەواو بکەم.',
                'narrator' => 'عن أبي هريرة رضي الله عنه',
                'source' => 'رواه أحمد والبخاري في الأدب المفرد',
                'explanation_ku' => 'ئەم فەرموودەیە ئاماژە بەوە دەکات کە یەکێک لە گەورەترین ئامانجەکانی ناردنی پێغەمبەر (د.خ) بەرزکردنەوە و تەواوکردنی بەها ئەخلاقییەکانە لە نێو کۆمەڵگەدا.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'arabic_text' => 'لَا يُؤْمِنُ أَحَدُكُمْ حَتَّى يُحِبَّ لِأَخِيهِ مَا يُحِبُّ لِنَفْسِهِ.',
                'translation_ku' => 'هیچ یەکێک لە ئێوە ئیمانی تەواو نابێت، تاوەکو ئەوەی بۆ خۆی پێی خۆشە بۆ براکەشی پێی خۆش بێت.',
                'narrator' => 'عن أنس بن مالك رضي الله عنه',
                'source' => 'رواه البخاري ومسلم',
                'explanation_ku' => 'ئەم فەرموودەیە هاندەرە بۆ دروستکردنی خۆشەویستی و یەکگرتوویی لە نێوان باوەڕداران و دوورکەوتنەوە لە حەسەدی و خۆپەرستی.',
                'order' => 2,
                'is_active' => true,
            ]
        ];

        foreach ($ethicsHadiths as $item) {
            $ethics->hadiths()->updateOrCreate(
                ['arabic_text' => $item['arabic_text']],
                $item
            );
        }

        // 3. Seed Worship Hadiths
        $worshipHadiths = [
            [
                'arabic_text' => 'إِنَّمَا الْأَعْمَالُ بِالنِّيَّاتِ، وَإِنَّمَا لِكُلِّ امْرِئٍ مَا نَوَى.',
                'translation_ku' => 'بێگومان پاداشتی کارەکان بەپێی نییەتەکانە، و بۆ هەر مرۆڤێک ئەوە هەیە کە نییەتی بۆ هێناوە.',
                'narrator' => 'عن عمر بن الخطاب رضي الله عنه',
                'source' => 'رواه البخاري ومسلم',
                'explanation_ku' => 'ئەم فەرموودەیە یەکێکە لە بنچینە گەورەکانی ئیسلام، کە تێیدا ڕوون دەبێتەوە کە وەرگرتنی هەر کارێک بەندە بە دڵسۆزی و پاکی نییەت بۆ خودای گەورە.',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'arabic_text' => 'بُنِيَ الْإِسْلَامُ عَلَى خَمْسٍ: شَهَادَةِ أَنْ لَا إِلَهَ إِلَّا اللَّهُ وَأَنَّ مُحَمَّدًا رَسُولُ اللَّهِ، وَإِقَامِ الصَّلَاةِ، وَإِيتَاءِ الزَّكَاةِ، وَالْحَجِّ، وَصَوْمِ رَمَضَانَ.',
                'translation_ku' => 'ئیسلام لەسەر پێنج بنەما بونیات نراوە: شایەتیدان بەوەی هیچ پەرستراوێک نییە شایستەی پەرستن بێت جگە لە ئەڵڵا و محەممەد نێردراوی ئەوە، و ئەنجامدانی نوێژە فەرزەکان، و دانی زەکات، و حەجکردنی ماڵی خودا، و ڕۆژووی مانگی ڕەمەزان.',
                'narrator' => 'عن عبد الله بن عمر رضي الله عنهما',
                'source' => 'رواه البخاري ومسلم',
                'explanation_ku' => 'پایە گەورەکانی ئیسلام لێرەدا ڕوون کراونەتەوە کە وەک کۆڵەکەی بونیادی ژیانی موسڵمان وایە.',
                'order' => 2,
                'is_active' => true,
            ]
        ];

        foreach ($worshipHadiths as $item) {
            $worship->hadiths()->updateOrCreate(
                ['arabic_text' => $item['arabic_text']],
                $item
            );
        }

        // 4. Seed Creed Hadiths
        $creedHadiths = [
            [
                'arabic_text' => 'مَنْ كَانَ يُؤْمِنُ بِاللَّهِ وَالْيَوْمِ الْآخِرِ فَلْيَقُلْ خَيْرًا أَوْ لِيَصْمُتْ.',
                'translation_ku' => 'هەرکەسێک باوەڕی بە خودا و ڕۆژی دوایی هەیە، با قسەی چاک بکات یان بێدەنگ بێت.',
                'narrator' => 'عن أبي هريرة رضي الله عنه',
                'source' => 'رواه البخاري ومسلم',
                'explanation_ku' => 'ئەم فەرموودەیە پەیوەندی نێوان ڕاستی ئیمان و پاراستنی زمان دەردەخات، کە بێدەنگی لە قسەی خراپ نیشانەی باوەڕە.',
                'order' => 1,
                'is_active' => true,
            ]
        ];

        foreach ($creedHadiths as $item) {
            $creed->hadiths()->updateOrCreate(
                ['arabic_text' => $item['arabic_text']],
                $item
            );
        }
    }
}
