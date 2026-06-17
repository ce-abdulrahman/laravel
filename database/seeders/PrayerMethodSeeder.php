<?php

namespace Database\Seeders;

use App\Models\PrayerMethod;
use Illuminate\Database\Seeder;

class PrayerMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'key' => 'muslim_world_league',
                'config' => [
                    'fajr_angle' => 18.0,
                    'isha_angle' => 17.0,
                    'rules' => (object) [],
                    'offsets' => (object) [],
                ],
                'sort_order' => 1,
                'is_enabled' => true,
            ],
            [
                'key' => 'egyptian',
                'config' => [
                    'fajr_angle' => 19.5,
                    'isha_angle' => 17.5,
                    'rules' => (object) [],
                    'offsets' => (object) [],
                ],
                'sort_order' => 2,
                'is_enabled' => true,
            ],
            [
                'key' => 'umm_al_qura',
                'config' => [
                    'fajr_angle' => 18.5,
                    'isha_angle' => null,
                    'rules' => [
                        'isha_delay_minutes' => 90,
                        'isha_delay_ramadan_minutes' => 120,
                    ],
                    'offsets' => (object) [],
                ],
                'sort_order' => 3,
                'is_enabled' => true,
            ],
            [
                'key' => 'isna',
                'config' => [
                    'fajr_angle' => 15.0,
                    'isha_angle' => 15.0,
                    'rules' => (object) [],
                    'offsets' => (object) [],
                ],
                'sort_order' => 4,
                'is_enabled' => true,
            ],
            [
                'key' => 'turkey',
                'config' => [
                    'fajr_angle' => 18.0,
                    'isha_angle' => 17.0,
                    'rules' => [
                        'use_diyanet_offsets' => true,
                    ],
                    'offsets' => (object) [],
                ],
                'sort_order' => 5,
                'is_enabled' => true,
            ],
            [
                'key' => 'kurdistan',
                'config' => [
                    'fajr_angle' => 18.0,
                    'isha_angle' => 17.0,
                    'rules' => [
                        'local_offsets_enabled' => true,
                    ],
                    'offsets' => (object) [],
                ],
                'sort_order' => 6,
                'is_enabled' => true,
            ],
        ];

        foreach ($methods as $m) {
            PrayerMethod::updateOrCreate(
                ['key' => $m['key']],
                [
                    'config' => $m['config'],
                    'sort_order' => $m['sort_order'],
                    'is_enabled' => $m['is_enabled'],
                ]
            );
        }
    }
}
