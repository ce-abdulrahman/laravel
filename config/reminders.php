<?php

/**
 * config/reminders.php
 *
 * Central configuration for the Smart Reminder System.
 * Seeder reads from this file — add new reminder types here without code changes.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Reminder Times (user local timezone)
    |--------------------------------------------------------------------------
    */
    'default_times' => [
        'MORNING'      => '08:00',
        'AFTERNOON'    => '13:00',
        'EVENING'      => '18:00',
        'BEFORE_SLEEP' => '22:00',
        'DAILY_GOAL'   => '20:00',
        'STREAK'       => '21:00',
        'ACHIEVEMENT'  => null,    // Triggered by event, not schedule
        'INACTIVITY'   => '18:00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Snooze Options (in minutes)
    |--------------------------------------------------------------------------
    */
    'snooze_options' => [10, 30, 60],

    /*
    |--------------------------------------------------------------------------
    | Inactivity Threshold
    |--------------------------------------------------------------------------
    | Number of days of inactivity before sending inactivity reminder.
    */
    'inactivity_days' => 3,

    /*
    |--------------------------------------------------------------------------
    | Template Definitions (Seeder reads from here)
    |--------------------------------------------------------------------------
    | Each entry creates one ReminderTemplate with translations.
    */
    'templates' => [
        [
            'key'        => 'morning_reminder',
            'type'       => 'MORNING',
            'icon'       => '🌅',
            'priority'   => 1,
            'sort_order' => 10,
            'version'    => 1,
            'is_active'  => true,
            'metadata'   => ['color' => '#f59e0b', 'sound' => 'default'],
            'translations' => [
                'ku' => [
                    'title' => 'بەیانیت بەخێر 🌅',
                    'body'  => 'کاتی تەسبیح گفتنەوەیە، ئەمڕۆ بە یادی خودا دەست پێبکە',
                ],
                'ar' => [
                    'title' => 'صباح الخير 🌅',
                    'body'  => 'حان وقت الذكر، ابدأ يومك بذكر الله',
                ],
                'en' => [
                    'title' => 'Good Morning 🌅',
                    'body'  => 'Time for your morning dhikr, start your day with the remembrance of Allah',
                ],
            ],
        ],
        [
            'key'        => 'afternoon_reminder',
            'type'       => 'AFTERNOON',
            'icon'       => '☀️',
            'priority'   => 2,
            'sort_order' => 20,
            'version'    => 1,
            'is_active'  => true,
            'metadata'   => ['color' => '#f97316', 'sound' => 'default'],
            'translations' => [
                'ku' => [
                    'title' => 'نیوەڕۆ ☀️',
                    'body'  => 'یەک خولەکت بگرە، تەسبیح بگو و ئارامی بدۆزەرەوە',
                ],
                'ar' => [
                    'title' => 'تذكير الظهر ☀️',
                    'body'  => 'خذ دقيقة، اذكر الله وأرِح قلبك',
                ],
                'en' => [
                    'title' => 'Afternoon Reminder ☀️',
                    'body'  => 'Take a moment, do your dhikr and find peace',
                ],
            ],
        ],
        [
            'key'        => 'evening_reminder',
            'type'       => 'EVENING',
            'icon'       => '🌆',
            'priority'   => 3,
            'sort_order' => 30,
            'version'    => 1,
            'is_active'  => true,
            'metadata'   => ['color' => '#8b5cf6', 'sound' => 'default'],
            'translations' => [
                'ku' => [
                    'title' => 'ئێوارە 🌆',
                    'body'  => 'کاتی تەسبیحی ئێوارەیە، ئەمڕۆت چۆن بووە؟',
                ],
                'ar' => [
                    'title' => 'تذكير المساء 🌆',
                    'body'  => 'حان وقت أذكار المساء، كيف كان يومك؟',
                ],
                'en' => [
                    'title' => 'Evening Reminder 🌆',
                    'body'  => 'Time for your evening dhikr, how was your day?',
                ],
            ],
        ],
        [
            'key'        => 'before_sleep_reminder',
            'type'       => 'BEFORE_SLEEP',
            'icon'       => '🌙',
            'priority'   => 4,
            'sort_order' => 40,
            'version'    => 1,
            'is_active'  => true,
            'metadata'   => ['color' => '#1e40af', 'sound' => 'soft'],
            'translations' => [
                'ku' => [
                    'title' => 'پێش خەوتن 🌙',
                    'body'  => 'ئەمشەو بە تەسبیح خەوبکە، شەوت خۆش بێت',
                ],
                'ar' => [
                    'title' => 'قبل النوم 🌙',
                    'body'  => 'اختم يومك بذكر الله، تصبح على خير',
                ],
                'en' => [
                    'title' => 'Before Sleep 🌙',
                    'body'  => 'End your day with dhikr, good night',
                ],
            ],
        ],
        [
            'key'        => 'daily_goal_reminder',
            'type'       => 'DAILY_GOAL',
            'icon'       => '🎯',
            'priority'   => 5,
            'sort_order' => 50,
            'version'    => 1,
            'is_active'  => true,
            'metadata'   => ['color' => '#16a34a', 'sound' => 'default'],
            'translations' => [
                'ku' => [
                    'title' => 'ئامانجی ئەمڕۆ 🎯',
                    'body'  => 'تەواوکردنی ئامانجی ئەمڕۆت نزیکتر بوو، بەردەوام بە!',
                ],
                'ar' => [
                    'title' => 'هدف اليوم 🎯',
                    'body'  => 'اقتربت من إتمام هدف اليوم، استمر!',
                ],
                'en' => [
                    'title' => 'Daily Goal 🎯',
                    'body'  => 'You are close to completing today\'s goal, keep going!',
                ],
            ],
        ],
        [
            'key'        => 'streak_reminder',
            'type'       => 'STREAK',
            'icon'       => '🔥',
            'priority'   => 6,
            'sort_order' => 60,
            'version'    => 1,
            'is_active'  => true,
            'metadata'   => ['color' => '#dc2626', 'sound' => 'alert'],
            'translations' => [
                'ku' => [
                    'title' => '🔥 زنجیرەکەت لەتەهلوکەداە!',
                    'body'  => 'ئەمڕۆ تەسبیح نەگوتووتە، زنجیرەکەت لادەبات!',
                ],
                'ar' => [
                    'title' => '🔥 سلسلتك في خطر!',
                    'body'  => 'لم تذكر الله اليوم، لا تكسر سلسلتك!',
                ],
                'en' => [
                    'title' => '🔥 Your Streak is at Risk!',
                    'body'  => 'You haven\'t done your dhikr today, don\'t break your streak!',
                ],
            ],
        ],
        [
            'key'        => 'achievement_reminder',
            'type'       => 'ACHIEVEMENT',
            'icon'       => '🏆',
            'priority'   => 7,
            'sort_order' => 70,
            'version'    => 1,
            'is_active'  => true,
            'metadata'   => ['color' => '#ca8a04', 'sound' => 'success'],
            'translations' => [
                'ku' => [
                    'title' => '🏆 نزیکی دەستکەوتنی نوێیت!',
                    'body'  => 'تەنها چەند تەسبیچ ماوەتە تا کردنەوەی دەستکەوتنی نوێ!',
                ],
                'ar' => [
                    'title' => '🏆 على وشك إنجاز جديد!',
                    'body'  => 'أنت قريب جداً من فتح إنجاز جديد!',
                ],
                'en' => [
                    'title' => '🏆 Almost There!',
                    'body'  => 'You are so close to unlocking a new achievement!',
                ],
            ],
        ],
        [
            'key'        => 'inactivity_reminder',
            'type'       => 'INACTIVITY',
            'icon'       => '💤',
            'priority'   => 8,
            'sort_order' => 80,
            'version'    => 1,
            'is_active'  => true,
            'metadata'   => ['color' => '#6b7280', 'sound' => 'default'],
            'translations' => [
                'ku' => [
                    'title' => 'دەتمانویستنووتە 💤',
                    'body'  => 'چەند ڕۆژە تەسبیحت نەگوتووە، گەڕانەوە بۆ گەشتی تەسبیح',
                ],
                'ar' => [
                    'title' => 'اشتقنا إليك 💤',
                    'body'  => 'مضت أيام دون ذكر، عد إلى رحلتك الروحية',
                ],
                'en' => [
                    'title' => 'We Miss You 💤',
                    'body'  => 'It\'s been a few days, return to your dhikr journey',
                ],
            ],
        ],
    ],

];
