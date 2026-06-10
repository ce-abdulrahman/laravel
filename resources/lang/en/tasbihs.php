<?php
// resources/lang/en/tasbihs.php

return [
    'titles' => [
        'index'  => 'Tasbih (Dhikr Counter)',
        'create' => 'Add New Tasbih',
        'edit'   => 'Edit Tasbih',
        'show'   => 'Tasbih Details',
    ],

    'hints' => [
        'index'  => 'Manage the Dhikr and Tasbih entries displayed on the mobile app\'s counter screen.',
        'create' => 'Add a new Tasbih entry with its Arabic text and the target repetition count.',
        'edit'   => 'Update the information for this Tasbih entry.',
        'show'   => 'View the full details of this Tasbih entry.',
    ],

    'fields' => [
        'name'      => 'Dhikr / Tasbih Name',
        'target'    => 'Target Count (repetitions)',
        'is_active' => 'Active (shown on mobile)',
    ],

    'placeholders' => [
        'name'   => 'e.g. سُبْحَانَ اللهِ or Alhamdulillah...',
        'target' => 'e.g. 33',
        'search' => 'Search Tasbih...',
    ],

    'sections' => [
        'content' => 'Tasbih Content',
    ],

    'actions' => [
        'create'       => 'Add Tasbih',
        'create_first' => 'Create First Tasbih',
        'edit'         => 'Edit',
        'view'         => 'View Details',
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
        'created'        => 'Tasbih created successfully.',
        'updated'        => 'Tasbih updated successfully.',
        'deleted'        => 'Tasbih deleted successfully.',
        'confirm_delete' => 'Are you sure you want to delete this Tasbih entry?',
    ],

    'table' => [
        'number'  => '#',
        'name'    => 'Dhikr / Tasbih Name',
        'target'  => 'Target',
        'status'  => 'Status',
        'actions' => 'Actions',
    ],

    'pagination' => [
        'showing' => 'Showing',
        'to'      => 'to',
        'of'      => 'of',
        'entries' => 'tasbihs',
        'total'   => 'Total:',
    ],

    'empty' => [
        'title'   => 'No Tasbih Found',
        'message' => 'No Tasbih or Dhikr entries found in the database.',
    ],

    'times' => ':count times',
];
