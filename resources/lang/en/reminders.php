<?php

return [

    'titles' => [
        'index'     => 'Manage Reminders',
        'create'    => 'New Reminder Template',
        'edit'      => 'Edit Reminder Template',
        'analytics' => 'Reminder Analytics',
        'users'     => 'User Reminders',
        'settings'  => 'Reminder Settings',
    ],

    'hints' => [
        'index'     => 'Manage and configure push notification reminder templates',
        'create'    => 'Create a new reminder template',
        'edit'      => 'Modify reminder template details and translations',
        'analytics' => 'View performance metrics and open rates for smart reminders',
        'users'     => 'Monitor user reminder preferences and logs',
    ],

    'fields' => [
        'key'            => 'Key',
        'type'           => 'Type',
        'icon'           => 'Icon',
        'priority'       => 'Priority',
        'sort_order'     => 'Sort Order',
        'version'        => 'Version',
        'is_active'      => 'Active',
        'metadata'       => 'Metadata',
        'title'          => 'Title',
        'body'           => 'Body',
        'frequency'      => 'Frequency',
        'scheduled_time' => 'Scheduled Time',
        'timezone'       => 'Timezone',
        'last_sent_at'   => 'Last Sent At',
        'enabled_count'  => 'Active Subscriptions',
    ],

    'types' => [
        'MORNING'      => '🌅 Morning',
        'AFTERNOON'    => '☀️ Afternoon',
        'EVENING'      => '🌆 Evening',
        'BEFORE_SLEEP' => '🌙 Before Sleep',
        'DAILY_GOAL'   => '🎯 Daily Goal',
        'STREAK'       => '🔥 Streak Protection',
        'ACHIEVEMENT'  => '🏆 Achievement',
        'INACTIVITY'   => '💤 Inactivity',
        'CUSTOM'       => '⚙️ Custom',
    ],

    'frequency' => [
        'daily'    => 'Daily',
        'weekdays' => 'Weekdays (Mon-Fri)',
        'weekends' => 'Weekends (Sat-Sun)',
        'custom'   => 'Specific Days',
    ],

    'status' => [
        'sent'      => 'Sent',
        'opened'    => 'Opened',
        'failed'    => 'Failed',
        'snoozed'   => 'Snoozed',
        'cancelled' => 'Cancelled',
        'active'    => 'Active',
        'inactive'  => 'Inactive',
    ],

    'sections' => [
        'basic'        => 'Basic Information',
        'translations' => 'Translations',
        'options'      => 'Options',
    ],

    'actions' => [
        'create'    => 'Add Template',
        'edit'      => 'Edit',
        'delete'    => 'Delete',
        'save'      => 'Save Template',
        'update'    => 'Update Template',
        'cancel'    => 'Cancel',
        'back'      => 'Back',
        'duplicate' => 'Duplicate',
        'test'      => 'Send Test',
        'analytics' => 'Analytics',
        'users'     => 'Users',
        'filter'    => 'Filter',
        'preview'   => 'Preview',
    ],

    'messages' => [
        'created'    => '✅ Reminder template created successfully.',
        'updated'    => '✅ Reminder template updated successfully.',
        'deleted'    => '🗑️ Reminder template deleted successfully.',
        'duplicated' => '📋 Reminder template duplicated successfully.',
        'saved'      => 'Reminder settings saved successfully.',
        'test_sent'  => '🔔 Test notification log generated.',
        'confirm_delete' => 'Are you sure you want to delete this reminder template?',
    ],

    'table' => [
        'key'        => 'Key',
        'type'       => 'Type',
        'title'      => 'Title',
        'priority'   => 'Priority',
        'version'    => 'Version',
        'status'     => 'Status',
        'actions'    => 'Actions',
        'sent'       => 'Sent',
        'opened'     => 'Opened',
        'user'       => 'User',
        'reminders'  => 'Reminders',
        'last_sent'  => 'Last Sent',
        'platform'   => 'Platform',
    ],

    'stats' => [
        'total_sent'     => 'Total Sent',
        'total_opened'   => 'Total Opened',
        'open_rate'      => 'Open Rate',
        'active_users'   => 'Active Users',
        'total_failed'   => 'Total Failed',
        'total_snoozed'  => 'Total Snoozed',
        'most_effective' => 'Most Effective Type',
    ],

    'analytics' => [
        'period'      => 'Period',
        'days_7'      => '7 Days',
        'days_14'     => '14 Days',
        'days_30'     => '30 Days',
        'days_90'     => '90 Days',
        'daily_chart' => 'Daily Activity Chart',
    ],

    'smart' => [
        'goal_remaining'   => 'Only :count Tasbihs left to complete your daily goal!',
        'streak_warning'   => '🔥 Your :days days streak is in danger — make Tasbih today!',
        'achievement_close'=> '🏆 You are close to unlocking a new achievement!',
    ],

    'empty' => [
        'templates' => 'No reminder templates found',
        'users'     => 'No users found with active reminders',
        'logs'      => 'No delivery history available',
    ],

    'placeholders' => [
        'key'    => 'morning_reminder',
        'search' => 'Search by key or type...',
        'search_user' => 'Search by username or email...',
    ],

];
