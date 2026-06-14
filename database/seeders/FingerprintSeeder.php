<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\AchievementCategory;
use Illuminate\Database\Seeder;

class FingerprintSeeder extends Seeder
{
    public function run(): void
    {
        $catSession = AchievementCategory::where('icon', '⚡')->first() ?? AchievementCategory::first();
        $catMilestone = AchievementCategory::where('icon', '📿')->first() ?? AchievementCategory::first();

        $achievements = [
            [
                'key' => 'fingerprint_first_session',
                'category_id' => $catSession->id,
                'icon' => '👆',
                'condition_type' => Achievement::CONDITION_FINGERPRINT_TOTAL_SESSIONS,
                'condition_value' => 1,
                'reward_points' => 20,
                'translations' => [
                    'en' => ['name' => 'First Fingerprint Session', 'description' => 'Completed your first session using Fingerprint Counter Mode.'],
                    'ar' => ['name' => 'أول جلسة بصمة', 'description' => 'أكملت أول جلسة لك باستخدام وضع عداد البصمة.'],
                    'ku' => ['name' => 'یەکەم سێشنی پەنجەمۆر', 'description' => 'یەکەم سێشنت بە بەکارهێنانی مۆدی پەنجەمۆر تەواو کرد.'],
                ]
            ],
            [
                'key' => 'fingerprint_100_counts',
                'category_id' => $catMilestone->id,
                'icon' => '📈',
                'condition_type' => Achievement::CONDITION_FINGERPRINT_TOTAL_COUNTS,
                'condition_value' => 100,
                'reward_points' => 30,
                'translations' => [
                    'en' => ['name' => '100 Fingerprint Counts', 'description' => 'Completed 100 total counts in Fingerprint Mode.'],
                    'ar' => ['name' => '١٠٠ تسبيحة بالبصمة', 'description' => 'أكملت ١٠٠ تسبيحة إجمالاً في وضع البصمة.'],
                    'ku' => ['name' => '١٠٠ تەسبیحی پەنجەمۆر', 'description' => '١٠٠ تەسبیحت بە مۆدی پەنجەمۆر ئەنجامدا.'],
                ]
            ],
            [
                'key' => 'fingerprint_1000_counts',
                'category_id' => $catMilestone->id,
                'icon' => '🚀',
                'condition_type' => Achievement::CONDITION_FINGERPRINT_TOTAL_COUNTS,
                'condition_value' => 1000,
                'reward_points' => 100,
                'translations' => [
                    'en' => ['name' => '1000 Fingerprint Counts', 'description' => 'Completed 1,000 total counts in Fingerprint Mode.'],
                    'ar' => ['name' => '١٠٠٠ تسبيحة بالبصمة', 'description' => 'أكملت ١٠٠٠ تسبيحة إجمالاً في وضع البصمة.'],
                    'ku' => ['name' => '١٠٠٠ تەسبیحی پەنجەمۆر', 'description' => '١٠٠٠ تەسبیحت بە مۆدی پەنجەمۆر ئەنجامدا.'],
                ]
            ],
            [
                'key' => 'fingerprint_10_sessions',
                'category_id' => $catSession->id,
                'icon' => '🔥',
                'condition_type' => Achievement::CONDITION_FINGERPRINT_TOTAL_SESSIONS,
                'condition_value' => 10,
                'reward_points' => 50,
                'translations' => [
                    'en' => ['name' => '10 Fingerprint Sessions', 'description' => 'Completed 10 sessions using Fingerprint Counter Mode.'],
                    'ar' => ['name' => '١٠ جلسات بصمة', 'description' => 'أكملت ١٠ جلسات باستخدام وضع عداد البصمة.'],
                    'ku' => ['name' => '١٠ سێشنی پەنجەمۆر', 'description' => '١٠ سێشنت بە بەکارهێنانی مۆدی پەنجەمۆر تەواو کرد.'],
                ]
            ],
            [
                'key' => 'fingerprint_100_sessions',
                'category_id' => $catSession->id,
                'icon' => '👑',
                'condition_type' => Achievement::CONDITION_FINGERPRINT_TOTAL_SESSIONS,
                'condition_value' => 100,
                'reward_points' => 250,
                'translations' => [
                    'en' => ['name' => '100 Fingerprint Sessions', 'description' => 'Completed 100 sessions using Fingerprint Counter Mode.'],
                    'ar' => ['name' => '١٠٠ جلسة بصمة', 'description' => 'أكملت ١٠٠ جلسة باستخدام وضع عداد البصمة.'],
                    'ku' => ['name' => '١٠٠ سێشنی پەنجەمۆر', 'description' => '١٠٠ سێشنت بە بەکارهێنانی مۆدی پەنجەمۆر تەواو کرد.'],
                ]
            ],
            [
                'key' => 'fingerprint_blind_master',
                'category_id' => $catSession->id,
                'icon' => '🕶️',
                'condition_type' => Achievement::CONDITION_FINGERPRINT_BLIND_SESSIONS,
                'condition_value' => 10,
                'reward_points' => 100,
                'translations' => [
                    'en' => ['name' => 'Blind Mode Master', 'description' => 'Completed 10 fingerprint sessions in Blind Mode.'],
                    'ar' => ['name' => 'سيد الوضع الأعمى', 'description' => 'أكملت ١٠ جلسات بصمة في الوضع المغلق (الأعمى).'],
                    'ku' => ['name' => 'شارەزای مۆدی کوێر', 'description' => '١٠ سێشنی مۆدی پەنجەمۆرت بە مۆدی کوێر (بێ بینین) تەواو کرد.'],
                ]
            ],
            [
                'key' => 'fingerprint_focus_master',
                'category_id' => $catSession->id,
                'icon' => '🎯',
                'condition_type' => Achievement::CONDITION_FINGERPRINT_FOCUS_SESSIONS,
                'condition_value' => 10,
                'reward_points' => 100,
                'translations' => [
                    'en' => ['name' => 'Focus Mode Master', 'description' => 'Completed 10 fingerprint sessions in Focus Mode.'],
                    'ar' => ['name' => 'سيد وضع التركيز', 'description' => 'أكملت ١٠ جلسات بصمة في وضع التركيز الكامل.'],
                    'ku' => ['name' => 'شارەزای مۆدی سەرنجدان', 'description' => '١٠ سێشنی مۆدی پەنجەمۆرت بە مۆدی سەرنجدان تەواو کرد.'],
                ]
            ],
        ];

        foreach ($achievements as $data) {
            $achievement = Achievement::updateOrCreate(
                ['key' => $data['key']],
                [
                    'category_id' => $data['category_id'],
                    'icon' => $data['icon'],
                    'condition_type' => $data['condition_type'],
                    'condition_value' => $data['condition_value'],
                    'reward_type' => 'POINTS',
                    'reward_points' => $data['reward_points'],
                    'version' => 1,
                    'is_hidden' => false,
                    'is_active' => true,
                    'sort_order' => 50,
                ]
            );

            // Seed translations dynamically using standard relation
            foreach ($data['translations'] as $locale => $t) {
                $achievement->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name' => $t['name'],
                        'description' => $t['description'],
                    ]
                );
            }
        }
    }
}
