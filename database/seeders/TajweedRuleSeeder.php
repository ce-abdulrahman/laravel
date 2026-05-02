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
                'slug' => 'idgham',
                'category' => 'noon_sakinah',
                'color_code' => '#22c55e',
                'description' => 'Merging one letter into another with or without ghunnah.',
                'example_text' => 'مِنْ رَبِّهِمْ',
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Ikhfa',
                'slug' => 'ikhfa',
                'category' => 'noon_sakinah',
                'color_code' => '#f59e0b',
                'description' => 'Hiding the noon sakinah or tanween with ghunnah.',
                'example_text' => 'مِنْ شَرِّ',
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Iqlab',
                'slug' => 'iqlab',
                'category' => 'noon_sakinah',
                'color_code' => '#8b5cf6',
                'description' => 'Converting noon sakinah or tanween into meem before baa.',
                'example_text' => 'سَمِيعٌ بَصِيرٌ',
                'priority' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Izhar',
                'slug' => 'izhar',
                'category' => 'noon_sakinah',
                'color_code' => '#3b82f6',
                'description' => 'Clear pronunciation of noon sakinah or tanween.',
                'example_text' => 'مِنْ آمَنَ',
                'priority' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Qalqalah',
                'slug' => 'qalqalah',
                'category' => 'letters',
                'color_code' => '#ef4444',
                'description' => 'Echoing sound on قطب جد letters when sakin.',
                'example_text' => 'أَحَدٌ',
                'priority' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Ghunnah',
                'slug' => 'ghunnah',
                'category' => 'sound',
                'color_code' => '#14b8a6',
                'description' => 'Nasal sound for noon and meem mushaddad.',
                'example_text' => 'إِنَّ',
                'priority' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Madd Tabi‘i',
                'slug' => 'madd-tabii',
                'category' => 'madd',
                'color_code' => '#0ea5e9',
                'description' => 'Natural prolongation of two counts.',
                'example_text' => 'قَالَ',
                'priority' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Madd Munfasil',
                'slug' => 'madd-munfasil',
                'category' => 'madd',
                'color_code' => '#6366f1',
                'description' => 'Elongation when madd letter comes at the end of a word and hamzah at the beginning of the next.',
                'example_text' => 'إِنَّا أَعْطَيْنَاكَ',
                'priority' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Madd Muttasil',
                'slug' => 'madd-muttasil',
                'category' => 'madd',
                'color_code' => '#9333ea',
                'description' => 'Elongation when madd letter and hamzah are in the same word.',
                'example_text' => 'جَاءَ',
                'priority' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Meem Sakinah Ikhfa Shafawi',
                'slug' => 'ikhfa-shafawi',
                'category' => 'meem_sakinah',
                'color_code' => '#f97316',
                'description' => 'Hidden meem before baa with ghunnah.',
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
