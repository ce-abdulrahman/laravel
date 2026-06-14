<?php

return [
    'titles' => [
        'index' => 'Tajweed Segments',
        'create' => 'Add Tajweed Segment',
        'edit' => 'Edit Segment',
        'form_create' => 'Add Segment Form',
        'form_edit' => 'Edit Segment Form',
        'danger_zone' => 'Danger Zone',
    ],

    'hints' => [
        'manage' => 'Manage Tajweed segments',
        'create_new' => 'Add a new Tajweed segment',
    ],

    'actions' => [
        'create' => 'Add Segment',
        'create_first' => 'Add first segment',
        'back' => 'Back',
        'add_segment' => 'Add Segment',
        'add_first' => 'Add first segment',
    ],

    'total_segments' => 'Total Segments',
    'total_rules_used' => 'Rules Used',
    'ayahs_with_tajweed' => 'Ayahs with Tajweed',

    'filter_by_rule' => 'Filter by Rule',
    'filter_by_surah' => 'Filter by Surah',
    'filter_by_category' => 'Filter by Category',
    'filter_by_ayah' => 'Filter by Ayah Number',
    'all_rules' => 'All Rules',
    'all_surahs' => 'All Surahs',
    'all_categories' => 'All Categories',
    'search' => 'Search',
    'search_placeholder' => 'Search by matched text...',

    'fields' => [
        'tajweed_rule' => 'Tajweed Rule',
        'ayah' => 'Ayah',
        'surah_ayah' => 'Surah & Ayah',
        'rule' => 'Rule',
        'matched_text' => 'Matched Text',
        'text_segment' => 'Matched Text', // Deprecated alias
        'start_index' => 'Start Index',
        'end_index' => 'End Index',
        'metadata' => 'Metadata (JSON)',
        'note' => 'Note',
    ],

    'sections' => [
        'selection' => 'Selection',
        'segment_details' => 'Segment Details',
    ],

    'placeholders' => [
        'text_segment' => 'Matched text in Arabic...',
        'matched_text' => 'Matched text in Arabic...',
        'metadata' => '{"duration": "2_harakat"}',
        'note' => 'Additional note...',
    ],

    'select_rule' => 'Select Rule',
    'select_ayah' => 'Select Ayah',
    'selected_ayah' => 'Selected Ayah',
    'ayah' => 'Ayah',
    'full_ayah' => 'Full Ayah',
    'segment_details' => 'Segment Details',
    'rule_info' => 'Rule Information',
    'view_full_rule' => 'View Full Rule',
    'other_segments' => 'Other Segments',
    'metadata' => 'Metadata',

    'no_segments_found' => 'No segments found',

    'messages' => [
        'created' => 'Segment added successfully',
        'created_batch' => 'Segments added successfully',
        'updated' => 'Segment updated successfully',
        'deleted' => 'Segment deleted successfully',
        'delete_title' => 'Delete Segment',
        'delete_warning' => 'Deleting a segment is permanent and cannot be undone.',
        'confirm_delete' => 'Are you sure you want to delete this segment?',
        'rebuild_title' => 'Rebuild Segments',
        'rebuild_warning' => 'Rebuilding segments will clear ALL existing records first and import new ones. This cannot be undone.',
        'confirm_rebuild' => 'Are you sure you want to delete all segments and rebuild from the uploaded file?',
    ],
];
