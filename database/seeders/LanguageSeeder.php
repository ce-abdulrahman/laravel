<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = config('languages.supported', []);

        foreach ($languages as $code => $data) {
            Language::updateOrCreate(
                ['code' => $code],
                array_merge($data, ['code' => $code])
            );
        }
    }
}
