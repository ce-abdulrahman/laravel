<?php
// resources/lang/ku/achievements.php

return [
    'titles' => [
        'index'              => 'بەڕێوەبردنی دەستکەوتەکان',
        'achievements'       => 'هەموو دەستکەوتەکان',
        'create'             => 'دەستکەوتەی نوێ',
        'edit'               => 'دەستکاریکردنی دەستکەوتە',
        'categories'         => 'کەتێگۆریەکانی دەستکەوتە',
        'category_create'    => 'کەتێگۆری نوێ',
        'category_edit'      => 'دەستکاریکردنی کەتێگۆری',
        'user_achievements'  => 'دەستکەوتەی بەکارهێنەران',
        'analytics'          => 'ئەنالیزی دەستکەوتەکان',
    ],

    'hints' => [
        'index'             => 'بەڕێوەبردن و دروستکردنی دەستکەوتەکانی سیستەم.',
        'create'            => 'زانیاری دەستکەوتەکەی نوێ پڕ بکەرەوە.',
        'edit'              => 'دەستکاری زانیارییەکانی ئەم دەستکەوتەیە بکە.',
        'categories'        => 'بەڕێوەبردنی کەتێگۆریەکانی دەستکەوتە.',
        'user_achievements' => 'ئەنالیز و بەڕێوەبردنی دەستکەوتەی هەموو بەکارهێنەران.',
    ],

    'fields' => [
        'icon'            => 'ئایکۆن',
        'key'             => 'کلیل',
        'category'        => 'کەتێگۆری',
        'sort_order'      => 'ڕیزبەندی',
        'condition_type'  => 'جۆری مەرج',
        'condition_value' => 'ئامانجی مەرج',
        'version'         => 'وەشان',
        'reward_type'     => 'جۆری خەڵات',
        'reward_points'   => 'پوائنتی خەڵات',
        'is_active'       => 'چالاک',
        'is_hidden'       => 'نهێنی',
        'name'            => 'ناو',
        'description'     => 'وصف',
    ],

    'sections' => [
        'basic'        => 'زانیاری سەرەکی',
        'condition'    => 'مەرجی دەستکەوتن',
        'reward'       => 'خەڵات',
        'translations' => 'وەرگێڕانەکان',
        'options'      => 'ئۆپشنەکان',
    ],

    'actions' => [
        'create'        => 'دەستکەوتەی نوێ',
        'edit'          => 'دەستکاری',
        'delete'        => 'سڕینەوە',
        'save'          => 'تۆمارکردن',
        'update'        => 'پاشەکەوتکردن',
        'back'          => 'گەڕانەوە',
        'cancel'        => 'پاشگەزبوونەوە',
        'refresh'       => 'تازەکردنەوە',
        'view_cats'     => 'کەتێگۆریەکان',
        'view_users'    => 'دەستکەوتەی بەکارهێنەران',
        'view_analytics'=> 'ئەنالیز',
        'grant'         => 'داستین',
        'revoke'        => 'لادەبردن',
        'reset'         => 'ڕێکخستنەوە',
    ],

    'condition_types' => [
        'TOTAL_DHIKR'         => 'کۆی تەسبیح',
        'CURRENT_STREAK'      => 'زنجیرەی ئێستا (ڕۆژ)',
        'LONGEST_STREAK'      => 'درێژترین زنجیرە (ڕۆژ)',
        'GOALS_COMPLETED'     => 'ئامانجە تەواوکراوەکان',
        'SESSION_DHIKR_COUNT' => 'تەسبیح لە یەک دانیشتنەکدا',
        'CONSECUTIVE_DAYS'    => 'ڕۆژی بەردەوام',
        'SPECIAL_EVENT'       => 'ڕووداوی تایبەت',
        'CUSTOM_RULE'         => 'یاسای دیاریکراو',
    ],

    'reward_types' => [
        'POINTS'        => 'پوائنت',
        'BADGE'         => 'نیشانە',
        'TITLE'         => 'ناونیشان',
        'SPECIAL_THEME' => 'تایبیت',
        'FUTURE_REWARD' => 'خەڵاتی داهاتوو',
    ],

    'status' => [
        'active'    => 'چالاک',
        'inactive'  => 'ناچالاک',
        'hidden'    => 'نهێنی',
        'completed' => 'ئەنجامدراو',
        'in_progress'=> 'لەسەر ڕێگا',
    ],

    'messages' => [
        'created'        => 'دەستکەوتەکە بە سەرکەوتوویی دروستکرا.',
        'updated'        => 'دەستکەوتەکە بە سەرکەوتوویی نوێکرایەوە.',
        'deleted'        => 'دەستکەوتەکە بە سەرکەوتوویی سڕایەوە.',
        'granted'        => 'دەستکەوتەکە بە سەرکەوتوویی بە بەکارهێنەر دراوە.',
        'revoked'        => 'دەستکەوتەکە لادرا.',
        'reset'          => 'پێشکەوتنەکە ڕێکخستەوە.',
        'confirm_delete' => 'دڵنیایت لە سڕینەوەی ئەم دەستکەوتەیە؟',
        'confirm_revoke' => 'دڵنیایت لە لادەبردنی دەستکەوتەکە لە بەکارهێنەر؟',
    ],

    'table' => [
        'number'      => 'ز',
        'achievement' => 'دەستکەوتە',
        'category'    => 'کەتێگۆری',
        'condition'   => 'مەرج',
        'target'      => 'ئامانج',
        'reward'      => 'خەڵات',
        'status'      => 'دۆخ',
        'actions'     => 'کردارەکان',
        'user'        => 'بەکارهێنەر',
        'progress'    => 'پێشکەوتن',
        'unlocked_at' => 'کاتی ئەنجامدان',
    ],

    'stats' => [
        'total'           => 'کۆی دەستکەوتە',
        'active'          => 'چالاک',
        'hidden'          => 'نهێنی',
        'categories'      => 'کەتێگۆری',
        'total_unlocks'   => 'کۆی ئەنجامدان',
        'active_users'    => 'بەکارهێنەرانی چالاک',
        'avg_completion'  => 'ناوەندی تەواوکردن',
        'today_unlocks'   => 'ئەمڕۆ',
        'top_achievements'=> 'دەستکەوتەی زۆرتر ئەنجامدراو',
        'top_users'       => 'بەکارهێنەرانی سەرەوە',
        'by_category'     => 'ئەنجامدان بەپێی کەتێگۆری',
    ],

    'pagination' => [
        'showing' => 'پیشاندانی',
        'to'      => 'بۆ',
        'of'      => 'لە',
        'entries' => 'دەستکەوتە',
        'total'   => 'کۆی گشتی:',
    ],

    'empty' => [
        'achievements' => 'هیچ دەستکەوتەیەک نەدۆزرایەوە',
        'categories'   => 'هیچ کەتێگۆریەک نەدۆزرایەوە',
        'users'        => 'هیچ ئەنجامدانێک نەدۆزرایەوە',
    ],

    'placeholders' => [
        'search'      => 'گەڕان بە ناو، کلیل...',
        'search_user' => 'گەڕان بە ناوی بەکارهێنەر...',
        'key'         => 'first_tasbih',
        'icon'        => '🏆',
    ],
];
