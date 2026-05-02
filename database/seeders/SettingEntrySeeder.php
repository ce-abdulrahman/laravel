<?php

namespace Database\Seeders;

use App\Models\SettingEntry;
use Illuminate\Database\Seeder;

class SettingEntrySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'font_size' => '20',
            'theme_mode' => 'system',
        ];

        foreach ($defaults as $key => $value) {
            SettingEntry::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
