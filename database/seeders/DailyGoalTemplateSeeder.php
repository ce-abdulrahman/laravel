<?php

namespace Database\Seeders;

use App\Models\DailyGoalTemplate;
use Illuminate\Database\Seeder;

class DailyGoalTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'value' => 100,
                'is_active' => true,
                'translations' => [
                    'en' => [
                        'title' => 'Low',
                        'description' => 'Recommended for beginners to build daily consistency.',
                    ],
                    'ar' => [
                        'title' => 'خفيف',
                        'description' => 'موصى به للمبتدئين لبناء عادة يومية مستمرة.',
                    ],
                    'ku' => [
                        'title' => 'کەم',
                        'description' => 'پێشنیارکراو بۆ سەرەتاییەکان بۆ دروستکردنی بەردەوامی ڕۆژانە.',
                    ],
                ]
            ],
            [
                'value' => 500,
                'is_active' => true,
                'translations' => [
                    'en' => [
                        'title' => 'Medium',
                        'description' => 'A balanced target for regular dhikr tracking.',
                    ],
                    'ar' => [
                        'title' => 'متوسط',
                        'description' => 'هدف متوازن لتتبع الأذكار اليومية بانتظام.',
                    ],
                    'ku' => [
                        'title' => 'ناوەند',
                        'description' => 'ئامانجێکی هاوسەنگ بۆ بەدواداچوونی زیکری ڕۆژانە بە ڕێکی.',
                    ],
                ]
            ],
            [
                'value' => 1000,
                'is_active' => true,
                'translations' => [
                    'en' => [
                        'title' => 'High',
                        'description' => 'For advanced users seeking high daily engagement.',
                    ],
                    'ar' => [
                        'title' => 'عالي',
                        'description' => 'للمستخدمين المتقدمين الساعين لمستويات عالية من الذكر اليومي.',
                    ],
                    'ku' => [
                        'title' => 'زۆر',
                        'description' => 'بۆ بەکارهێنەرانی پێشکەوتوو کە بەدوای بەشدارییەکی زۆری ڕۆژانەدا دەگەڕێن.',
                    ],
                ]
            ]
        ];

        foreach ($templates as $tData) {
            // Check if template with same value already exists to prevent duplicate seeding
            $template = DailyGoalTemplate::firstOrCreate(
                ['value' => $tData['value']],
                ['is_active' => $tData['is_active']]
            );

            $template->saveTranslationsFromArray($tData['translations']);
        }
    }
}
