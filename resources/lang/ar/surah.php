<?php

return [
    'titles' => [
        'index' => 'السور',
        'create' => 'إضافة سورة جديدة',
        'edit' => 'تعديل السورة',
        'show' => 'تفاصيل السورة',
        'details' => 'المعلومات',
        'quick_actions' => 'إجراءات سريعة',
        'form_create' => 'نموذج الإضافة',
        'form_edit' => 'نموذج التعديل',
        'help' => 'مساعدة',
        'danger_zone' => 'منطقة الخطر',
    ],

    'fields' => [
        'number' => 'الرقم',
        'name_ar' => 'الاسم بالعربية',
        'name_ku' => 'الاسم بالكردية',
        'name_en' => 'الاسم بالإنجليزية',
        'revelation_type' => 'مكان النزول',
        'ayah_count' => 'عدد الآيات',
        'is_active' => 'نشط',
        'page_start' => 'صفحة البداية',
        'page_end' => 'صفحة النهاية',
        'juz_start' => 'بداية الجزء',
        'juz_end' => 'نهاية الجزء',
        'description' => 'الوصف',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'page_range' => 'نطاق الصفحات',
        'juz_range' => 'نطاق الأجزاء',
        'juz' => 'جزء',
    ],

    'revelation_types' => [
        'meccan' => 'مكية',
        'medinan' => 'مدنية',
        'Meccan' => 'مكية',
        'Medinan' => 'مدنية',
    ],

    'status' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ],

    'actions' => [
        'create' => 'إضافة سورة',
        'create_first' => 'أضف أول سورة',
        'view' => 'عرض',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'back' => 'رجوع',
        'save' => 'حفظ',
        'cancel' => 'إلغاء',
        'view_ayahs' => 'عرض الآيات',
        'view_tafsir' => 'عرض التفسير',
        'listen' => 'استماع',
        'start_memorization' => 'بدء الحفظ',
    ],

    'sections' => [
        'basic_info' => 'معلومات أساسية',
        'translations' => 'الترجمات',
        'classification' => 'التصنيف',
        'position' => 'الموضع',
    ],

    'hints' => [
        'manage' => 'إدارة جميع سور القرآن الكريم',
        'create_new' => 'أضف سورة جديدة إلى قاعدة البيانات',
        'edit_existing' => 'تحديث معلومات هذه السورة',
        'view_details' => 'عرض التفاصيل الكاملة للسورة',
        'updated_at' => 'آخر تحديث',
    ],

    'messages' => [
        'confirm_delete' => 'هل أنت متأكد من حذف هذه السورة؟',
        'delete_title' => 'حذف السورة',
        'delete_warning' => 'هذا الإجراء لا يمكن التراجع عنه. سيتم حذف جميع الآيات والبيانات المرتبطة بهذه السورة.',
        'cannot_undo' => 'هذا الإجراء لا يمكن التراجع عنه!',
        'no_surahs_found' => 'لم يتم العثور على أي سورة',
        'created' => 'تم إنشاء السورة بنجاح',
        'updated' => 'تم تحديث السورة بنجاح',
        'deleted' => 'تم حذف السورة بنجاح',
    ],

    'help' => [
        'step1' => 'أدخل رقم واسم السورة',
        'step2' => 'حدد مكان النزول وعدد الآيات',
        'step3' => 'حدد الصفحات والأجزاء (اختياري)',
    ],

    'placeholders' => [
        'description' => 'اكتب وصفاً مختصراً عن السورة...',
    ],

    'search_placeholder' => 'بحث باسم السورة...',
];
