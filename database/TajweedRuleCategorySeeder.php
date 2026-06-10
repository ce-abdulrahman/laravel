<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TajweedRuleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            [
                'id'             => 1,
                'name'           => 'Rules of Noon Sakinah and Tanween',
                'name_ku'        => 'یاساکانی نوونی ساکن و تەنووین',
                'name_ar'        => 'أحكام النون الساكنة والتنوين',
                'slug'           => 'rules-of-noon-sakinah-and-tanween',
                'description'    => 'Rules governing the pronunciation of a static Noon or Tanween.',
                'description_ku' => 'ئەو یاسایانەی تایبەتن بە چۆنیەتی دەربڕینی نوونی ساکن و تەنووین.',
                'description_ar' => 'الأحكام الخاصة بنطق النون الساكنة والتنوين.',
                'order'          => 1,
                'is_active'      => true,
            ],
            [
                'id'             => 2,
                'name'           => 'Rules of Meem Sakinah',
                'name_ku'        => 'یاساکانی میمی ساکن',
                'name_ar'        => 'أحكام الميم الساكنة',
                'slug'           => 'rules-of-meem-sakinah',
                'description'    => 'Rules governing the pronunciation of a static Meem.',
                'description_ku' => 'ئەو یاسایانەی تایبەتن بە چۆنیەتی دەربڕینی پیتی میمی ساکن.',
                'description_ar' => 'الأحكام الخاصة بنطق الميم الساكنة.',
                'order'          => 2,
                'is_active'      => true,
            ],
            [
                'id'             => 3,
                'name'           => 'Rules of Madd (Prolongation)',
                'name_ku'        => 'یاساکانی مەد (درێژکردنەوە)',
                'name_ar'        => 'أحكام المد',
                'slug'           => 'rules-of-madd',
                'description'    => 'Rules governing the prolongation of sounds.',
                'description_ku' => 'ئەو یاسایانەی تایبەتن بە درێژکردنەوەی دەنگ لە پیتەکانی مەد و لین.',
                'description_ar' => 'الأحكام الخاصة بإطالة الصوت بحرف من حروف المد واللين.',
                'order'          => 3,
                'is_active'      => true,
            ],
            [
                'id'             => 4,
                'name'           => 'Rules of the letter Raa',
                'name_ku'        => 'یاساکانی پیتی ڕاء',
                'name_ar'        => 'أحكام الراء',
                'slug'           => 'rules-of-the-letter-raa',
                'description'    => 'Rules governing the heavy and light pronunciation of Raa.',
                'description_ku' => 'ئەو یاسایانەی تایبەتن بە کاتی قەڵەوکردن و تەنککردنی پیتی ڕاء.',
                'description_ar' => 'الأحكام الخاصة بتفخيم وترقيق حرف الراء.',
                'order'          => 4,
                'is_active'      => true,
            ],
            [
                'id'             => 5,
                'name'           => 'Rules of Qalqalah (Echoing)',
                'name_ku'        => 'یاساکانی قەلقەلە (لەرزاندن)',
                'name_ar'        => 'أحكام القلقلة',
                'slug'           => 'rules-of-qalqalah',
                'description'    => 'The echoing sound produced when pronouncing certain letters.',
                'description_ku' => 'دروستکردنی دەنگێکی لەرزۆک لە کاتی دەربڕینی پیتەکانی قەلقەلە.',
                'description_ar' => 'اضطراب مخرج الحرف الساكن عند النطق به.',
                'order'          => 5,
                'is_active'      => true,
            ],
            [
                'id'             => 6,
                'name'           => 'Rules of Ghunnah (Nasalization)',
                'name_ku'        => 'یاساکانی غوننە (نوون و میمی شەددەدار)',
                'name_ar'        => 'أحكام النون والميم المشددتين',
                'slug'           => 'rules-of-ghunnah',
                'description'    => 'Pronouncing the letter with a nasal sound for 2 beats.',
                'description_ku' => 'دەرکردنی دەنگی غوننە کاتێک نوون یان میم شەددەیان لەسەرە.',
                'description_ar' => 'إظهار الغنة بمقدار حركتين في النون والميم المشددتين.',
                'order'          => 6,
                'is_active'      => true,
            ],
            [
                'id'             => 7,
                'name'           => 'Silent Letters',
                'name_ku'        => 'پیتە بێدەنگەکان (ئەوانەی ناخوێندرێنەوە)',
                'name_ar'        => 'الحروف التي تكتب ولا تنطق',
                'slug'           => 'silent-letters',
                'description'    => 'Letters written in the text but not pronounced.',
                'description_ku' => 'ئەو پیتانەی لە قورئاندا نووسراون بەڵام دەپەڕێندرێن.',
                'description_ar' => 'الحروف الزائدة في الرسم العثماني التي تسقط في الدرج.',
                'order'          => 7,
                'is_active'      => true,
            ],
        ];

        foreach ($categories as &$category) {
            $category['created_at'] = $now;
            $category['updated_at'] = $now;
        }

        DB::table('tajweed_rule_categories')->insert($categories);
    }
}