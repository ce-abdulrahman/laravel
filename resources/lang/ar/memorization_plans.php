<?php
// resources/lang/ar/memorization_plans.php

return [
    'titles' => [
        'index' => 'خطط الحفظ',
        'create' => 'إنشاء خطة جديدة',
        'edit' => 'تعديل الخطة',
        'show' => 'تفاصيل الخطة',
    ],

    'actions' => [
        'create' => 'إنشاء خطة',
        'create_first' => 'أنشئ خطتك الأولى',
        'back' => 'رجوع',
        'mark_complete' => 'تحديد كمكتمل',
    ],

    'sections' => [
        'basic_settings' => 'الإعدادات الأساسية',
        'plan_content' => 'محتوى الخطة',
    ],

    'fields' => [
        'title' => 'العنوان',
        'plan_type' => 'نوع الخطة',
        'start_date' => 'تاريخ البدء',
        'target_end_date' => 'تاريخ الانتهاء المستهدف',
        'daily_target' => 'الهدف اليومي',
        'notes' => 'ملاحظات',
        'day' => 'اليوم',
        'surah_ayah' => 'السورة والآية',
        'target_date' => 'التاريخ المستهدف',
        'status' => 'الحالة',
    ],

    'placeholders' => [
        'notes' => 'ملاحظاتك على هذه الخطة...',
    ],

    'plan_types' => [
        'juz' => 'جزء',
        'surah' => 'سورة',
        'custom' => 'مخصص',
    ],

    'target_types' => [
        'ayahs' => 'آيات',
        'pages' => 'صفحات',
        'juz' => 'أجزاء',
        'hizb' => 'أحزاب',
    ],

    'statuses' => [
        'active' => 'نشط',
        'paused' => 'متوقف مؤقتاً',
        'completed' => 'مكتمل',
        'pending' => 'قيد الانتظار',
        'skipped' => 'متخطى',
    ],

    'total_plans' => 'إجمالي الخطط',
    'active_plans' => 'الخطط النشطة',
    'completed_plans' => 'الخطط المكتملة',
    'total_items' => 'إجمالي العناصر',
    'days' => 'أيام',

    'filter_by_status' => 'تصفية حسب الحالة',
    'filter_by_type' => 'تصفية حسب النوع',
    'all_statuses' => 'جميع الحالات',
    'all_types' => 'جميع الأنواع',

    'select_type' => 'اختر نوع الخطة',
    'select_surah' => 'اختر السورة',
    'select_juz' => 'اختر الجزء',
    'choose_surah' => 'اختر سورة',
    'choose_juz' => 'اختر جزءاً',
    'juz' => 'جزء',
    'per_day' => 'في اليوم',
    'to' => 'إلى',

    'overall_progress' => 'التقدم العام',
    'completed_days' => 'الأيام المكتملة',
    'pending_days' => 'الأيام المتبقية',
    'total_ayahs' => 'إجمالي الآيات',
    'today_task' => 'مهمة اليوم',
    'completed' => 'مكتمل',
    'overdue' => 'متأخر',
    'plan_schedule' => 'جدول الخطة',
    'started' => 'بدأت',
    'target_end' => 'نهاية الهدف',
    'next_target' => 'الهدف القادم',

    'no_plans' => 'لا توجد خطط',
    'no_plans_message' => 'لم تقم بإنشاء أي خطة حفظ بعد.',

    'messages' => [
        'created' => 'تم إنشاء الخطة بنجاح.',
        'updated' => 'تم تحديث الخطة بنجاح.',
        'deleted' => 'تم حذف الخطة بنجاح.',
        'item_updated' => 'تم تحديث حالة عنصر الخطة بنجاح.',
        'confirm_delete' => 'هل أنت متأكد أنك تريد حذف هذه الخطة؟',
    ],

    'hints' => [
        'my_plans' => 'خطط حفظ القرآن الكريم',
        'manage_all_plans' => 'إدارة جميع خطط الحفظ',
        'available_plans' => 'الخطط المتاحة للحفظ',
        'create_new' => 'إنشاء خطة حفظ جديدة',
        'custom_plan_info' => 'الخطة المخصصة تتيح لك تحديد الآيات يدوياً',
        'custom_after_create' => 'بعد إنشاء الخطة، يمكنك إضافة العناصر يدوياً',
    ],

    'total_users' => 'إجمالي المستخدمين',
    'my_progress' => 'تقدمي',
    'today' => 'اليوم',

    'no_plans_message_admin' => 'لا توجد خطط منشأة بعد. أنشئ الخطة الأولى.',
    'no_plans_message_user' => 'لا توجد خطط نشطة حالياً. يرجى زيارة الصفحة لاحقاً.',
];
