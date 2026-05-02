<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'My Quran', 'group' => 'general'],
            ['key' => 'app_description', 'value' => 'Quran Memorization App', 'group' => 'general'],
            ['key' => 'default_reciter', 'value' => '1', 'group' => 'audio'],
            ['key' => 'default_translation', 'value' => 'ku', 'group' => 'reading'],
            ['key' => 'max_daily_ayahs', 'value' => '10', 'group' => 'memorization'],
            ['key' => 'review_after_days', 'value' => '7', 'group' => 'memorization'],
            ['key' => 'enable_notifications', 'value' => 'true', 'group' => 'notifications'],
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'system'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
