<?php
// resources/lang/ku/memorization_plans.php

return [
    'titles' => [
        'index' => 'پلانەکانی لەبەرکردن',
        'create' => 'دروستکردنی پلانی نوێ',
        'edit' => 'دەستکاریکردنی پلان',
        'show' => 'وردەکاریی پلان',
    ],

    'actions' => [
        'create' => 'دروستکردنی پلان',
        'create_first' => 'یەکەم پلان دروست بکە',
        'back' => 'گەڕانەوە',
        'mark_complete' => 'تەواو بوو',
    ],

    'sections' => [
        'basic_settings' => 'ڕێکخستنە بنەڕەتییەکان',
        'plan_content' => 'ناوەڕۆکی پلان',
    ],

    'fields' => [
        'title' => 'ناونیشان',
        'plan_type' => 'جۆری پلان',
        'start_date' => 'ڕێکەوتی دەستپێکردن',
        'target_end_date' => 'ڕێکەوتی کۆتایی',
        'daily_target' => 'ئامانژی ڕۆژانە',
        'notes' => 'تێبینییەکان',
        'day' => 'ڕۆژ',
        'surah_ayah' => 'سورەت و ئایەت',
        'target_date' => 'ڕێکەوتی ئامانج',
        'status' => 'دۆخ',
    ],

    'placeholders' => [
        'notes' => 'تێبینییەکانت لەسەر ئەم پلانە...',
    ],

    'plan_types' => [
        'juz' => 'جوز',
        'surah' => 'سورەت',
        'custom' => 'تایبەت',
    ],

    'target_types' => [
        'ayahs' => 'ئایەت',
        'pages' => 'لاپەڕە',
        'juz' => 'جوز',
        'hizb' => 'حیزب',
    ],

    'statuses' => [
        'active' => 'چالاک',
        'paused' => 'وەستاوە',
        'completed' => 'تەواو بوو',
        'pending' => 'چاوەڕوان',
        'skipped' => 'پەڕێندراو',
    ],

    'total_plans' => 'کۆی پلانەکان',
    'active_plans' => 'پلانە چالاکەکان',
    'completed_plans' => 'پلانە تەواوکراوەکان',
    'total_items' => 'کۆی بڕگەکان',
    'days' => 'ڕۆژ',

    'filter_by_status' => 'فلتەر بەپێی دۆخ',
    'filter_by_type' => 'فلتەر بەپێی جۆر',
    'all_statuses' => 'هەموو دۆخەکان',
    'all_types' => 'هەموو جۆرەکان',

    'select_type' => 'جۆری پلان هەڵبژێرە',
    'select_surah' => 'سورەت هەڵبژێرە',
    'select_juz' => 'جوز هەڵبژێرە',
    'choose_surah' => 'سورەتێک هەڵبژێرە',
    'choose_juz' => 'جوزێک هەڵبژێرە',
    'juz' => 'جوز',
    'per_day' => 'لە ڕۆژێکدا',
    'to' => 'تا',

    'overall_progress' => 'پێشکەوتنی گشتی',
    'completed_days' => 'ڕۆژە تەواوکراوەکان',
    'pending_days' => 'ڕۆژە چاوەڕوانەکان',
    'total_ayahs' => 'کۆی ئایەتەکان',
    'today_task' => 'ئەرکی ئەمڕۆ',
    'completed' => 'تەواو بوو',
    'overdue' => 'دواکەوتوو',
    'plan_schedule' => 'خشتەی پلان',
    'started' => 'دەستی پێکردووە',
    'target_end' => 'کۆتایی ئامانج',
    'next_target' => 'ئامانژی داهاتوو',

    'no_plans' => 'هیچ پلانێک نییە',
    'no_plans_message' => 'تۆ هێشتا هیچ پلانێکی لەبەرکردنت دروست نەکردووە.',

    'messages' => [
        'created' => 'پلان بە سەرکەوتوویی دروست کرا',
        'updated' => 'پلان بە سەرکەوتوویی نوێکرایەوە',
        'deleted' => 'پلان بە سەرکەوتوویی سڕایەوە',
        'item_updated' => 'دۆخی بڕگەکە نوێکرایەوە',
        'confirm_delete' => 'دڵنیایت کە دەتەوێت ئەم پلانە بسڕیتەوە؟',
    ],

    'hints' => [
        'my_plans' => 'پلانەکانی لەبەرکردنی قورئان',
        'manage_all_plans' => 'بەڕێوەبردنی هەموو پلانەکانی لەبەرکردن',
        'available_plans' => 'ئەو پلانانەی کە بەردەستن بۆ لەبەرکردن',
        'create_new' => 'پلانێکی نوێ بۆ لەبەرکردن دروست بکە',
        'custom_plan_info' => 'پلانی تایبەت ڕێگەت پێدەدات خۆت ئایەتەکان هەڵبژێریت',
        'custom_after_create' => 'دوای دروستکردنی پلان دەتوانیت بڕگەکان بە دەستی زیاد بکەیت',
    ],

    'total_users' => 'ژمارەی بەکارهێنەران',
    'my_progress' => 'پێشکەوتنی من',
    'today' => 'ئەمڕۆ',

    'no_plans_message_admin' => 'هێشتا هیچ پلانێک دروست نەکراوە. یەکەم پلان دروست بکە.',
    'no_plans_message_user' => 'لە ئێستادا هیچ پلانێکی چالاک نییە. تکایە دواتر سەردان بکە.',
];