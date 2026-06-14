<?php

return [
    'titles' => [
        'index' => 'أجزاء التجويد',
        'create' => 'إضافة جزء تجويد',
        'edit' => 'تعديل الجزء',
        'form_create' => 'نموذج إضافة جزء',
        'form_edit' => 'نموذج تعديل جزء',
        'danger_zone' => 'منطقة الخطورة',
    ],

    'hints' => [
        'manage' => 'إدارة أجزاء التجويد',
        'create_new' => 'إضافة جزء تجويد جديد',
    ],

    'actions' => [
        'create' => 'إضافة جزء',
        'create_first' => 'إضافة أول جزء',
        'back' => 'رجوع',
        'add_segment' => 'إضافة جزء',
        'add_first' => 'إضافة أول جزء',
    ],

    'total_segments' => 'إجمالي الأجزاء',
    'total_rules_used' => 'الأحكام المستخدمة',
    'ayahs_with_tajweed' => 'الآيات مع التجويد',

    'filter_by_rule' => 'تصفية حسب الحكم',
    'filter_by_surah' => 'تصفية حسب السورة',
    'filter_by_category' => 'تصفية حسب القسم',
    'filter_by_ayah' => 'تصفية حسب رقم الآية',
    'all_rules' => 'جميع الأحكام',
    'all_surahs' => 'جميع السور',
    'all_categories' => 'جميع الأقسام',
    'search' => 'بحث',
    'search_placeholder' => 'بحث حسب نص التجويد...',

    'fields' => [
        'tajweed_rule' => 'حكم التجويد',
        'ayah' => 'الآية',
        'surah_ayah' => 'السورة والآية',
        'rule' => 'الحكم',
        'matched_text' => 'النص المقارن',
        'text_segment' => 'النص المقارن', // Deprecated alias
        'start_index' => 'مؤشر البداية',
        'end_index' => 'مؤشر النهاية',
        'metadata' => 'البيانات الوصفية (JSON)',
        'note' => 'ملاحظة',
    ],

    'sections' => [
        'selection' => 'الاختيار',
        'segment_details' => 'تفاصيل الجزء',
    ],

    'placeholders' => [
        'text_segment' => 'نص الجزء بالعربية...',
        'matched_text' => 'نص الجزء بالعربية...',
        'metadata' => '{"duration": "2_harakat"}',
        'note' => 'ملاحظة إضافية...',
    ],

    'select_rule' => 'اختر الحكم',
    'select_ayah' => 'اختر الآية',
    'selected_ayah' => 'الآية المختارة',
    'ayah' => 'الآية',
    'full_ayah' => 'الآية الكاملة',
    'segment_details' => 'تفاصيل الجزء',
    'rule_info' => 'معلومات الحكم',
    'view_full_rule' => 'عرض الحكم الكامل',
    'other_segments' => 'أجزاء أخرى',
    'metadata' => 'البيانات الوصفية',

    'no_segments_found' => 'لم يتم العثور على أجزاء',

    'messages' => [
        'created' => 'تم إضافة الجزء بنجاح',
        'created_batch' => 'تم إضافة الأجزاء بنجاح',
        'updated' => 'تم تحديث الجزء بنجاح',
        'deleted' => 'تم حذف الجزء بنجاح',
        'delete_title' => 'حذف الجزء',
        'delete_warning' => 'حذف الجزء إجراء نهائي ولا يمكن التراجع عنه.',
        'confirm_delete' => 'هل أنت متأكد أنك تريد حذف هذا الجزء؟',
        'rebuild_title' => 'إعادة بناء الأجزاء',
        'rebuild_warning' => 'إعادة بناء الأجزاء سيقوم بحذف كافة السجلات الحالية أولاً واستيراد سجلات جديدة. هذا الإجراء لا يمكن التراجع عنه.',
        'confirm_rebuild' => 'هل أنت متأكد أنك تريد حذف جميع الأجزاء وإعادة البناء من الملف المرفوع؟',
    ],
];
