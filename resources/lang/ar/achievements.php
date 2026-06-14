<?php
// resources/lang/ar/achievements.php

return [
    'titles' => [
        'index'              => 'إدارة الإنجازات',
        'achievements'       => 'جميع الإنجازات',
        'create'             => 'إنجاز جديد',
        'edit'               => 'تعديل الإنجاز',
        'categories'         => 'تصنيفات الإنجازات',
        'category_create'    => 'تصنيف جديد',
        'category_edit'      => 'تعديل التصنيف',
        'user_achievements'  => 'إنجازات المستخدمين',
        'analytics'          => 'تحليلات الإنجازات',
    ],

    'hints' => [
        'index'             => 'إدارة وإنشاء إنجازات النظام.',
        'create'            => 'أدخل بيانات الإنجاز الجديد.',
        'edit'              => 'عدّل بيانات هذا الإنجاز.',
        'categories'        => 'إدارة تصنيفات الإنجازات.',
        'user_achievements' => 'تحليل وإدارة إنجازات جميع المستخدمين.',
    ],

    'fields' => [
        'icon'            => 'الأيقونة',
        'key'             => 'المفتاح',
        'category'        => 'التصنيف',
        'sort_order'      => 'الترتيب',
        'condition_type'  => 'نوع الشرط',
        'condition_value' => 'هدف الشرط',
        'version'         => 'الإصدار',
        'reward_type'     => 'نوع المكافأة',
        'reward_points'   => 'نقاط المكافأة',
        'is_active'       => 'نشط',
        'is_hidden'       => 'مخفي',
        'name'            => 'الاسم',
        'description'     => 'الوصف',
    ],

    'sections' => [
        'basic'        => 'المعلومات الأساسية',
        'condition'    => 'شرط الإنجاز',
        'reward'       => 'المكافأة',
        'translations' => 'الترجمات',
        'options'      => 'الخيارات',
    ],

    'actions' => [
        'create'         => 'إنجاز جديد',
        'edit'           => 'تعديل',
        'delete'         => 'حذف',
        'save'           => 'حفظ',
        'update'         => 'تحديث',
        'back'           => 'رجوع',
        'cancel'         => 'إلغاء',
        'refresh'        => 'تحديث',
        'view_cats'      => 'التصنيفات',
        'view_users'     => 'إنجازات المستخدمين',
        'view_analytics' => 'التحليلات',
        'grant'          => 'منح',
        'revoke'         => 'سحب',
        'reset'          => 'إعادة ضبط',
    ],

    'condition_types' => [
        'TOTAL_DHIKR'         => 'إجمالي التسبيح',
        'CURRENT_STREAK'      => 'السلسلة الحالية (أيام)',
        'LONGEST_STREAK'      => 'أطول سلسلة (أيام)',
        'GOALS_COMPLETED'     => 'الأهداف المكتملة',
        'SESSION_DHIKR_COUNT' => 'التسبيح في جلسة واحدة',
        'CONSECUTIVE_DAYS'    => 'الأيام المتتالية',
        'SPECIAL_EVENT'       => 'حدث خاص',
        'CUSTOM_RULE'         => 'قاعدة مخصصة',
    ],

    'reward_types' => [
        'POINTS'        => 'نقاط',
        'BADGE'         => 'شارة',
        'TITLE'         => 'لقب',
        'SPECIAL_THEME' => 'ثيم خاص',
        'FUTURE_REWARD' => 'مكافأة مستقبلية',
    ],

    'status' => [
        'active'      => 'نشط',
        'inactive'    => 'غير نشط',
        'hidden'      => 'مخفي',
        'completed'   => 'مكتمل',
        'in_progress' => 'قيد التقدم',
    ],

    'messages' => [
        'created'        => 'تم إنشاء الإنجاز بنجاح.',
        'updated'        => 'تم تحديث الإنجاز بنجاح.',
        'deleted'        => 'تم حذف الإنجاز بنجاح.',
        'granted'        => 'تم منح الإنجاز للمستخدم بنجاح.',
        'revoked'        => 'تم سحب الإنجاز.',
        'reset'          => 'تم إعادة ضبط التقدم.',
        'confirm_delete' => 'هل أنت متأكد من حذف هذا الإنجاز؟',
        'confirm_revoke' => 'هل أنت متأكد من سحب هذا الإنجاز من المستخدم؟',
    ],

    'table' => [
        'number'      => '#',
        'achievement' => 'الإنجاز',
        'category'    => 'التصنيف',
        'condition'   => 'الشرط',
        'target'      => 'الهدف',
        'reward'      => 'المكافأة',
        'status'      => 'الحالة',
        'actions'     => 'الإجراءات',
        'user'        => 'المستخدم',
        'progress'    => 'التقدم',
        'unlocked_at' => 'وقت الإنجاز',
    ],

    'stats' => [
        'total'            => 'إجمالي الإنجازات',
        'active'           => 'نشط',
        'hidden'           => 'مخفي',
        'categories'       => 'التصنيفات',
        'total_unlocks'    => 'إجمالي الإنجازات المحققة',
        'active_users'     => 'المستخدمون النشطون',
        'avg_completion'   => 'متوسط الإكمال',
        'today_unlocks'    => 'اليوم',
        'top_achievements' => 'الإنجازات الأكثر تحقيقاً',
        'top_users'        => 'أفضل المستخدمين',
        'by_category'      => 'الإنجازات حسب التصنيف',
    ],

    'pagination' => [
        'showing' => 'عرض',
        'to'      => 'إلى',
        'of'      => 'من',
        'entries' => 'إنجاز',
        'total'   => 'المجموع:',
    ],

    'empty' => [
        'achievements' => 'لا توجد إنجازات',
        'categories'   => 'لا توجد تصنيفات',
        'users'        => 'لا توجد إنجازات محققة',
    ],

    'placeholders' => [
        'search'      => 'البحث بالاسم أو المفتاح...',
        'search_user' => 'البحث باسم المستخدم...',
        'key'         => 'first_tasbih',
        'icon'        => '🏆',
    ],
];
