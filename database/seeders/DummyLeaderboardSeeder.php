<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ReadingHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyLeaderboardSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Darya', 'Zana', 'Bayan', 'Saman', 'Lana', 'Aram', 'Hanna', 'Kardan', 'Shad', 'Hevi'];
        
        foreach ($names as $index => $name) {
            $email = strtolower($name) . '@example.com';
            
            // Create user
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'status' => true,
                    'points_total' => (10 - $index) * 25 + rand(5, 15),
                    'streak_days' => rand(2, 12),
                    'longest_streak' => rand(12, 20),
                    'last_read_date' => Carbon::today()->subDays(rand(0, 1)),
                ]
            );

            // Seed reading history for the last 7 days
            for ($d = 0; $d < 7; $d++) {
                $ayahsCount = rand(3, 10);
                for ($a = 0; $a < $ayahsCount; $a++) {
                    ReadingHistory::create([
                        'user_id' => $user->id,
                        'ayah_id' => rand(1, 100), // Safely within existing 669 ayahs
                        'last_read_at' => Carbon::now()->subDays($d)->subMinutes(rand(10, 300)),
                        'seconds_spent' => rand(5, 60),
                    ]);
                }
            }
        }
    }
}
