<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
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
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => true,
            ]
        );

        \App\Models\AyahTajweedSegment::truncate();

        $this->call([
            LanguageSeeder::class,
            SurahSeeder::class,
            AyahSeeder::class,
            TafsirBookSeeder::class,
            CategoryTajweedRulesSeeder::class,
            TajweedRuleSeeder::class,
            TajweedRuleOfNunSakenSeeder::class,
            TajweedRuleOfMeemSeeder::class,
            TajweedRuleOfMaddSeeder::class,
            TajweedRuleOfRaaSeeder::class,
            TajweedRuleOfQalqalahSeeder::class,
            TajweedRuleOfIdghmAdvancedSeeder::class,
            
            ReciterSeeder::class,
            AudioFileSeeder::class,
            SettingSeeder::class,
            SettingEntrySeeder::class,
            TranslationSeeder::class,
            BannerSeeder::class,
            AdhkarSeeder::class,
            TasbihSeeder::class,
            HadithSeeder::class,
            DailyGoalTemplateSeeder::class,
            AchievementSeeder::class,
            FingerprintSeeder::class,
            CitySeeder::class,
            PrayerMethodSeeder::class,
            WidgetTranslationSeeder::class,
        ]);

    }
}
