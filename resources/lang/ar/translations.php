<?php

return [
    // Page Titles
    'titles' => [
        'index' => 'الترجمات',
        'create' => 'إضافة ترجمة جديدة',
        'edit' => 'تعديل الترجمة',
        'show' => 'تفاصيل الترجمة',
        'form_create' => 'نموذج إضافة ترجمة',
        'form_edit' => 'نموذج تعديل ترجمة',
        'help' => 'تعليمات',
        'danger_zone' => 'منطقة الخطورة',
    ],

    // Hints
    'hints' => [
        'manage' => 'إدارة ترجمات القرآن الكريم',
        'create_new' => 'إضافة ترجمة جديدة لآية',
        'default_help' => 'سيتم تعيين هذه الترجمة كترجمة افتراضية',
        'active_help' => 'سيتم عرض الترجمة على الصفحات',
    ],

    // Actions
    'actions' => [
        'create' => 'إضافة ترجمة',
        'create_first' => 'إضافة أول ترجمة',
        'back' => 'رجوع',
    ],

    // Statistics
    'total_translations' => 'إجمالي الترجمات',
    'total_languages' => 'عدد اللغات',
    'default_translations' => 'الترجمات الافتراضية',
    'translations' => 'الترجمة',

    // Filters
    'filter_by_language' => 'تصفية حسب اللغة',
    'filter_by_surah' => 'تصفية حسب السورة',
    'filter_by_translator' => 'تصفية حسب المترجم',
    'all_languages' => 'جميع اللغات',
    'all_surahs' => 'جميع السور',
    'all_translators' => 'جميع المترجمين',
    'search' => 'بحث',
    'search_placeholder' => 'بحث في محتوى الترجمة...',

    // Fields
    'fields' => [
        'surah_ayah' => 'السورة والآية',
        'language' => 'اللغة',
        'translator' => 'المترجم',
        'content' => 'محتوى الترجمة',
        'is_default' => 'ترجمة افتراضية',
        'status' => 'الحالة',
        'ayah' => 'الآية',
        'select_ayah' => 'اختر الآية',
        'select_language' => 'اختر اللغة',
        'set_as_default' => 'تعيين كافتراضي',
        'is_active' => 'نشط',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'surah' => 'السورة',
        'ayah_number' => 'رقم الآية',
    ],

    // Sections
    'sections' => [
        'ayah_selection' => 'اختيار الآية',
        'translation_details' => 'تفاصيل الترجمة',
        'settings' => 'الإعدادات',
    ],

    // Placeholders
    'placeholders' => [
        'translator' => 'اسم المترجم (اختياري)',
        'content' => 'محتوى الترجمة...',
    ],

    // Ayah
    'ayah' => 'الآية',
    'selected_ayah' => 'الآية المختارة',
    'original_ayah' => 'النص الأصلي للآية',
    'view_full_ayah' => 'عرض الآية الكاملة',
    'translation' => 'الترجمة',
    'default' => 'افتراضي',
    'unknown' => 'غير معروف',
    'details' => 'التفاصيل',
    'other_translations' => 'ترجمات أخرى',

    // Messages
    'no_translations_found' => 'لم يتم العثور على ترجمات',
    'no_translations_message' => 'لم يتم العثور على ترجمة بهذه الفلاتر. يرجى تغيير الفلاتر',
    'delete_confirm_message' => 'هل أنت متأكد أنك تريد حذف هذه الترجمة؟',
    'delete_title' => 'حذف هذه الترجمة',
    'delete_warning' => 'حذف الترجمة إجراء نهائي ولا يمكن التراجع عنه.',
    'confirm_delete' => 'هل أنت متأكد أنك تريد حذف هذه الترجمة؟',
    'messages' => [
        'created' => 'تم إضافة الترجمة بنجاح',
        'updated' => 'تم تحديث الترجمة بنجاح',
        'deleted' => 'تم حذف الترجمة بنجاح',
        'activated' => 'تم تفعيل الترجمة',
        'deactivated' => 'تم تعطيل الترجمة',
        'set_default' => 'تم تعيين الترجمة كافتراضية',
        'copied' => 'تم نسخ الترجمة',
    ],

    // Validation
    'validation' => [
        'translation_exists' => 'توجد ترجمة مسجلة بالفعل لهذه اللغة والمترجم وهذه الآية',
    ],

    // Help
    'help' => [
        'step1' => 'اختر آية للترجمة',
        'step2' => 'حدد اللغة واسم المترجم',
        'step3' => 'اكتب نص الترجمة واحفظها',
    ],

    'copy_translation' => 'نسخ الترجمة',
    'set_as_default' => 'تعيين كترجمة افتراضية',
];
