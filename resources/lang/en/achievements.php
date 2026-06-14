<?php
// resources/lang/en/achievements.php

return [
    'titles' => [
        'index'              => 'Achievements Management',
        'achievements'       => 'All Achievements',
        'create'             => 'New Achievement',
        'edit'               => 'Edit Achievement',
        'categories'         => 'Achievement Categories',
        'category_create'    => 'New Category',
        'category_edit'      => 'Edit Category',
        'user_achievements'  => 'User Achievements',
        'analytics'          => 'Achievement Analytics',
    ],

    'hints' => [
        'index'             => 'Manage and configure system achievements.',
        'create'            => 'Fill in the details for the new achievement.',
        'edit'              => 'Edit the details of this achievement.',
        'categories'        => 'Manage achievement categories.',
        'user_achievements' => 'Analyse and manage achievements across all users.',
    ],

    'fields' => [
        'icon'            => 'Icon',
        'key'             => 'Key',
        'category'        => 'Category',
        'sort_order'      => 'Sort Order',
        'condition_type'  => 'Condition Type',
        'condition_value' => 'Condition Target',
        'version'         => 'Version',
        'reward_type'     => 'Reward Type',
        'reward_points'   => 'Reward Points',
        'is_active'       => 'Active',
        'is_hidden'       => 'Hidden',
        'name'            => 'Name',
        'description'     => 'Description',
    ],

    'sections' => [
        'basic'        => 'Basic Information',
        'condition'    => 'Achievement Condition',
        'reward'       => 'Reward',
        'translations' => 'Translations',
        'options'      => 'Options',
    ],

    'actions' => [
        'create'         => 'New Achievement',
        'edit'           => 'Edit',
        'delete'         => 'Delete',
        'save'           => 'Save',
        'update'         => 'Update',
        'back'           => 'Back',
        'cancel'         => 'Cancel',
        'refresh'        => 'Refresh',
        'view_cats'      => 'Categories',
        'view_users'     => 'User Achievements',
        'view_analytics' => 'Analytics',
        'grant'          => 'Grant',
        'revoke'         => 'Revoke',
        'reset'          => 'Reset Progress',
    ],

    'condition_types' => [
        'TOTAL_DHIKR'         => 'Total Dhikr Count',
        'CURRENT_STREAK'      => 'Current Streak (Days)',
        'LONGEST_STREAK'      => 'Longest Streak (Days)',
        'GOALS_COMPLETED'     => 'Goals Completed',
        'SESSION_DHIKR_COUNT' => 'Dhikr in One Session',
        'CONSECUTIVE_DAYS'    => 'Consecutive Active Days',
        'SPECIAL_EVENT'       => 'Special Event (Time-based)',
        'CUSTOM_RULE'         => 'Custom Rule',
    ],

    'reward_types' => [
        'POINTS'        => 'Points',
        'BADGE'         => 'Badge',
        'TITLE'         => 'Title',
        'SPECIAL_THEME' => 'Special Theme',
        'FUTURE_REWARD' => 'Future Reward',
    ],

    'status' => [
        'active'      => 'Active',
        'inactive'    => 'Inactive',
        'hidden'      => 'Hidden',
        'completed'   => 'Completed',
        'in_progress' => 'In Progress',
    ],

    'messages' => [
        'created'        => 'Achievement created successfully.',
        'updated'        => 'Achievement updated successfully.',
        'deleted'        => 'Achievement deleted.',
        'granted'        => 'Achievement granted to user successfully.',
        'revoked'        => 'Achievement revoked.',
        'reset'          => 'Achievement progress reset.',
        'confirm_delete' => 'Are you sure you want to delete this achievement?',
        'confirm_revoke' => 'Are you sure you want to revoke this achievement from the user?',
    ],

    'table' => [
        'number'      => '#',
        'achievement' => 'Achievement',
        'category'    => 'Category',
        'condition'   => 'Condition',
        'target'      => 'Target',
        'reward'      => 'Reward',
        'status'      => 'Status',
        'actions'     => 'Actions',
        'user'        => 'User',
        'progress'    => 'Progress',
        'unlocked_at' => 'Unlocked At',
    ],

    'stats' => [
        'total'            => 'Total Achievements',
        'active'           => 'Active',
        'hidden'           => 'Hidden',
        'categories'       => 'Categories',
        'total_unlocks'    => 'Total Unlocks',
        'active_users'     => 'Active Users',
        'avg_completion'   => 'Avg. Completion',
        'today_unlocks'    => 'Today',
        'top_achievements' => 'Most Earned Achievements',
        'top_users'        => 'Top Users',
        'by_category'      => 'Unlocks by Category',
    ],

    'pagination' => [
        'showing' => 'Showing',
        'to'      => 'to',
        'of'      => 'of',
        'entries' => 'achievements',
        'total'   => 'Total:',
    ],

    'empty' => [
        'achievements' => 'No achievements found',
        'categories'   => 'No categories found',
        'users'        => 'No unlocks found',
    ],

    'placeholders' => [
        'search'      => 'Search by name or key...',
        'search_user' => 'Search by user name...',
        'key'         => 'first_tasbih',
        'icon'        => '🏆',
    ],
];
