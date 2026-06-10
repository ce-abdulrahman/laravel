<?php

return [
    'titles' => [
        'index' => 'Surahs',
        'create' => 'Create New Surah',
        'edit' => 'Edit Surah',
        'show' => 'Surah Details',
        'details' => 'Information',
        'quick_actions' => 'Quick Actions',
        'form_create' => 'Create Form',
        'form_edit' => 'Edit Form',
        'help' => 'Help',
        'danger_zone' => 'Danger Zone',
    ],

    'fields' => [
        'number' => 'Number',
        'name_ar' => 'Arabic Name',
        'name_ku' => 'Kurdish Name',
        'name_en' => 'English Name',
        'revelation_type' => 'Revelation Place',
        'ayah_count' => 'Ayahs Count',
        'is_active' => 'Active',
        'page_start' => 'Start Page',
        'page_end' => 'End Page',
        'juz_start' => 'Start Juz',
        'juz_end' => 'End Juz',
        'description' => 'Description',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'page_range' => 'Page Range',
        'juz_range' => 'Juz Range',
        'juz' => 'Juz',
    ],

    'revelation_types' => [
        'meccan' => 'Meccan',
        'medinan' => 'Medinan',
        'Meccan' => 'Meccan',
        'Medinan' => 'Medinan',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    'actions' => [
        'create' => 'Create Surah',
        'create_first' => 'Create First Surah',
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'back' => 'Back',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'view_ayahs' => 'View Ayahs',
        'view_tafsir' => 'View Tafsir',
        'listen' => 'Listen',
        'start_memorization' => 'Start Memorization',
    ],

    'sections' => [
        'basic_info' => 'Basic Information',
        'translations' => 'Translations',
        'classification' => 'Classification',
        'position' => 'Position',
    ],

    'hints' => [
        'manage' => 'Manage all Surahs of the Holy Quran',
        'create_new' => 'Add a new Surah to the database',
        'edit_existing' => 'Update this Surah\'s information',
        'view_details' => 'View complete details of the Surah',
        'updated_at' => 'Last updated',
    ],

    'messages' => [
        'confirm_delete' => 'Are you sure you want to delete this Surah?',
        'delete_title' => 'Delete Surah',
        'delete_warning' => 'This action cannot be undone. All Ayahs and related data for this Surah will be permanently deleted.',
        'cannot_undo' => 'This action cannot be undone!',
        'no_surahs_found' => 'No Surahs found',
        'created' => 'Surah created successfully',
        'updated' => 'Surah updated successfully',
        'deleted' => 'Surah deleted successfully',
    ],

    'help' => [
        'step1' => 'Enter the Surah number and name',
        'step2' => 'Select revelation type and number of Ayahs',
        'step3' => 'Specify pages and Juz (optional)',
    ],

    'placeholders' => [
        'description' => 'Write a brief description about the Surah...',
    ],

    'search_placeholder' => 'Search by Surah name...',
];
