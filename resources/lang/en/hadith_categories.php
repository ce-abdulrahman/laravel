<?php
// resources/lang/en/hadith_categories.php

return [
    'titles' => [
        'index'  => 'Hadith Categories',
        'create' => 'Create Hadith Category',
        'edit'   => 'Edit Hadith Category',
        'show'   => 'Hadith Category Details',
    ],

    'hints' => [
        'index'  => 'Manage Hadith categories such as Manners, Beliefs, and Acts of Worship.',
        'create' => 'Add a new Hadith category to organize Hadith entries.',
        'edit'   => 'Update the information for this Hadith category.',
        'show'   => 'View full details and the Hadith entries belonging to this category.',
    ],

    'fields' => [
        'name_ku'   => 'Kurdish Name',
        'name_ar'   => 'Arabic Name',
        'name_en'   => 'English Name',
        'icon'      => 'Widget Icon (for mobile)',
        'icon_hint' => 'This icon is used on the Hadith page of the mobile app.',
        'order'     => 'Display Order',
        'is_active' => 'Active (shown on mobile)',
    ],

    'placeholders' => [
        'name_ku' => 'Enter category name in Kurdish...',
        'name_ar' => 'Enter category name in Arabic...',
        'name_en' => 'Enter category name in English...',
        'icon'    => 'e.g. auto_stories, library_books, menu_book...',
        'search'  => 'Search categories...',
    ],

    'sections' => [
        'info'          => 'Category Information',
        'configuration' => 'Settings & Design',
    ],

    'actions' => [
        'create'        => 'Add Category',
        'create_first'  => 'Create First Category',
        'edit'          => 'Edit',
        'view'          => 'View Details',
        'view_hadiths'  => 'View Hadiths',
        'delete'        => 'Delete',
        'save'          => 'Save',
        'update'        => 'Update',
        'back'          => 'Back to List',
        'cancel'        => 'Cancel',
        'refresh'       => 'Refresh',
    ],

    'status' => [
        'active'   => 'Active',
        'inactive' => 'Inactive',
    ],

    'messages' => [
        'created'        => 'Hadith category created successfully.',
        'updated'        => 'Hadith category updated successfully.',
        'deleted'        => 'Hadith category deleted successfully.',
        'confirm_delete' => 'Are you sure? All Hadith entries under this category will also be deleted!',
    ],

    'table' => [
        'order'          => 'Order',
        'name_ku'        => 'Kurdish Name',
        'name_ar'        => 'Arabic Name',
        'name_en'        => 'English Name',
        'icon'           => 'Icon',
        'status'         => 'Status',
        'actions'        => 'Actions',
        'hadiths_count'  => 'Hadiths',
    ],

    'pagination' => [
        'showing' => 'Showing',
        'to'      => 'to',
        'of'      => 'of',
        'entries' => 'categories',
        'total'   => 'Total:',
    ],

    'empty' => [
        'title'   => 'No Categories Found',
        'message' => 'No Hadith categories were found in the database.',
    ],

    'no_hadiths'    => 'No Hadith entries in this category.',
    'hadiths_count' => ':count Hadith entries',
];
