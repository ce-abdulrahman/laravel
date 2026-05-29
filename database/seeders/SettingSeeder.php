<?php

namespace Database\Seeders;

use App\Models\Qiraat;
use App\Models\Reciter;
use App\Models\Setting;
use App\Models\TafsirBook;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $defaultTafsirBook = TafsirBook::first();
        $defaultReciter = Reciter::first();
        $defaultQiraah = Qiraat::first();

        if (! $defaultTafsirBook || ! $defaultReciter) {
            $this->command->error('Default tafsir book or reciter not found. Seed TafsirBook and Reciter data first.');
            return;
        }

        Setting::truncate();

        Setting::create([
            'app_name' => 'My Quran',
            'app_logo' => null,
            'default_language' => 'ku',
            'default_tafsir_book_id' => $defaultTafsirBook->id,
            'default_reciter_id' => $defaultReciter->id,
            'default_qiraah_id' => $defaultQiraah?->id,
            'about_text' => 'Quran Memorization App',
            'contact_email' => null,
        ]);
    }
}
