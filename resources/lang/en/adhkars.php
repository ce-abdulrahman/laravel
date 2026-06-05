<?php
// resources/lang/en/adhkars.php

return [
    'titles' => [
        'index'  => 'Adhkar (Dhikr) List',
        'create' => 'Add New Dhikr',
        'edit'   => 'Edit Dhikr',
        'show'   => 'Dhikr Details',
    ],

    'hints' => [
        'index'  => 'Manage all Dhikr and Du\'a entries. Filter by category to focus on a specific group.',
        'create' => 'Add a new Dhikr entry with its Arabic text, Kurdish translation, and repetition count.',
        'edit'   => 'Update the information for this Dhikr entry.',
        'show'   => 'View the full details of this Dhikr entry.',
    ],

    'fields' => [
        'category'      => 'Category',
        'arabic_text'   => 'Arabic Text (Dhikr)',
        'translation_ku'=> 'Kurdish Translation',
        'translation_en'=> 'English Translation',
        'count'         => 'Repetition Count (times)',
        'source'        => 'Hadith Source',
        'description'   => 'Virtue & Reward (Kurdish)',
        'order'         => 'Display Order (within category)',
    ],

    'placeholders' => [
        'category'       => '-- Select Category --',
        'all_categories' => '-- All Categories --',
        'arabic_text'    => 'Enter the Dhikr text in Arabic...',
        'translation_ku' => 'Enter the Kurdish meaning of this Dhikr...',
        'translation_en' => 'Enter the English meaning of this Dhikr...',
        'source'         => 'e.g. Sahih Bukhari, Hisn al-Muslim...',
        'description'    => 'Enter the virtues and rewards of this Dhikr...',
        'search'         => 'Search by Arabic text, Kurdish, or source...',
    ],

    'sections' => [
        'content' => 'Dhikr Content',
        'meta'    => 'Source & Virtue Information',
    ],

    'actions' => [
        'create'       => 'Add Dhikr',
        'create_first' => 'Create First Dhikr',
        'edit'         => 'Edit',
        'view'         => 'View Details',
        'delete'       => 'Delete',
        'save'         => 'Save',
        'update'       => 'Update',
        'back'         => 'Back to List',
        'cancel'       => 'Cancel',
        'search'       => 'Search',
        'refresh'      => 'Refresh',
        'categories'   => 'Adhkar Categories',
    ],

    'messages' => [
        'created'        => 'Dhikr entry created successfully.',
        'updated'        => 'Dhikr entry updated successfully.',
        'deleted'        => 'Dhikr entry deleted successfully.',
        'confirm_delete' => 'Are you sure you want to delete this Dhikr entry?',
    ],

    'table' => [
        'category'   => 'Category',
        'order'      => 'Order',
        'arabic'     => 'Arabic Text',
        'kurdish'    => 'Kurdish (Meaning)',
        'count'      => 'Times',
        'source'     => 'Source',
        'actions'    => 'Actions',
    ],

    'pagination' => [
        'showing' => 'Showing',
        'to'      => 'to',
        'of'      => 'of',
        'entries' => 'entries',
        'total'   => 'Total:',
    ],

    'empty' => [
        'title'   => 'No Dhikr Found',
        'message' => 'No Dhikr entries found in this section.',
    ],

    'count_label'    => ':count×',
    'no_translation' => 'No translation available.',
];
