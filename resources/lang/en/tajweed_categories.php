<?php
// resources/lang/en/tajweed_categories.php

return [
    'titles' => [
        'index'  => 'Tajweed Rule Categories',
        'create' => 'Create Tajweed Category',
        'edit'   => 'Edit Tajweed Category',
        'show'   => 'Tajweed Category Details',
    ],

    'hints' => [
        'index'  => 'Manage and organize Tajweed rules into categories for easier learning and navigation.',
        'create' => 'Add a new Tajweed category to group related pronunciation rules.',
        'edit'   => 'Update the information for this Tajweed category.',
        'show'   => 'View all Tajweed rules belonging to this category.',
    ],

    'fields' => [
        'name'           => 'Name (English)',
        'name_ku'        => 'Kurdish Name',
        'name_ar'        => 'Arabic Name',
        'description'    => 'Description (English)',
        'description_ku' => 'Kurdish Description',
        'description_ar' => 'Arabic Description',
        'order'          => 'Display Order',
        'is_active'      => 'Active',
        'slug'           => 'Slug',
    ],

    'placeholders' => [
        'name'           => 'Enter category name in English...',
        'name_ku'        => 'Enter category name in Kurdish...',
        'name_ar'        => 'Enter category name in Arabic...',
        'description'    => 'Enter a description in English...',
        'description_ku' => 'Enter a description in Kurdish...',
        'description_ar' => 'Enter a description in Arabic...',
        'search'         => 'Search categories...',
    ],

    'sections' => [
        'basic_info'  => 'Basic Information',
        'description' => 'Descriptions',
        'settings'    => 'Settings',
    ],

    'actions' => [
        'create'       => 'Add Category',
        'create_first' => 'Create First Category',
        'edit'         => 'Edit',
        'view'         => 'View Details',
        'view_rules'   => 'View Rules',
        'delete'       => 'Delete',
        'save'         => 'Save',
        'update'       => 'Update Category',
        'back'         => 'Back to List',
        'cancel'       => 'Cancel',
    ],

    'table' => [
        'name'        => 'Name',
        'rules_count' => 'Rules',
        'order'       => 'Order',
        'status'      => 'Status',
        'actions'     => 'Actions',
    ],

    'messages' => [
        'created'        => 'Tajweed category created successfully.',
        'updated'        => 'Tajweed category updated successfully.',
        'deleted'        => 'Tajweed category deleted successfully.',
        'has_rules'      => 'Cannot delete category: it still has Tajweed rules assigned.',
        'confirm_delete' => 'Are you sure you want to delete this category? Rules will not be deleted but will lose their category.',
    ],

    'empty' => [
        'title'   => 'No Categories Found',
        'message' => 'No Tajweed categories exist yet. Create one to get started.',
    ],

    'stats' => [
        'total'    => 'Total Categories',
        'active'   => 'Active',
        'inactive' => 'Inactive',
    ],

    'no_rules' => 'No rules in this category yet.',
];
