<?php
// resources/lang/en/adhkar_categories.php

return [
    'titles' => [
        'index'  => 'Adhkar Categories',
        'create' => 'Create Adhkar Category',
        'edit'   => 'Edit Adhkar Category',
        'show'   => 'Adhkar Category Details',
    ],

    'hints' => [
        'index'  => 'Manage Adhkar (Dhikr) categories such as Morning, Evening, After Prayer, and Sleep remembrances.',
        'create' => 'Add a new Adhkar category to organize Dhikr entries.',
        'edit'   => 'Update the information for this Adhkar category.',
        'show'   => 'View full details and the Dhikr entries belonging to this category.',
    ],

    'fields' => [
        'name_ku'   => 'Kurdish Name',
        'name_ar'   => 'Arabic Name',
        'name_en'   => 'English Name',
        'icon'      => 'Widget Icon (for mobile)',
        'icon_hint' => 'This icon is used on the Adhkar page of the mobile app.',
        'order'     => 'Display Order',
        'is_active' => 'Active (shown on mobile)',
    ],

    'placeholders' => [
        'name_ku'    => 'Enter category name in Kurdish...',
        'name_ar'    => 'Enter category name in Arabic...',
        'name_en'    => 'Enter category name in English...',
        'icon'       => 'e.g. wb_sunny_rounded or dark_mode_outlined...',
        'search'     => 'Search categories...',
    ],

    'sections' => [
        'info'          => 'Category Information',
        'configuration' => 'Settings & Design',
    ],

    'actions' => [
        'create'       => 'Add Category',
        'create_first' => 'Create First Category',
        'edit'         => 'Edit',
        'view'         => 'View Details',
        'view_adhkars' => 'View Adhkars',
        'delete'       => 'Delete',
        'save'         => 'Save',
        'update'       => 'Update',
        'back'         => 'Back to List',
        'cancel'       => 'Cancel',
        'refresh'      => 'Refresh',
    ],

    'status' => [
        'active'   => 'Active',
        'inactive' => 'Inactive',
    ],

    'messages' => [
        'created'        => 'Adhkar category created successfully.',
        'updated'        => 'Adhkar category updated successfully.',
        'deleted'        => 'Adhkar category deleted successfully.',
        'confirm_delete' => 'Are you sure? All Adhkar entries under this category will also be deleted!',
    ],

    'table' => [
        'order'   => 'Order',
        'name_ku' => 'Kurdish Name',
        'name_ar' => 'Arabic Name',
        'name_en' => 'English Name',
        'icon'    => 'Icon',
        'status'  => 'Status',
        'actions' => 'Actions',
        'adhkars_count' => 'Adhkars',
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
        'message' => 'No Adhkar categories were found in the database.',
    ],

    'no_adhkars' => 'No Adhkar entries in this category.',
    'adhkars_count' => ':count Adhkar entries',
];
