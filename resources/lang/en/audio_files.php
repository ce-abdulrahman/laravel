<?php

return [
    'titles' => [
        'index' => 'Audio Files',
        'create' => 'Upload Audio File',
        'edit' => 'Edit Audio File',
    ],

    'hints' => [
        'manage' => 'Manage Quran audio files',
        'upload_new' => 'Upload a new audio file',
        'url_help' => 'Direct link to the audio file (MP3, WAV, OGG)',
    ],

    'actions' => [
        'upload' => 'Upload File',
        'upload_first' => 'Upload first file',
        'upload_for_reciter' => 'Upload for this reciter',
        'back' => 'Back',
    ],

    'sections' => [
        'basic_info' => 'Basic Information',
        'audio_settings' => 'Audio Settings',
        'source_settings' => 'Source Settings',
    ],

    'fields' => [
        'reciter' => 'Reciter',
        'surah' => 'Surah',
        'ayah' => 'Ayah',
        'duration' => 'Duration',
        'quality' => 'Quality',
        'source_type' => 'Source Type',
        'url' => 'URL',
        'is_active' => 'Is Active',
        'surah_ayah' => 'Surah & Ayah',
        'status' => 'Status',
    ],

    'source_types' => [
        'upload' => 'Upload',
        'url' => 'External URL',
    ],

    'select_reciter' => 'Select Reciter',
    'select_surah' => 'Select Surah',
    'select_ayah' => 'Select Ayah',
    'select_quality' => 'Select Quality',
    'loading_ayahs' => 'Loading ayahs...',

    'drag_drop' => 'Drag and drop file here',
    'or' => 'or',
    'browse_files' => 'Browse files',
    'supported_formats' => 'Supported formats: MP3, WAV, OGG (Max 100MB)',
    'preview' => 'Preview',
    'seconds' => 'seconds',

    'total_files' => 'Total Files',
    'total_duration' => 'Total Duration',
    'reciters_with_audio' => 'Reciters with Audio',
    'full_surahs' => 'Full Surahs',

    'filter_by_reciter' => 'Filter by Reciter',
    'filter_by_surah' => 'Filter by Surah',
    'filter_by_type' => 'Filter by Type',
    'all_reciters' => 'All Reciters',
    'all_surahs' => 'All Surahs',
    'all_types' => 'All Types',
    'full_surah' => 'Full Surah',
    'single_ayah' => 'Single Ayah',

    'ayah' => 'Ayah',
    'no_files_found' => 'No audio files found',

    'messages' => [
        'created' => 'Audio file added successfully',
        'updated' => 'Audio file updated successfully',
        'deleted' => 'Audio file deleted successfully',
        'activated' => 'Audio file activated',
        'deactivated' => 'Audio file deactivated',
        'upload_success' => 'File uploaded successfully',
        'upload_error' => 'Error occurred while uploading the file',
        'invalid_file_type' => 'File type not supported. Please upload MP3, WAV, or OGG',
        'file_too_large' => 'File size is too large. Maximum size is 100MB',
        'confirm_delete' => 'Are you sure you want to delete this audio file?',
    ],

    'validation' => [
        'audio_exists' => 'An audio file is already registered for this reciter and surah/ayah',
    ],

    'placeholders' => [
        'duration' => 'In seconds',
    ],
];
