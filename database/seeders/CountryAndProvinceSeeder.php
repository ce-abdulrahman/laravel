<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Province;
use App\Models\ProvinceTranslation;
use App\Models\Language;
use Illuminate\Database\Seeder;

class CountryAndProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $langs = Language::all()->keyBy('code');
        
        $data = [
            'IQ' => [
                'names' => ['en' => 'Iraq', 'ar' => 'العراق', 'ku' => 'عێراق'],
                'provinces' => [
                    ['names' => ['en' => 'Erbil', 'ar' => 'أربيل', 'ku' => 'هەولێر']],
                    ['names' => ['en' => 'Sulaymaniyah', 'ar' => 'السليمانية', 'ku' => 'سلێمانی']],
                    ['names' => ['en' => 'Duhok', 'ar' => 'دهوك', 'ku' => 'دهۆک']],
                    ['names' => ['en' => 'Halabja', 'ar' => 'حلبجة', 'ku' => 'هەڵەبجە']],
                    ['names' => ['en' => 'Kirkuk', 'ar' => 'كركوك', 'ku' => 'کەرکووک']],
                    ['names' => ['en' => 'Baghdad', 'ar' => 'بغداد', 'ku' => 'بەغداد']],
                ]
            ],
            'SA' => [
                'names' => ['en' => 'Saudi Arabia', 'ar' => 'المملكة العربية السعودية', 'ku' => 'عەرەبستانی سعوودی'],
                'provinces' => [
                    ['names' => ['en' => 'Makkah', 'ar' => 'مكة المكرمة', 'ku' => 'مەککە']],
                    ['names' => ['en' => 'Madinah', 'ar' => 'المدينة المنورة', 'ku' => 'مەدینە']],
                    ['names' => ['en' => 'Riyadh', 'ar' => 'الرياض', 'ku' => 'ڕیاز']],
                ]
            ],
            'TR' => [
                'names' => ['en' => 'Turkey', 'ar' => 'تركيا', 'ku' => 'تورکیا'],
                'provinces' => [
                    ['names' => ['en' => 'Istanbul', 'ar' => 'إسطنبول', 'ku' => 'ئیستانبوڵ']],
                    ['names' => ['en' => 'Ankara', 'ar' => 'أنقرة', 'ku' => 'ئەنکەرە']],
                    ['names' => ['en' => 'Diyarbakir', 'ar' => 'ديار بكر', 'ku' => 'ئامەد']],
                ]
            ]
        ];

        foreach ($data as $code => $info) {
            $country = Country::firstOrCreate(['code' => $code]);
            
            foreach ($info['names'] as $locale => $name) {
                if (isset($langs[$locale])) {
                    CountryTranslation::updateOrCreate([
                        'country_id' => $country->id,
                        'language_id' => $langs[$locale]->id,
                        'field' => 'name',
                    ], ['value' => $name]);
                }
            }

            foreach ($info['provinces'] as $provInfo) {
                $province = Province::create(['country_id' => $country->id]);
                
                foreach ($provInfo['names'] as $locale => $name) {
                    if (isset($langs[$locale])) {
                        ProvinceTranslation::updateOrCreate([
                            'province_id' => $province->id,
                            'language_id' => $langs[$locale]->id,
                            'field' => 'name',
                        ], ['value' => $name]);
                    }
                }
            }
        }
    }
}
