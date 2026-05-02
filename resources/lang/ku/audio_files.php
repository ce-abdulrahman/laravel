<?php
// resources/lang/ku/audio_files.php

return [
    'titles' => [
        'index' => 'فایلە دەنگییەکان',
        'create' => 'بارکردنی فایلی دەنگی',
        'edit' => 'دەستکاریکردنی فایلی دەنگی',
    ],

    'hints' => [
        'manage' => 'بەڕێوەبردنی فایلە دەنگییەکانی قورئان',
        'upload_new' => 'فایلێکی دەنگی نوێ بار بکە',
        'url_help' => 'بەستەری ڕاستەوخۆ بۆ فایلی دەنگی (MP3, WAV, OGG)',
    ],

    'actions' => [
        'upload' => 'بارکردنی فایل',
        'upload_first' => 'یەکەم فایل بار بکە',
        'upload_for_reciter' => 'بارکردن بۆ ئەم قورئان خوێنەرە',
        'back' => 'گەڕانەوە',
    ],

    'sections' => [
        'basic_info' => 'زانیاری بنەڕەتی',
        'audio_settings' => 'ڕێکخستنەکانی دەنگ',
        'source_settings' => 'ڕێکخستنەکانی سەرچاوە',
    ],

    'fields' => [
        'reciter' => 'قورئان خوێن',
        'surah' => 'سورەت',
        'ayah' => 'ئایەت',
        'duration' => 'ماوە',
        'quality' => 'کوالێتی',
        'source_type' => 'جۆری سەرچاوە',
        'url' => 'بەستەر',
        'is_active' => 'چالاکە',
        'surah_ayah' => 'سورەت و ئایەت',
        'status' => 'دۆخ',
    ],

    'source_types' => [
        'upload' => 'بارکردن',
        'url' => 'بەستەری دەرەکی',
    ],

    'select_reciter' => 'قورئان خوێن هەڵبژێرە',
    'select_surah' => 'سورەت هەڵبژێرە',
    'select_ayah' => 'ئایەت هەڵبژێرە',
    'select_quality' => 'کوالێتی هەڵبژێرە',
    'loading_ayahs' => 'ئایەتەکان بار دەکرێن...',

    'drag_drop' => 'فایلەکە ڕابکێشە و دایبنێ',
    'or' => 'یان',
    'browse_files' => 'هەڵبژاردنی فایل',
    'supported_formats' => 'فۆرماتە پشتگیریکراوەکان: MP3, WAV, OGG (تا 100MB)',
    'preview' => 'پێشبینین',
    'seconds' => 'چرکە',

    'total_files' => 'کۆی فایلەکان',
    'total_duration' => 'کۆی ماوە',
    'reciters_with_audio' => 'قورئان خوێنەرانی خاوەن دەنگ',
    'full_surahs' => 'سورەتی تەواو',

    'filter_by_reciter' => 'فلتەر بەپێی قورئان خوێن',
    'filter_by_surah' => 'فلتەر بەپێی سورەت',
    'filter_by_type' => 'فلتەر بەپێی جۆر',
    'all_reciters' => 'هەموو قورئان خوێنەکان',
    'all_surahs' => 'هەموو سورەتەکان',
    'all_types' => 'هەموو جۆرەکان',
    'full_surah' => 'سورەتی تەواو',
    'single_ayah' => 'تاکە ئایەت',

    'ayah' => 'ئایەت',
    'no_files_found' => 'هیچ فایلێکی دەنگی نەدۆزرایەوە',

    'messages' => [
        'created' => 'فایلی دەنگی بە سەرکەوتوویی زیاد کرا',
        'updated' => 'فایلی دەنگی بە سەرکەوتوویی نوێکرایەوە',
        'deleted' => 'فایلی دەنگی بە سەرکەوتوویی سڕایەوە',
        'activated' => 'فایلی دەنگی چالاک کرا',
        'deactivated' => 'فایلی دەنگی ناچالاک کرا',
        'upload_success' => 'فایلەکە بە سەرکەوتوویی بار کرا',
        'upload_error' => 'هەڵەیەک ڕوویدا لە کاتی بارکردنی فایلەکە',
        'invalid_file_type' => 'جۆری فایلەکە پشتگیری ناکرێت. تکایە MP3, WAV یان OGG بار بکە',
        'file_too_large' => 'قەبارەی فایلەکە زۆر گەورەیە. ئەوپەڕی قەبارە 100MB یە',
        'confirm_delete' => 'دڵنیایت کە دەتەوێت ئەم فایلە دەنگییە بسڕیتەوە؟',
    ],

    'validation' => [
        'audio_exists' => 'فایلێکی دەنگی پێشتر بۆ ئەم قورئان خوێن و سورەت/ئایەتە تۆمار کراوە',
    ],

    'placeholders' => [
        'duration' => 'بە چرکە',
    ],
];