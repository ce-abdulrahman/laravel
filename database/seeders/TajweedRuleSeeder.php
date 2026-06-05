<?php

namespace Database\Seeders;

use App\Models\TajweedRule;
use Illuminate\Database\Seeder;

class TajweedRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name' => 'Idgham',
                'name_ku' => 'ئیدغام (تێکەڵکردن)',
                'name_ar' => 'الإدغام',
                'slug' => 'idgham',
                'category' => 'noon_sakinah',
                'color_code' => '#22c55e',
                'description' => 'Merging one letter into another with or without ghunnah.',
                'description_ku' => 'تێکەڵکردنی پیتێکە بە پیتێکی ترەوە بە غوننە یان بەبێ غوننە کە پاش پیتەکانی نوونی ساکین یان تەنوین دێن.',
                'example_text' => 'مِنْ رَبِّهِمْ',
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Ikhfa',
                'name_ku' => 'ئیخفا (شاردنەوە)',
                'name_ar' => 'الإخفاء',
                'slug' => 'ikhfa',
                'category' => 'noon_sakinah',
                'color_code' => '#f59e0b',
                'description' => 'Hiding the noon sakinah or tanween with ghunnah.',
                'description_ku' => 'شاردنەوەی دەنگی نوونی ساکین یان تەنوینە لەگەڵ دەرکردنی دەنگی غوننە لە کاتی گەیاندنی بە پیتەکانی ئیخفا.',
                'example_text' => 'مِنْ شَرِّ',
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Iqlab',
                'name_ku' => 'ئیقلاب (گۆڕین)',
                'name_ar' => 'الإقلاب',
                'slug' => 'iqlab',
                'category' => 'noon_sakinah',
                'color_code' => '#8b5cf6',
                'description' => 'Converting noon sakinah or tanween into meem before baa.',
                'description_ku' => 'گۆڕینی دەنگی نوونی ساکین یان تەنوینە بۆ پیتی میم (م) لە کاتێکدا بکەوێتە پێش پیتی باء (ب).',
                'example_text' => 'سَمِيعٌ بَصِيرٌ',
                'priority' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Izhar',
                'name_ku' => 'ئیزهار (دەرخستن)',
                'name_ar' => 'الإظهار',
                'slug' => 'izhar',
                'category' => 'noon_sakinah',
                'color_code' => '#3b82f6',
                'description' => 'Clear pronunciation of noon sakinah or tanween.',
                'description_ku' => 'خوێندنەوە و دەربڕینی ئاشکرا و ڕوونی نوونی ساکین یان تەنوینە بێ غوننە کاتێک بکەوێتە پێش پیتەکانی قورگ.',
                'example_text' => 'مِنْ آمَنَ',
                'priority' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Qalqalah',
                'name_ku' => 'قەلقەلە (لەرزاندن)',
                'name_ar' => 'القلقلة',
                'slug' => 'qalqalah',
                'category' => 'letters',
                'color_code' => '#ef4444',
                'description' => 'Echoing sound on قطب جد letters when sakin.',
                'description_ku' => 'لەرزاندن یان لەرینەوەی دەنگی پیتەکانی (ق، ط، ب، ج، د) لە کاتی سکون یان وەستان لەسەریان.',
                'example_text' => 'أَحَدٌ',
                'priority' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Ghunnah',
                'name_ku' => 'غوننە (دەنگی لووت)',
                'name_ar' => 'الغنة',
                'slug' => 'ghunnah',
                'category' => 'sound',
                'color_code' => '#14b8a6',
                'description' => 'Nasal sound for noon and meem mushaddad.',
                'description_ku' => 'دەرکردنی دەنگێکی خۆشە لە قورگ و لووتەوە (خەیشووم) بۆ پیتەکانی نوون و میمی توندکراو (موشەددەد).',
                'example_text' => 'إِنَّ',
                'priority' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Madd Tabi‘i',
                'name_ku' => 'مەدی سروشتی',
                'name_ar' => 'المد الطبيعي',
                'slug' => 'madd-tabii',
                'category' => 'madd',
                'color_code' => '#0ea5e9',
                'description' => 'Natural prolongation of two counts.',
                'description_ku' => 'Natural prolongation of two counts.',
                'example_text' => 'قَالَ',
                'priority' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Madd Munfasil',
                'name_ku' => 'مەدی جیاواز',
                'name_ar' => 'المد المنفصل',
                'slug' => 'madd-munfasil',
                'category' => 'madd',
                'color_code' => '#6366f1',
                'description' => 'Elongation when madd letter comes at the end of a word and hamzah at the beginning of the next.',
                'description_ku' => 'درێژکردنەوەی دەنگە کاتێک پیتی مەد لە کۆتایی وشەیەکدا بێت و هەمزە لە سەرەتای وشەی دواتردا بێت.',
                'example_text' => 'إِنَّا أَعْطَيْنَاكَ',
                'priority' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Madd Muttasil',
                'name_ku' => 'مەدی پێکەوەبەستراو',
                'name_ar' => 'المد المتصل',
                'slug' => 'madd-muttasil',
                'category' => 'madd',
                'color_code' => '#9333ea',
                'description' => 'Elongation when madd letter and hamzah are in the same word.',
                'description_ku' => 'درێژکردنەوەی دەنگە کاتێک پیتی مەد و هەمزە پێکەوە لە ناو یەک وشەدا بن.',
                'example_text' => 'جَاءَ',
                'priority' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Meem Sakinah Ikhfa Shafawi',
                'name_ku' => 'ئیخفای لێوی (ئیخفای شەفەوی)',
                'name_ar' => 'الإخفاء الشفوي',
                'slug' => 'ikhfa-shafawi',
                'category' => 'meem_sakinah',
                'color_code' => '#f97316',
                'description' => 'Hidden meem before baa with ghunnah.',
                'description_ku' => 'شاردنەوەی دەنگی پیتی میمی ساکینە کاتێک بکەوێتە پێش پیتی باء (ب) لەگەڵ غوننە.',
                'example_text' => 'تَرْمِيهِمْ بِحِجَارَةٍ',
                'priority' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            TajweedRule::updateOrCreate(
                ['slug' => $rule['slug']],
                $rule
            );
        }
    }
}
