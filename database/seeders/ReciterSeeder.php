<?php

namespace Database\Seeders;

use App\Models\Reciter;
use Illuminate\Database\Seeder;

class ReciterSeeder extends Seeder
{
    public function run(): void
    {
        $reciters = [
            [
                'name' => 'Mishary Rashid Alafasy',
                'riwayah' => 'Hafs',
                'language' => 'ar',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Maher Al-Muaiqly',
                'riwayah' => 'Hafs',
                'language' => 'ar',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Abdul Basit Abdus Samad',
                'riwayah' => 'Hafs',
                'language' => 'ar',
                'image' => null,
                'is_active' => true,
            ],
        ];

        foreach ($reciters as $reciter) {
            Reciter::updateOrCreate(
                ['name' => $reciter['name']],
                $reciter
            );
        }
    }
}
