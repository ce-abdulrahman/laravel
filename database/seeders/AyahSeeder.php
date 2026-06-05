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
        $path = database_path('data/ayahs.json');

        if (! File::exists($path)) {
            $this->command->error('ayahs.json file not found in database/data/');
            return;
        }

        $json = File::get($path);
        $ayahs = json_decode($json, true);

        if (! is_array($ayahs) || empty($ayahs)) {
            $this->command->error('ayahs.json is empty or invalid.');
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
