<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Jobs\DailyLeaderboardJob;
use App\Jobs\WeeklyLeaderboardJob;
use App\Jobs\MonthlyLeaderboardJob;
use App\Jobs\AllTimeLeaderboardJob;

Schedule::job(new DailyLeaderboardJob)->dailyAt('00:00');
Schedule::job(new WeeklyLeaderboardJob)->weekly();
Schedule::job(new MonthlyLeaderboardJob)->monthly();
Schedule::job(new AllTimeLeaderboardJob)->dailyAt('04:00');

Schedule::call(function () {
    $activeUsers = \App\Models\User::where('last_login_at', '>=', now()->subDays(7))->get();
    $controller = app(\App\Http\Controllers\Api\V1\UserAyahProgressController::class);
    foreach ($activeUsers as $user) {
        try {
            $controller->warmCache($user->id);
        } catch (\Exception $e) {
            \Log::error("Failed to warm scheduled cache for user {$user->id}: " . $e->getMessage());
        }
    }
})->dailyAt('00:00');

