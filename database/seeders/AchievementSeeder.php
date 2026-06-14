<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\AchievementCategory;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Categories ─────────────────────────────────────────────────────────

        $categories = [
            ['icon' => '📿', 'sort_order' => 1, 'translations' => [
                'ku' => 'مایلستۆنی تەسبیح',
                'ar' => 'إنجازات التسبيح',
                'en' => 'Tasbih Milestones',
            ]],
            ['icon' => '🔥', 'sort_order' => 2, 'translations' => [
                'ku' => 'دەستکەوتەکانی ستریک',
                'ar' => 'إنجازات السلاسل',
                'en' => 'Streak Achievements',
            ]],
            ['icon' => '🎯', 'sort_order' => 3, 'translations' => [
                'ku' => 'دەستکەوتەکانی ئامانج',
                'ar' => 'إنجازات الأهداف',
                'en' => 'Daily Goal Achievements',
            ]],
            ['icon' => '⚡', 'sort_order' => 4, 'translations' => [
                'ku' => 'دەستکەوتەکانی سێشن',
                'ar' => 'إنجازات الجلسات',
                'en' => 'Session Achievements',
            ]],
            ['icon' => '📅', 'sort_order' => 5, 'translations' => [
                'ku' => 'دەستکەوتەکانی بەردەوامی',
                'ar' => 'إنجازات الاتساق',
                'en' => 'Consistency Achievements',
            ]],
            ['icon' => '🌟', 'sort_order' => 6, 'translations' => [
                'ku' => 'دەستکەوتەکانی تایبەت',
                'ar' => 'إنجازات خاصة',
                'en' => 'Special Achievements',
            ]],
            ['icon' => '🔮', 'sort_order' => 7, 'translations' => [
                'ku' => 'دەستکەوتەی نهێنی',
                'ar' => 'إنجازات مخفية',
                'en' => 'Hidden Achievements',
            ]],
        ];

        $categoryModels = [];
        foreach ($categories as $catData) {
            $cat = AchievementCategory::updateOrCreate(
                ['sort_order' => $catData['sort_order']],
                [
                    'icon'       => $catData['icon'],
                    'is_active'  => true,
                ]
            );
            foreach ($catData['translations'] as $locale => $name) {
                $cat->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['name' => $name]
                );
            }
            $categoryModels[] = $cat;
        }

        [$catMilestone, $catStreak, $catGoal, $catSession, $catConsistency, $catSpecial, $catHidden] = $categoryModels;

        // ── 2. Helper: create achievement ──────────────────────────────────────────

        $create = function (array $data) {
            $achievement = Achievement::updateOrCreate(
                ['key' => $data['key']],
                [
                    'category_id'     => $data['category_id'],
                    'icon'            => $data['icon'],
                    'condition_type'  => $data['condition_type'],
                    'condition_value' => $data['condition_value'],
                    'condition_meta'  => $data['condition_meta'] ?? null,
                    'reward_type'     => $data['reward_type'] ?? 'POINTS',
                    'reward_points'   => $data['reward_points'] ?? 0,
                    'reward_value'    => $data['reward_value'] ?? null,
                    'version'         => 1,
                    'is_hidden'       => $data['is_hidden'] ?? false,
                    'is_active'       => true,
                    'sort_order'      => $data['sort_order'] ?? 0,
                ]
            );
            foreach ($data['translations'] as $locale => $t) {
                $achievement->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name'        => $t['name'],
                        'description' => $t['description'] ?? null,
                    ]
                );
            }
        };

        // ── 3. Tasbih Milestones (TOTAL_DHIKR) ──────────────────────────────────

        $milestones = [
            ['key' => 'first_tasbih', 'icon' => '🌱', 'value' => 1, 'points' => 10, 'order' => 1,
             'ku' => ['name' => 'یەکەم تەسبیح', 'desc' => 'یەکەم دانەی تەسبیحت کرد'],
             'ar' => ['name' => 'أول تسبيحة', 'desc' => 'أكملت أول ذكر لك'],
             'en' => ['name' => 'First Tasbih', 'desc' => 'You completed your very first dhikr']],

            ['key' => 'beginner_worshipper', 'icon' => '🌿', 'value' => 100, 'points' => 25, 'order' => 2,
             'ku' => ['name' => 'سەرەتاوانی ئیبادەت', 'desc' => 'گەیشتیت بە ١٠٠ تەسبیح'],
             'ar' => ['name' => 'مبتدئ العبادة', 'desc' => 'وصلت إلى ١٠٠ تسبيحة'],
             'en' => ['name' => 'Beginner Worshipper', 'desc' => 'Reached 100 total dhikr']],

            ['key' => 'dedicated_worshipper', 'icon' => '🌳', 'value' => 1000, 'points' => 75, 'order' => 3,
             'ku' => ['name' => 'پابەندی ئیبادەت', 'desc' => 'گەیشتیت بە ١٠٠٠ تەسبیح'],
             'ar' => ['name' => 'المتعبد المخلص', 'desc' => 'وصلت إلى ١٠٠٠ تسبيحة'],
             'en' => ['name' => 'Dedicated Worshipper', 'desc' => 'Reached 1,000 total dhikr']],

            ['key' => 'advanced_worshipper', 'icon' => '⭐', 'value' => 10000, 'points' => 200, 'order' => 4,
             'ku' => ['name' => 'پێشکەوتووی ئیبادەت', 'desc' => 'گەیشتیت بە ١٠٠٠٠ تەسبیح'],
             'ar' => ['name' => 'المتعبد المتقدم', 'desc' => 'وصلت إلى ١٠٠٠٠ تسبيحة'],
             'en' => ['name' => 'Advanced Worshipper', 'desc' => 'Reached 10,000 total dhikr']],

            ['key' => 'master_of_dhikr', 'icon' => '👑', 'value' => 100000, 'points' => 1000, 'order' => 5,
             'ku' => ['name' => 'مامۆستای ذکر', 'desc' => 'گەیشتیت بە ١٠٠٠٠٠ تەسبیح'],
             'ar' => ['name' => 'سيد الذكر', 'desc' => 'وصلت إلى ١٠٠٠٠٠ تسبيحة'],
             'en' => ['name' => 'Master of Dhikr', 'desc' => 'Reached 100,000 total dhikr']],

            ['key' => 'dhikr_legend', 'icon' => '🌌', 'value' => 1000000, 'points' => 5000, 'order' => 6,
             'ku' => ['name' => 'ئەفسانەی ذکر', 'desc' => 'ملیۆن تەسبیح کردووە'],
             'ar' => ['name' => 'أسطورة الذكر', 'desc' => 'وصلت إلى مليون تسبيحة'],
             'en' => ['name' => 'Dhikr Legend', 'desc' => 'Reached 1,000,000 total dhikr']],
        ];

        foreach ($milestones as $m) {
            $create([
                'key' => $m['key'], 'category_id' => $catMilestone->id,
                'icon' => $m['icon'], 'condition_type' => 'TOTAL_DHIKR',
                'condition_value' => $m['value'], 'reward_points' => $m['points'],
                'sort_order' => $m['order'],
                'translations' => [
                    'ku' => ['name' => $m['ku']['name'], 'description' => $m['ku']['desc']],
                    'ar' => ['name' => $m['ar']['name'], 'description' => $m['ar']['desc']],
                    'en' => ['name' => $m['en']['name'], 'description' => $m['en']['desc']],
                ],
            ]);
        }

        // ── 4. Streak Achievements ───────────────────────────────────────────────

        $streaks = [
            ['key' => 'streak_3', 'icon' => '🔥', 'value' => 3, 'points' => 15, 'order' => 1,
             'ku' => ['name' => '٣ ڕۆژ ستریک', 'desc' => '٣ ڕۆژ بەردەوام بوویت'],
             'ar' => ['name' => 'سلسلة ٣ أيام', 'desc' => 'حافظت على التسبيح ٣ أيام متتالية'],
             'en' => ['name' => '3 Day Streak', 'desc' => '3 consecutive days of dhikr']],

            ['key' => 'streak_7', 'icon' => '🌟', 'value' => 7, 'points' => 50, 'order' => 2,
             'ku' => ['name' => '٧ ڕۆژ ستریک', 'desc' => 'هەفتەیەک بەردەوام بوویت'],
             'ar' => ['name' => 'سلسلة أسبوع', 'desc' => 'حافظت على التسبيح أسبوعاً كاملاً'],
             'en' => ['name' => '7 Day Streak', 'desc' => 'A full week of daily dhikr']],

            ['key' => 'streak_30', 'icon' => '🏆', 'value' => 30, 'points' => 200, 'order' => 3,
             'ku' => ['name' => '٣٠ ڕۆژ ستریک', 'desc' => 'مانگێک بەردەوام بوویت'],
             'ar' => ['name' => 'سلسلة شهر', 'desc' => 'حافظت على التسبيح شهراً كاملاً'],
             'en' => ['name' => '30 Day Streak', 'desc' => 'A full month of daily dhikr']],

            ['key' => 'streak_100', 'icon' => '💯', 'value' => 100, 'points' => 750, 'order' => 4,
             'ku' => ['name' => '١٠٠ ڕۆژ ستریک', 'desc' => '١٠٠ ڕۆژ بەردەوام بوویت'],
             'ar' => ['name' => 'سلسلة ١٠٠ يوم', 'desc' => 'حافظت على التسبيح ١٠٠ يوم متتالي'],
             'en' => ['name' => '100 Day Streak', 'desc' => '100 consecutive days of dhikr']],

            ['key' => 'streak_365', 'icon' => '🌈', 'value' => 365, 'points' => 5000, 'order' => 5,
             'ku' => ['name' => 'ساڵ ستریک', 'desc' => 'ساڵێک بەشێوەیەکی بەردەوام تەسبیح کردووە'],
             'ar' => ['name' => 'سلسلة سنة كاملة', 'desc' => 'حافظت على التسبيح لسنة كاملة'],
             'en' => ['name' => '365 Day Streak', 'desc' => 'A full year of unbroken daily dhikr']],
        ];

        foreach ($streaks as $s) {
            $create([
                'key' => $s['key'], 'category_id' => $catStreak->id,
                'icon' => $s['icon'], 'condition_type' => 'CURRENT_STREAK',
                'condition_value' => $s['value'], 'reward_points' => $s['points'],
                'sort_order' => $s['order'],
                'translations' => [
                    'ku' => ['name' => $s['ku']['name'], 'description' => $s['ku']['desc']],
                    'ar' => ['name' => $s['ar']['name'], 'description' => $s['ar']['desc']],
                    'en' => ['name' => $s['en']['name'], 'description' => $s['en']['desc']],
                ],
            ]);
        }

        // ── 5. Daily Goal Achievements ────────────────────────────────────────────

        $goals = [
            ['key' => 'first_goal', 'icon' => '🎯', 'value' => 1, 'points' => 20, 'order' => 1,
             'ku' => ['name' => 'یەکەم ئامانج', 'desc' => 'یەکەم ئامانجی ڕۆژانەت تەواو کرد'],
             'ar' => ['name' => 'أول هدف', 'desc' => 'أكملت هدفك اليومي لأول مرة'],
             'en' => ['name' => 'First Goal Completed', 'desc' => 'Completed your first daily goal']],

            ['key' => 'goals_10', 'icon' => '✅', 'value' => 10, 'points' => 60, 'order' => 2,
             'ku' => ['name' => '١٠ ئامانج', 'desc' => '١٠ ئامانجی ڕۆژانەت تەواو کرد'],
             'ar' => ['name' => '١٠ أهداف', 'desc' => 'أكملت ١٠ أهداف يومية'],
             'en' => ['name' => '10 Goals Completed', 'desc' => 'Completed 10 daily goals']],

            ['key' => 'goals_50', 'icon' => '🌙', 'value' => 50, 'points' => 250, 'order' => 3,
             'ku' => ['name' => '٥٠ ئامانج', 'desc' => '٥٠ ئامانجی ڕۆژانەت تەواو کرد'],
             'ar' => ['name' => '٥٠ هدفاً', 'desc' => 'أكملت ٥٠ هدفاً يومياً'],
             'en' => ['name' => '50 Goals Completed', 'desc' => 'Completed 50 daily goals']],

            ['key' => 'goals_100', 'icon' => '💎', 'value' => 100, 'points' => 500, 'order' => 4,
             'ku' => ['name' => '١٠٠ ئامانج', 'desc' => '١٠٠ ئامانجی ڕۆژانەت تەواو کرد'],
             'ar' => ['name' => '١٠٠ هدف', 'desc' => 'أكملت ١٠٠ هدف يومي'],
             'en' => ['name' => '100 Goals Completed', 'desc' => 'Completed 100 daily goals']],

            ['key' => 'goals_365', 'icon' => '🌟', 'value' => 365, 'points' => 2000, 'order' => 5,
             'ku' => ['name' => '٣٦٥ ئامانج', 'desc' => 'ساڵێک ئامانجەکانت پڕ کردووە'],
             'ar' => ['name' => '٣٦٥ هدفاً', 'desc' => 'أكملت أهدافك اليومية لمدة سنة'],
             'en' => ['name' => '365 Goals Completed', 'desc' => 'Completed a goal every day for a year']],
        ];

        foreach ($goals as $g) {
            $create([
                'key' => $g['key'], 'category_id' => $catGoal->id,
                'icon' => $g['icon'], 'condition_type' => 'GOALS_COMPLETED',
                'condition_value' => $g['value'], 'reward_points' => $g['points'],
                'sort_order' => $g['order'],
                'translations' => [
                    'ku' => ['name' => $g['ku']['name'], 'description' => $g['ku']['desc']],
                    'ar' => ['name' => $g['ar']['name'], 'description' => $g['ar']['desc']],
                    'en' => ['name' => $g['en']['name'], 'description' => $g['en']['desc']],
                ],
            ]);
        }

        // ── 6. Session Achievements ───────────────────────────────────────────────

        $sessions = [
            ['key' => 'session_100', 'icon' => '⚡', 'value' => 100, 'points' => 30, 'order' => 1,
             'ku' => ['name' => '١٠٠ تەسبیح لە سێشنێکدا', 'desc' => '١٠٠ تەسبیح لە سێشنێکی تەکدا کردووە'],
             'ar' => ['name' => '١٠٠ تسبيحة في جلسة', 'desc' => 'أكملت ١٠٠ تسبيحة في جلسة واحدة'],
             'en' => ['name' => '100 Dhikr In One Session', 'desc' => '100 dhikr in a single session']],

            ['key' => 'session_500', 'icon' => '🌊', 'value' => 500, 'points' => 100, 'order' => 2,
             'ku' => ['name' => '٥٠٠ تەسبیح لە سێشنێکدا', 'desc' => '٥٠٠ تەسبیح لە سێشنێکی تەکدا کردووە'],
             'ar' => ['name' => '٥٠٠ تسبيحة في جلسة', 'desc' => 'أكملت ٥٠٠ تسبيحة في جلسة واحدة'],
             'en' => ['name' => '500 Dhikr In One Session', 'desc' => '500 dhikr in a single session']],

            ['key' => 'session_1000', 'icon' => '🔱', 'value' => 1000, 'points' => 300, 'order' => 3,
             'ku' => ['name' => '١٠٠٠ تەسبیح لە سێشنێکدا', 'desc' => '١٠٠٠ تەسبیح لە سێشنێکی تەکدا کردووە'],
             'ar' => ['name' => '١٠٠٠ تسبيحة في جلسة', 'desc' => 'أكملت ١٠٠٠ تسبيحة في جلسة واحدة'],
             'en' => ['name' => '1000 Dhikr In One Session', 'desc' => '1000 dhikr in a single session']],
        ];

        foreach ($sessions as $s) {
            $create([
                'key' => $s['key'], 'category_id' => $catSession->id,
                'icon' => $s['icon'], 'condition_type' => 'SESSION_DHIKR_COUNT',
                'condition_value' => $s['value'], 'reward_points' => $s['points'],
                'sort_order' => $s['order'],
                'translations' => [
                    'ku' => ['name' => $s['ku']['name'], 'description' => $s['ku']['desc']],
                    'ar' => ['name' => $s['ar']['name'], 'description' => $s['ar']['desc']],
                    'en' => ['name' => $s['en']['name'], 'description' => $s['en']['desc']],
                ],
            ]);
        }

        // ── 7. Consistency Achievements ───────────────────────────────────────────

        $consistency = [
            ['key' => 'consistent_7', 'icon' => '📅', 'value' => 7, 'points' => 50, 'order' => 1,
             'ku' => ['name' => 'هەفتەی بەردەوام', 'desc' => '٧ ڕۆژ بەردەوام چالاک بوویت'],
             'ar' => ['name' => 'أسبوع منتظم', 'desc' => 'نشطت ٧ أيام متتالية'],
             'en' => ['name' => 'Active Every Day For 7 Days', 'desc' => 'Active every day for a week']],

            ['key' => 'consistent_30', 'icon' => '🗓️', 'value' => 30, 'points' => 200, 'order' => 2,
             'ku' => ['name' => 'مانگی بەردەوام', 'desc' => '٣٠ ڕۆژ بەردەوام چالاک بوویت'],
             'ar' => ['name' => 'شهر منتظم', 'desc' => 'نشطت ٣٠ يوماً متتالياً'],
             'en' => ['name' => 'Active Every Day For 30 Days', 'desc' => 'Active every day for a month']],

            ['key' => 'consistent_90', 'icon' => '⏱️', 'value' => 90, 'points' => 750, 'order' => 3,
             'ku' => ['name' => '٩٠ ڕۆژ بەردەوام', 'desc' => '٩٠ ڕۆژ بەردەوام چالاک بوویت'],
             'ar' => ['name' => '٩٠ يوماً منتظماً', 'desc' => 'نشطت ٩٠ يوماً متتالياً'],
             'en' => ['name' => 'Active Every Day For 90 Days', 'desc' => 'Active every day for 90 days']],
        ];

        foreach ($consistency as $c) {
            $create([
                'key' => $c['key'], 'category_id' => $catConsistency->id,
                'icon' => $c['icon'], 'condition_type' => 'CONSECUTIVE_DAYS',
                'condition_value' => $c['value'], 'reward_points' => $c['points'],
                'sort_order' => $c['order'],
                'translations' => [
                    'ku' => ['name' => $c['ku']['name'], 'description' => $c['ku']['desc']],
                    'ar' => ['name' => $c['ar']['name'], 'description' => $c['ar']['desc']],
                    'en' => ['name' => $c['en']['name'], 'description' => $c['en']['desc']],
                ],
            ]);
        }

        // ── 8. Hidden Achievements ────────────────────────────────────────────────

        // Midnight Worshipper: dhikr between 00:00–03:00 Baghdad time
        $create([
            'key' => 'midnight_worshipper', 'category_id' => $catHidden->id,
            'icon' => '🌙', 'condition_type' => 'SPECIAL_EVENT',
            'condition_value' => 1,
            'condition_meta' => ['hour_start' => 0, 'hour_end' => 3],
            'reward_points' => 100, 'is_hidden' => true, 'sort_order' => 1,
            'translations' => [
                'ku' => ['name' => 'ئیبادەتکاری نیوەشەو', 'description' => 'لە نێوان ١٢ شەو و ٣ بەیانی تەسبیح کردووە'],
                'ar' => ['name' => 'عابد منتصف الليل', 'description' => 'ذكرت الله بين منتصف الليل والفجر'],
                'en' => ['name' => 'Midnight Worshipper', 'description' => 'Performed dhikr between midnight and 3am'],
            ],
        ]);

        // Early Bird: dhikr before 05:00 Baghdad
        $create([
            'key' => 'early_bird', 'category_id' => $catHidden->id,
            'icon' => '🌅', 'condition_type' => 'SPECIAL_EVENT',
            'condition_value' => 1,
            'condition_meta' => ['hour_start' => 3, 'hour_end' => 5],
            'reward_points' => 75, 'is_hidden' => true, 'sort_order' => 2,
            'translations' => [
                'ku' => ['name' => 'پێشتر خستەر', 'description' => 'پێش ڕووناکبوونی ئەسمان تەسبیح کردووە'],
                'ar' => ['name' => 'الباكر', 'description' => 'ذكرت الله قبل الفجر'],
                'en' => ['name' => 'Early Bird', 'description' => 'Performed dhikr before dawn'],
            ],
        ]);

        // Marathon: 5000 dhikr in one session
        $create([
            'key' => 'marathon_session', 'category_id' => $catHidden->id,
            'icon' => '🏃', 'condition_type' => 'SESSION_DHIKR_COUNT',
            'condition_value' => 5000,
            'reward_points' => 500, 'is_hidden' => true, 'sort_order' => 3,
            'translations' => [
                'ku' => ['name' => 'ماراتۆنی تەسبیح', 'description' => '٥٠٠٠ تەسبیح لە سێشنێکی تەکدا'],
                'ar' => ['name' => 'ماراثون التسبيح', 'description' => '٥٠٠٠ تسبيحة في جلسة واحدة'],
                'en' => ['name' => 'Dhikr Marathon', 'description' => '5000 dhikr in a single session'],
            ],
        ]);

        // Devoted: longest streak >= 365
        $create([
            'key' => 'devoted', 'category_id' => $catHidden->id,
            'icon' => '❤️', 'condition_type' => 'LONGEST_STREAK',
            'condition_value' => 365,
            'reward_points' => 10000, 'is_hidden' => true, 'sort_order' => 4,
            'translations' => [
                'ku' => ['name' => 'پابەند', 'description' => 'درێژترین ستریکت بەرز بوو بۆ ساڵێک'],
                'ar' => ['name' => 'المخلص', 'description' => 'أطول سلسلة لك بلغت سنة كاملة'],
                'en' => ['name' => 'Devoted', 'description' => 'Your longest streak reached a full year'],
            ],
        ]);

        $this->command->info('✅ AchievementSeeder: ' . Achievement::count() . ' achievements created across ' . AchievementCategory::count() . ' categories.');
    }
}
