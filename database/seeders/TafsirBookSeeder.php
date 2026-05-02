<?php

namespace Database\Seeders;

use App\Models\TafsirBook;
use Illuminate\Database\Seeder;

class TafsirBookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            [
                'name' => 'Tafsir Al-Muyassar',
                'author' => 'مجموعة من العلماء',
                'language_code' => 'ar',
                'short_description' => 'Simple Arabic tafsir for general readers.',
                'source' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Tafsir Ibn Kathir',
                'author' => 'ابن كثير',
                'language_code' => 'ar',
                'short_description' => 'Classical tafsir with narrations and scholarly explanations.',
                'source' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Kurdish Simple Tafsir',
                'author' => 'Custom',
                'language_code' => 'ku',
                'short_description' => 'Simple Kurdish explanation for learning mode.',
                'source' => null,
                'is_active' => true,
            ],
        ];

        foreach ($books as $book) {
            TafsirBook::updateOrCreate(
                ['name' => $book['name']],
                $book
            );
        }
    }
}
