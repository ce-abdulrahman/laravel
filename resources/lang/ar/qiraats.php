<?php
// resources/lang/ar/qiraats.php

return [
    'titles' => [
        'index' => 'القراءات',
        'create' => 'إضافة قراءة',
        'edit' => 'تعديل القراءة',
        'form_create' => 'نموذج إضافة قراءة',
        'form_edit' => 'نموذج تعديل القراءة',
        'danger_zone' => 'منطقة الخطر',
    ],

    'hints' => [
        'manage' => 'إدارة قراءات القرآن الكريم المختلفة',
        'create_new' => 'إضافة قراءة جديدة',
        'edit_existing' => 'تعديل تفاصيل القراءة',
    ],

    'actions' => [
        'create' => 'إضافة قراءة',
        'create_first' => 'إضافة القراءة الأولى',
        'back' => 'رجوع',
    ],

    'total_qiraats' => 'إجمالي القراءات',
    'active_qiraats' => 'القراءات النشطة',
    'total_texts' => 'إجمالي النصوص',
    'texts' => 'نصوص',

    'filter_by_riwayah' => 'تصفية حسب الرواية',
    'filter_by_status' => 'تصفية حسب الحالة',
    'all_riwayahs' => 'جميع الروايات',
    'all_status' => 'جميع الحالات',
    'search' => 'بحث',
    'search_placeholder' => 'البحث بالاسم أو الوصف...',

    'fields' => [
        'name' => 'اسم القراءة',
        'riwayah' => 'الرواية',
        'description' => 'الوصف',
        'status' => 'الحالة',
        'is_active' => 'نشط',
        'total_texts' => 'عدد النصوص',
        'surahs_covered' => 'السور المشمولة',
    ],

    'sections' => [
        'basic_info' => 'معلومات أساسية',
    ],

    'placeholders' => [
        'description' => 'وصف القراءة...',
    ],

    'select_riwayah' => 'اختر الرواية',
    'details' => 'التفاصيل',
    'texts_list' => 'قائمة النصوص',
    'surahs' => 'سور',
    'no_qiraats_found' => 'لم يتم العثور على قراءات',
    'no_qiraats_message' => 'لم يتم العثور على قراءة تطابق هذه الفلاتر',
    'no_texts_yet' => 'لا توجد نصوص بعد',

    'messages' => [
        'created' => 'تم إضافة القراءة بنجاح.',
        'updated' => 'تم تحديث القراءة بنجاح.',
        'deleted' => 'تم حذف القراءة بنجاح.',
        'activated' => 'تم تفعيل القراءة.',
        'deactivated' => 'تم إلغاء تفعيل القراءة.',
        'has_texts' => 'لا يمكنك حذف هذه القراءة لأنها تحتوي على نصوص مرتبطة بها',
        'delete_title' => 'حذف القراءة',
        'delete_warning' => 'حذف القراءة أمر دائم ولا يمكن التراجع عنه.',
        'confirm_delete' => 'هل أنت متأكد أنك تريد حذف هذه القراءة؟',
    ],
];
