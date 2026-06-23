<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Safe seed migration: upserts the 21 Kurdish cities used in the prayer_times CSV.
 * Only inserts rows where the city name does not already exist.
 * Never overwrites existing lat/lng/timezone values.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $cities = [
            // Cities actually present in prayer_times.csv
            ['name' => 'Erbil',        'lat' => 36.1901,  'lng' => 44.0091,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Duhok',        'lat' => 36.8668,  'lng' => 42.9506,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Zakho',        'lat' => 37.1440,  'lng' => 42.6876,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Sulaymaniyah', 'lat' => 35.5600,  'lng' => 45.4350,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Halabja',      'lat' => 35.1787,  'lng' => 45.9862,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Chamchamal',   'lat' => 35.5264,  'lng' => 44.8367,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Ranya',        'lat' => 36.2553,  'lng' => 44.8781,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Akre',         'lat' => 36.7436,  'lng' => 43.8841,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Penjwen',      'lat' => 35.6248,  'lng' => 45.9436,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Darbandikhan', 'lat' => 35.1103,  'lng' => 45.6964,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Dokan',        'lat' => 35.9500,  'lng' => 44.9600,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Kifri',        'lat' => 34.6880,  'lng' => 44.9740,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Kalar',        'lat' => 34.6314,  'lng' => 45.3228,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Khanaqin',     'lat' => 34.3379,  'lng' => 45.3705,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Kirkuk',       'lat' => 35.4681,  'lng' => 44.3922,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Koysinjaq',    'lat' => 36.0866,  'lng' => 44.6315,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Makhmur',      'lat' => 35.7706,  'lng' => 43.5843,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Qaladiza',     'lat' => 36.1781,  'lng' => 45.1240,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Qasre',        'lat' => 36.8500,  'lng' => 44.5000,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Gokhlan',      'lat' => 37.0000,  'lng' => 43.5000,  'timezone' => 'Asia/Baghdad'],
            ['name' => 'Tuz Khurma',   'lat' => 34.8871,  'lng' => 44.6390,  'timezone' => 'Asia/Baghdad'],
        ];

        $now = now();

        foreach ($cities as $city) {
            // Only insert if city name doesn't already exist — never overwrite
            DB::table('cities')->insertOrIgnore([
                'name'       => $city['name'],
                'lat'        => $city['lat'],
                'lng'        => $city['lng'],
                'timezone'   => $city['timezone'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     * We do NOT delete cities on rollback — cities are shared data.
     */
    public function down(): void
    {
        // Intentionally left empty — reversing city inserts is destructive
    }
};
