<?php

return [
    // Page Titles
    'titles' => [
        'index' => 'Translations',
        'create' => 'Add New Translation',
        'edit' => 'Edit Translation',
        'show' => 'Translation Details',
        'form_create' => 'Add Translation Form',
        'form_edit' => 'Edit Translation Form',
        'help' => 'Help',
        'danger_zone' => 'Danger Zone',
    ],

    // Hints
    'hints' => [
        'manage' => 'Manage Quran translations',
        'create_new' => 'Add a new translation for an ayah',
        'default_help' => 'This translation will be set as the default one',
        'active_help' => 'The translation will be shown on the pages',
    ],

    // Actions
    'actions' => [
        'create' => 'Add Translation',
        'create_first' => 'Add first translation',
        'back' => 'Back',
    ],

    // Statistics
    'total_translations' => 'Total Translations',
    'total_languages' => 'Number of Languages',
    'default_translations' => 'Default Translations',
    'translations' => 'Translation',

    // Filters
    'filter_by_language' => 'Filter by Language',
    'filter_by_surah' => 'Filter by Surah',
    'filter_by_translator' => 'Filter by Translator',
    'all_languages' => 'All Languages',
    'all_surahs' => 'All Surahs',
    'all_translators' => 'All Translators',
    'search' => 'Search',
    'search_placeholder' => 'Search in translation content...',

    // Fields
    'fields' => [
        'surah_ayah' => 'Surah & Ayah',
        'language' => 'Language',
        'translator' => 'Translator',
        'content' => 'Translation Content',
        'is_default' => 'Is Default',
        'status' => 'Status',
        'ayah' => 'Ayah',
        'select_ayah' => 'Select Ayah',
        'select_language' => 'Select Language',
        'set_as_default' => 'Set as Default',
        'is_active' => 'Is Active',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'surah' => 'Surah',
        'ayah_number' => 'Ayah Number',
    ],

    // Sections
    'sections' => [
        'ayah_selection' => 'Ayah Selection',
        'translation_details' => 'Translation Details',
        'settings' => 'Settings',
    ],

    // Placeholders
    'placeholders' => [
        'translator' => 'Translator name (Optional)',
        'content' => 'Translation content...',
    ],

    // Ayah
    'ayah' => 'Ayah',
    'selected_ayah' => 'Selected Ayah',
    'original_ayah' => 'Original Ayah Text',
    'view_full_ayah' => 'View Full Ayah',
    'translation' => 'Translation',
    'default' => 'Default',
    'unknown' => 'Unknown',
    'details' => 'Details',
    'other_translations' => 'Other Translations',

    // Messages
    'no_translations_found' => 'No translations found',
    'no_translations_message' => 'No translation found with these filters. Please change filters',
    'delete_confirm_message' => 'Are you sure you want to delete this translation?',
    'delete_title' => 'Delete Translation',
    'delete_warning' => 'Deleting a translation is permanent and cannot be undone.',
    'confirm_delete' => 'Are you sure you want to delete this translation?',
    'messages' => [
        'created' => 'Translation added successfully',
        'updated' => 'Translation updated successfully',
        'deleted' => 'Translation deleted successfully',
        'activated' => 'Translation activated',
        'deactivated' => 'Translation deactivated',
        'set_default' => 'Translation set as default',
        'copied' => 'Translation copied',
    ],

    // Validation
    'validation' => [
        'translation_exists' => 'A translation already exists for this language and translator for this ayah',
    ],

    // Help
    'help' => [
        'step1' => 'Select an ayah to translate',
        'step2' => 'Set language and translator name',
        'step3' => 'Write translation content and save',
    ],

    'copy_translation' => 'Copy Translation',
    'set_as_default' => 'Set as Default Translation',
];
