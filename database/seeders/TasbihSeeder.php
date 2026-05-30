<?php

namespace Database\Seeders;

use App\Models\Tasbih;
use Illuminate\Database\Seeder;

class TasbihSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dhikrs = [
            [
                'name' => 'سُبْحَانَ اللهِ',
                'target' => 33,
                'is_active' => true,
            ],
            [
                'name' => 'الْحَمْدُ للهِ',
                'target' => 33,
                'is_active' => true,
            ],
            [
                'name' => 'اللهُ أَكْبَرُ',
                'target' => 33,
                'is_active' => true,
            ],
            [
                'name' => 'لَا إِلَهَ إِلَّا اللهُ، وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ، وَهُوَ عَلَى كُلِّ شَيْءٍ قَدِيرٌ',
                'target' => 100,
                'is_active' => true,
            ],
        ];

        foreach ($dhikrs as $dhikr) {
            Tasbih::updateOrCreate(
                ['name' => $dhikr['name']],
                $dhikr
            );
        }
    }
}
