<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => true,
            ]
        );

        $this->call([
            SurahSeeder::class,
            AyahSeeder::class,
            TafsirBookSeeder::class,
            CategoryTajweedRulesSeeder::class,
            TajweedRuleSeeder::class,
            TajweedSegmentSeeder::class,
            ReciterSeeder::class,
            AudioFileSeeder::class,
            SettingSeeder::class,
            SettingEntrySeeder::class,
            TranslationSeeder::class,
            BannerSeeder::class,
            AdhkarSeeder::class,
            TasbihSeeder::class,
            HadithSeeder::class,
        ]);
    }
}
