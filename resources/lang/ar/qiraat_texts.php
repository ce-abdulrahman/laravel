<?php
// resources/lang/ar/qiraat_texts.php

return [
    'titles' => [
        'index' => 'نصوص القراءات',
        'create' => 'إضافة نص قراءة',
        'edit' => 'تعديل نص قراءة',
        'compare' => 'مقارنة القراءات',
        'form_create' => 'نموذج إضافة نص',
        'form_edit' => 'نموذج تعديل نص',
        'danger_zone' => 'منطقة الخطر',
    ],

    'hints' => [
        'manage' => 'إدارة متغيرات نصوص القراءات المختلفة',
        'create_new' => 'إضافة متغير نص قراءة جديد',
    ],

    'actions' => [
        'create' => 'إضافة نص',
        'create_first' => 'إضافة النص الأول',
        'back' => 'رجوع',
        'back_to_ayah' => 'العودة إلى الآية',
        'add_text' => 'إضافة نص',
        'add_first' => 'إضافة النص الأول',
        'add_variant' => 'إضافة متغير',
    ],

    'total_texts' => 'إجمالي النصوص',
    'total_qiraats_used' => 'القراءات المستخدمة',
    'ayahs_with_qiraat' => 'الآيات ذات القراءات',
    'variants' => 'متغير',

    'filter_by_qiraat' => 'تصفية حسب القراءة',
    'filter_by_surah' => 'تصفية حسب السورة',
    'all_qiraats' => 'جميع القراءات',
    'all_surahs' => 'جميع السور',
    'search' => 'بحث',
    'search_placeholder' => 'البحث بالنص...',

    'fields' => [
        'qiraat' => 'القراءة',
        'ayah' => 'آية',
        'surah_ayah' => 'السورة والآية',
        'text_variant' => 'متغير النص',
        'note' => 'ملاحظة',
    ],

    'sections' => [
        'selection' => 'الاختيار',
        'variant_details' => 'تفاصيل النص',
    ],

    'placeholders' => [
        'text_variant' => 'نص القراءة بالعربية...',
        'note' => 'ملاحظات إضافية...',
    ],

    'select_qiraat' => 'اختر القراءة',
    'select_ayah' => 'اختر الآية',
    'original_ayah' => 'نص الآية الأصلي',
    'ayah' => 'آية',
    'qiraat_variant' => 'متغير القراءة',
    'qiraat_info' => 'معلومات القراءة',
    'view_all_variants' => 'عرض جميع المتغيرات',
    'other_variants' => 'المتغيرات الأخرى',
    'compare_all' => 'مقارنة الكل',
    'highlight_differences' => 'تمييز الاختلافات',
    'diff_feature_coming' => 'هذه الميزة ستكون ملاحة قريباً',
    'qiraat_variants' => 'متغيرات القراءات',
    'no_variants_found' => 'لم يتم العثور على متغيرات',

    'no_texts_found' => 'لم يتم العثور على نصوص',

    'messages' => [
        'created' => 'تم إضافة نص القراءة بنجاح.',
        'updated' => 'تم تحديث نص القراءة بنجاح.',
        'deleted' => 'تم حذف نص القراءة بنجاح.',
        'delete_title' => 'حذف النص',
        'delete_warning' => 'حذف نص القراءة أمر دائم ولا يمكن التراجع عنه.',
        'confirm_delete' => 'هل أنت متأكد أنك تريد حذف هذا النص؟',
    ],

    'validation' => [
        'text_exists' => 'نص القراءة مسجل بالفعل لهذه الآية والقراءة',
    ],
];
