<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategoryTajweedRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            [
                'name'           => 'Noon Sakinah & Tanween',
                'name_ku'        => 'نوونی ساکین و تەنوین',
                'name_ar'        => 'أحكام النون الساكنة والتنوين',
                'slug'           => 'noon-sakinah-tanween',
                'description'    => 'Rules governing the pronunciation of Noon with Sukoon (ن) and Tanween (double vowels ً ٍ ٌ) before different Arabic letters.',
                'description_ku' => 'حوکمەکانی نوونی ساکین و تەنوین لە پێشتر ئەو پیتانەی کە لە دوایانی دێن.',
                'description_ar' => 'الأحكام المتعلقة بنطق النون الساكنة والتنوين أمام الحروف المختلفة.',
                'order'          => 1,
                'is_active'      => true,
            ],
            [
                'name'           => 'Meem Sakinah',
                'name_ku'        => 'میمی ساکین',
                'name_ar'        => 'أحكام الميم الساكنة',
                'slug'           => 'meem-sakinah',
                'description'    => 'Rules governing the pronunciation of Meem with Sukoon (م) before different Arabic letters.',
                'description_ku' => 'حوکمەکانی میمی ساکین لە پێشتر ئەو پیتانەی کە لە دوایانی دێن.',
                'description_ar' => 'الأحكام المتعلقة بنطق الميم الساكنة أمام الحروف المختلفة.',
                'order'          => 2,
                'is_active'      => true,
            ],
            [
                'name'           => 'Madd (Prolongation)',
                'name_ku'        => 'مەد (درێژکردنەوە)',
                'name_ar'        => 'المدود',
                'slug'           => 'madd-prolongation',
                'description'    => 'Rules governing the elongation of vowel sounds using Alif, Waw, and Yaa letters.',
                'description_ku' => 'حوکمەکانی درێژکردنەوەی دەنگی دەستوور بە هەموار بەکارهێنانی ئەلف و واو و یا.',
                'description_ar' => 'الأحكام المتعلقة بإطالة صوت حروف المد الثلاثة: الألف والواو والياء.',
                'order'          => 3,
                'is_active'      => true,
            ],
            [
                'name'           => 'Rules of Raa',
                'name_ku'        => 'حوکمەکانی ڕا (ر)',
                'name_ar'        => 'أحكام الراء',
                'slug'           => 'rules-of-raa',
                'description'    => 'Rules governing the heavy (Tafkhim) and light (Tarqiq) pronunciation of the letter Raa (ر).',
                'description_ku' => 'حوکمەکانی قووڵ (تەفخیم) و سووک (تەرقیق) خوێندنەوەی پیتی ڕا (ر).',
                'description_ar' => 'أحكام تفخيم وترقيق الراء.',
                'order'          => 4,
                'is_active'      => true,
            ],
            [
                'name'           => 'Qalqalah',
                'name_ku'        => 'قەلقەلە',
                'name_ar'        => 'القلقلة',
                'slug'           => 'qalqalah',
                'description'    => 'The echoing or bouncing sound produced when one of the five Qalqalah letters (ق ط ب ج د) has a Sukoon.',
                'description_ku' => 'دەنگی هەژمار کردن یان بازدانی کە لە کاتی سوکوون بوونی یەکێک لە پێنج پیتی قەلقەلە دا دروست دەبێت.',
                'description_ar' => 'الصوت المرتد أو المقلقل الذي ينتج عند سكون أحد حروف القلقلة الخمسة.',
                'order'          => 5,
                'is_active'      => true,
            ],
            [
                'name'           => 'Ghunnah',
                'name_ku'        => 'غوننە',
                'name_ar'        => 'الغنة',
                'slug'           => 'ghunnah',
                'description'    => 'The nasal resonance sound that occurs with Noon and Meem in specific situations.',
                'description_ku' => 'دەنگی ناویەکەی کە لەگەڵ نوون و میم لە دۆخی دیاریکراودا دروست دەبێت.',
                'description_ar' => 'صوت الرنين الأنفي الذي يحدث مع النون والميم في مواضع محددة.',
                'order'          => 6,
                'is_active'      => true,
            ],
            [
                'name'           => 'Laam of Allah',
                'name_ku'        => 'لامی لەفزی جەلالە',
                'name_ar'        => 'لام لفظ الجلالة',
                'slug'           => 'laam-of-allah',
                'description'    => 'Rules for pronouncing the Laam in the sacred word "Allah" as heavy or light.',
                'description_ku' => 'حوکمەکانی خوێندنەوەی لامی ناوی پیرۆزی "الله" قووڵ یان سووک.',
                'description_ar' => 'أحكام تفخيم وترقيق لام لفظ الجلالة.',
                'order'          => 7,
                'is_active'      => true,
            ],
            [
                'name'           => "Laam Al-Ta'reef",
                'name_ku'        => 'لامی ئال التعریف',
                'name_ar'        => 'لام التعريف',
                'slug'           => 'laam-al-taareef',
                'description'    => "Rules for pronouncing the definite article Laam (ال) as solar (Idgham) or lunar (Idhhar).",
                'description_ku' => 'حوکمەکانی لامی ئال التعریف (ال) شەمسی (ئیدغام) یان قەمەری (ئیزهار).',
                'description_ar' => 'أحكام لام التعريف بين الشمسية والقمرية.',
                'order'          => 8,
                'is_active'      => true,
            ],
            [
                'name'           => 'Advanced Idgham',
                'name_ku'        => 'ئیدغامی پێشکەوتوو',
                'name_ar'        => 'الإدغام المتماثل والمتجانس والمتقارب',
                'slug'           => 'advanced-idgham',
                'description'    => 'Rules governing the merging of identical, similar-articulated, and close-articulated letter pairs.',
                'description_ku' => 'حوکمەکانی تێکەڵ کردنی جووتی پیتانی یەکسان، هاوشێوە و نزیک لە یەکتر.',
                'description_ar' => 'أحكام إدغام المتماثلين والمتجانسين والمتقاربين.',
                'order'          => 9,
                'is_active'      => true,
            ],
            [
                'name'           => 'Special Rules',
                'name_ku'        => 'حوکمە تایبەتەکان',
                'name_ar'        => 'الأحكام الخاصة',
                'slug'           => 'special-rules',
                'description'    => 'Unique recitation rules such as Saktah (silent pause), Imalah, Ishmam, and other special Tajweed cases.',
                'description_ku' => 'حوکمە تایبەتەکانی خوێندنەوەی قورئان وەک سەکتە، ئیمالە، ئیشمام و حوکمی تری تەجوید.',
                'description_ar' => 'الأحكام الخاصة كالسكتة والإمالة والإشمام وغيرها.',
                'order'          => 10,
                'is_active'      => true,
            ],
        ];

        foreach ($categories as $cat) {
            \App\Models\TajweedRuleCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }
}
