<?php

namespace App\Services;

use App\Models\UserAyahProgress;
use App\Models\MemorizationPlan;
use Carbon\Carbon;

class CompletionForecastService
{
    public function getForecast(int $userId): array
    {
        // Total memorized/mastered ayahs
        $memorizedCount = UserAyahProgress::where('user_id', $userId)
            ->whereIn('memorize_status', ['memorized', 'mastered'])
            ->count();

        $totalQuranAyahs = 6236;
        $remaining = max(0, $totalQuranAyahs - $memorizedCount);

        // Determine daily target rate
        $activePlan = MemorizationPlan::where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        $dailyTarget = 5; // Default fallback
        if ($activePlan) {
            $val = $activePlan->daily_target_value;
            switch ($activePlan->daily_target_type) {
                case 'ayahs':
                    $dailyTarget = $val;
                    break;
                case 'pages':
                    $dailyTarget = $val * 10; // average 10 ayahs per page
                    break;
                case 'juz':
                    $dailyTarget = $val * 208; // average 208 ayahs per Juz
                    break;
                case 'hizb':
                    $dailyTarget = $val * 104; // average 104 ayahs per Hizb
                    break;
            }
        }
        $dailyTarget = max(1, $dailyTarget);

        if ($remaining === 0) {
            return [
                'estimated_completion_date' => Carbon::today()->toDateString(),
                'remaining_days' => 0,
                'daily_target' => $dailyTarget,
                'remaining_ayahs' => 0,
            ];
        }

        $remainingDays = (int) ceil($remaining / $dailyTarget);
        $finishDate = Carbon::today()->addDays($remainingDays)->toDateString();

        return [
            'estimated_completion_date' => $finishDate,
            'remaining_days' => $remainingDays,
            'daily_target' => $dailyTarget,
            'remaining_ayahs' => $remaining,
        ];
    }
}
