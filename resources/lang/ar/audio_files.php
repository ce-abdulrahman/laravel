<?php

return [
    'titles' => [
        'index' => 'الملفات الصوتية',
        'create' => 'رفع ملف صوتي',
        'edit' => 'تعديل الملف الصوتي',
    ],

    'hints' => [
        'manage' => 'إدارة ملفات القرآن الصوتية',
        'upload_new' => 'رفع ملف صوتي جديد',
        'url_help' => 'رابط مباشر للملف الصوتي (MP3, WAV, OGG)',
    ],

    'actions' => [
        'upload' => 'رفع ملف',
        'upload_first' => 'رفع أول ملف',
        'upload_for_reciter' => 'رفع لهذا القارئ',
        'back' => 'رجوع',
    ],

    'sections' => [
        'basic_info' => 'معلومات أساسية',
        'audio_settings' => 'إعدادات الصوت',
        'source_settings' => 'إعدادات المصدر',
    ],

    'fields' => [
        'reciter' => 'القارئ',
        'surah' => 'السورة',
        'ayah' => 'الآية',
        'duration' => 'المدة',
        'quality' => 'الجودة',
        'source_type' => 'نوع المصدر',
        'url' => 'الرابط',
        'is_active' => 'نشط',
        'surah_ayah' => 'السورة والآية',
        'status' => 'الحالة',
    ],

    'source_types' => [
        'upload' => 'رفع',
        'url' => 'رابط خارجي',
    ],

    'select_reciter' => 'اختر القارئ',
    'select_surah' => 'اختر السورة',
    'select_ayah' => 'اختر الآية',
    'select_quality' => 'اختر الجودة',
    'loading_ayahs' => 'جاري تحميل الآيات...',

    'drag_drop' => 'اسحب الملف وأفلته هنا',
    'or' => 'أو',
    'browse_files' => 'تصفح الملفات',
    'supported_formats' => 'الصيغ المدعومة: MP3, WAV, OGG (بحد أقصى 100 ميجابايت)',
    'preview' => 'معاينة',
    'seconds' => 'ثانية',

    'total_files' => 'إجمالي الملفات',
    'total_duration' => 'إجمالي المدة',
    'reciters_with_audio' => 'القراء الذين لديهم صوتيات',
    'full_surahs' => 'سور كاملة',

    'filter_by_reciter' => 'تصفية حسب القارئ',
    'filter_by_surah' => 'تصفية حسب السورة',
    'filter_by_type' => 'تصفية حسب النوع',
    'all_reciters' => 'جميع القراء',
    'all_surahs' => 'جميع السور',
    'all_types' => 'جميع الأنواع',
    'full_surah' => 'سورة كاملة',
    'single_ayah' => 'آية واحدة',

    'ayah' => 'الآية',
    'no_files_found' => 'لم يتم العثور على ملفات صوتية',

    'messages' => [
        'created' => 'تم إضافة الملف الصوتي بنجاح',
        'updated' => 'تم تحديث الملف الصوتي بنجاح',
        'deleted' => 'تم حذف الملف الصوتي بنجاح',
        'activated' => 'تم تفعيل الملف الصوتي',
        'deactivated' => 'تم تعطيل الملف الصوتي',
        'upload_success' => 'تم رفع الملف بنجاح',
        'upload_error' => 'حدث خطأ أثناء رفع الملف',
        'invalid_file_type' => 'نوع الملف غير مدعوم. يرجى رفع ملف MP3 أو WAV أو OGG',
        'file_too_large' => 'حجم الملف كبير جداً. الحد الأقصى هو 100 ميجابايت',
        'confirm_delete' => 'هل أنت متأكد أنك تريد حذف هذا الملف الصوتي؟',
    ],

    'validation' => [
        'audio_exists' => 'يوجد ملف صوتي مسجل بالفعل لهذا القارئ والسورة/الآية',
    ],

    'placeholders' => [
        'duration' => 'بالثواني',
    ],
];
