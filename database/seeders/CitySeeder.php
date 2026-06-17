<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            [
                'name' => 'Erbil',
                'lat' => 36.1912,
                'lng' => 44.0091,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'name' => 'Sulaymaniyah',
                'lat' => 35.5619,
                'lng' => 45.4375,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'name' => 'Duhok',
                'lat' => 36.8601,
                'lng' => 42.9961,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'name' => 'Kirkuk',
                'lat' => 35.4681,
                'lng' => 44.3922,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'name' => 'Halabja',
                'lat' => 35.1778,
                'lng' => 45.9861,
                'timezone' => 'Asia/Baghdad',
            ],
        ];

        foreach ($cities as $city) {
            City::query()->updateOrCreate(
                ['name' => $city['name']],
                $city
            );
        }
    }
}
