<?php
// resources/lang/en/hadiths.php

return [
    'titles' => [
        'index'  => 'Hadiths',
        'create' => 'Create Hadith',
        'edit'   => 'Edit Hadith',
        'show'   => 'Hadith Details',
    ],

    'hints' => [
        'index'  => 'Manage Hadith entries, search by narrator, text, source, or filter by category.',
        'create' => 'Add a new Hadith with its Arabic text, translation, narrator, source, and explanation.',
        'edit'   => 'Update the information, translation, or explanation of this Hadith.',
        'show'   => 'View the full details of this Hadith, including narrator chain, Arabic text, translations, and explanations.',
    ],

    'fields' => [
        'category_id'     => 'Hadith Category',
        'arabic_text'     => 'Arabic Text (Hadith)',
        'translation_ku'  => 'Kurdish Translation',
        'translation_en'  => 'English Translation',
        'narrator'        => 'Narrator',
        'source'          => 'Source',
        'explanation_ku'  => 'Kurdish Explanation',
        'explanation_en'  => 'English Explanation',
        'order'           => 'Display Order',
        'is_active'       => 'Active (shown on mobile)',
    ],

    'placeholders' => [
        'category_id'     => 'Select a category...',
        'arabic_text'     => 'Write the Hadith text in Arabic...',
        'translation_ku'  => 'Write Kurdish translation of the Hadith...',
        'translation_en'  => 'Write English translation of the Hadith...',
        'narrator'        => 'e.g., Narrated by Abu Hurayrah...',
        'source'          => 'e.g., Sahih al-Bukhari, Hadith 123...',
        'explanation_ku'  => 'Enter Kurdish explanation/commentary here...',
        'explanation_en'  => 'Enter English explanation/commentary here...',
        'search'          => 'Search Hadiths...',
    ],

    'sections' => [
        'content'         => 'Hadith Content',
        'meta'            => 'Source & Metadata',
        'explanations'    => 'Explanations & Commentary',
    ],

    'actions' => [
        'create'          => 'Add Hadith',
        'create_first'    => 'Create First Hadith',
        'edit'            => 'Edit',
        'view'            => 'View Details',
        'delete'          => 'Delete',
        'save'            => 'Save',
        'update'          => 'Update',
        'back'            => 'Back to List',
        'cancel'          => 'Cancel',
        'refresh'         => 'Refresh',
    ],

    'status' => [
        'active'          => 'Active',
        'inactive'        => 'Inactive',
    ],

    'messages' => [
        'created'         => 'Hadith created successfully.',
        'updated'         => 'Hadith updated successfully.',
        'deleted'         => 'Hadith deleted successfully.',
        'confirm_delete'  => 'Are you sure you want to delete this Hadith?',
    ],

    'table' => [
        'order'           => 'Order',
        'category'        => 'Category',
        'narrator'        => 'Narrator',
        'arabic_text'     => 'Arabic Text',
        'translation_ku'  => 'Kurdish Translation',
        'source'          => 'Source',
        'status'          => 'Status',
        'actions'         => 'Actions',
    ],

    'pagination' => [
        'showing'         => 'Showing',
        'to'              => 'to',
        'of'              => 'of',
        'entries'         => 'Hadiths',
        'total'           => 'Total:',
    ],

    'empty' => [
        'title'           => 'No Hadiths Found',
        'message'         => 'No Hadith entries were found matching your criteria.',
    ],
];
