<?php
// resources/lang/en/banners.php

return [
    'titles' => [
        'index'  => 'Home Banners',
        'create' => 'Create New Banner',
        'edit'   => 'Edit Banner',
        'show'   => 'Banner Details',
    ],

    'hints' => [
        'index'  => 'Manage the Quranic verses and banners displayed on the mobile home screen.',
        'create' => 'Add a new banner to be displayed on the mobile home screen.',
        'edit'   => 'Update the information for this banner.',
        'show'   => 'View full details of this banner.',
    ],

    'fields' => [
        'title_arabic'  => 'Arabic Text (Verse)',
        'verse'         => 'Translation / Meaning',
        'source'        => 'Source',
        'surah'         => 'Surah',
        'ayah_number'   => 'Ayah Number',
        'order'         => 'Display Order',
        'is_active'     => 'Active (shown on mobile)',
        'linked_to'     => 'Linked To',
        'not_linked'    => 'Not Linked',
    ],

    'placeholders' => [
        'title_arabic' => 'Enter the Arabic verse...',
        'verse'        => 'Enter the Kurdish/English translation or meaning...',
        'source'       => 'e.g. — Al-Isra 17:9',
        'search'       => 'Search banners...',
        'no_surah'     => '-- No Surah --',
        'ayah_number'  => 'Enter ayah number...',
    ],

    'sections' => [
        'content'       => 'Banner Content',
        'linking'       => 'Link to Surah & Ayah (Optional)',
        'linking_hint'  => 'If set, tapping the banner on mobile will navigate directly to that ayah.',
        'configuration' => 'Settings & Display Order',
    ],

    'actions' => [
        'create'        => 'Add Banner',
        'create_first'  => 'Create First Banner',
        'edit'          => 'Edit',
        'view'          => 'View Details',
        'delete'        => 'Delete',
        'save'          => 'Save',
        'update'        => 'Update',
        'back'          => 'Back to List',
        'cancel'        => 'Cancel',
        'search'        => 'Search',
        'refresh'       => 'Refresh',
    ],

    'status' => [
        'active'   => 'Active',
        'inactive' => 'Inactive',
    ],

    'messages' => [
        'created'        => 'Banner created successfully.',
        'updated'        => 'Banner updated successfully.',
        'deleted'        => 'Banner deleted successfully.',
        'confirm_delete' => 'Are you sure you want to delete this banner?',
    ],

    'table' => [
        'order'   => 'Order',
        'arabic'  => 'Arabic Text (Verse)',
        'verse'   => 'Translation / Meaning',
        'source'  => 'Source',
        'linked'  => 'Linked To',
        'status'  => 'Status',
        'actions' => 'Actions',
    ],

    'pagination' => [
        'showing' => 'Showing',
        'to'      => 'to',
        'of'      => 'of',
        'entries' => 'banners',
        'total'   => 'Total:',
    ],

    'empty' => [
        'title'   => 'No Banners Found',
        'message' => 'No banners were found in the database.',
    ],

    'no_banners' => 'No banners found',
    'surah_link' => 'Surah :name (Ayah :number)',
];
