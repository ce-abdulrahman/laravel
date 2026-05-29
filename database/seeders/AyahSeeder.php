<?php

namespace Database\Seeders;

use App\Models\Ayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AyahSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/ayahs.php');

        if (! File::exists($path)) {
            $this->command->error('ayahs.php file not found in database/data/');
            return;
        }

        $ayahs = require $path;

        if (! is_array($ayahs) || empty($ayahs)) {
            $this->command->error('ayahs.php is empty or invalid.');
            return;
        }

        DB::transaction(function () use ($ayahs) {
            Ayah::truncate();

            foreach (array_chunk($ayahs, 500) as $chunk) {
                Ayah::insert($chunk);
            }
        });

        $this->command->info('Ayahs seeded successfully.');
    }
}
