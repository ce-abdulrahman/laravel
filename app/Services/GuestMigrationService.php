<?php

namespace App\Services;

use App\Models\User;
use App\Models\TasbihSession;
use App\Models\UserDailyGoal;
use App\Models\UserAchievement;
use Illuminate\Support\Facades\DB;

class GuestMigrationService
{
    /**
     * Migrate guest local progress records to the authenticated user account.
     */
    public function migrate(User $user, array $guestData): void
    {
        DB::transaction(function () use ($user, $guestData) {
            // 1. Migrate Sessions
            if (isset($guestData['sessions']) && is_array($guestData['sessions'])) {
                foreach ($guestData['sessions'] as $session) {
                    $startTime = \Carbon\Carbon::parse($session['start_time'])->toDateTimeString();

                    try {
                        TasbihSession::updateOrCreate([
                            'user_id' => $user->id,
                            'start_time' => $startTime,
                        ], [
                            'dhikr_id' => $session['dhikr_id'] ?? null,
                            'custom_dhikr_name' => $session['custom_dhikr_name'] ?? null,
                            'end_time' => isset($session['end_time']) ? \Carbon\Carbon::parse($session['end_time'])->toDateTimeString() : null,
                            'duration_seconds' => $session['duration_seconds'] ?? 0,
                            'total_count' => $session['total_count'] ?? 0,
                            'avg_per_minute' => $session['avg_per_minute'] ?? 0.00,
                            'session_date' => $session['session_date'] ?? now()->toDateString(),
                            'status' => 'completed',
                        ]);
                    } catch (\Illuminate\Database\IntegrityConstraintViolationException $e) {
                        // If constraint violation occurs, ignore and continue
                        continue;
                    }
                }
            }

            // 2. Migrate Streaks
            if (isset($guestData['streaks']) && is_array($guestData['streaks'])) {
                $streakData = $guestData['streaks'];
                $existingStreak = DB::table('user_tasbih_streaks')->where('user_id', $user->id)->first();

                $lastActivityDate = isset($streakData['last_activity_date'])
                    ? \Carbon\Carbon::parse($streakData['last_activity_date'])->toDateString()
                    : null;

                if ($existingStreak) {
                    $newCurrent = max($existingStreak->current_streak, $streakData['current_streak'] ?? 0);
                    $newLongest = max($existingStreak->longest_streak, $streakData['longest_streak'] ?? 0);

                    DB::table('user_tasbih_streaks')->where('user_id', $user->id)->update([
                        'current_streak' => $newCurrent,
                        'longest_streak' => $newLongest,
                        'last_activity_date' => $lastActivityDate ?? $existingStreak->last_activity_date,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('user_tasbih_streaks')->insert([
                        'user_id' => $user->id,
                        'current_streak' => $streakData['current_streak'] ?? 0,
                        'longest_streak' => $streakData['longest_streak'] ?? 0,
                        'last_activity_date' => $lastActivityDate,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 3. Migrate Goals
            if (isset($guestData['goals']) && is_array($guestData['goals'])) {
                foreach ($guestData['goals'] as $goal) {
                    // Ensure consistent date format
                    $goalDate = \Carbon\Carbon::parse($goal['goal_date'])->toDateString();

                    try {
                        // Try to find and update existing goal
                        $existing = UserDailyGoal::where('user_id', $user->id)
                            ->where('goal_date', $goalDate)
                            ->first();

                        if ($existing) {
                            // Update only if new values are provided
                            $existing->update([
                                'goal_value' => $goal['goal_value'] ?? $existing->goal_value,
                                'today_progress' => $goal['today_progress'] ?? $existing->today_progress,
                                'is_completed' => $goal['is_completed'] ?? $existing->is_completed,
                            ]);
                        } else {
                            // Create new goal
                            UserDailyGoal::create([
                                'user_id' => $user->id,
                                'goal_date' => $goalDate,
                                'goal_value' => $goal['goal_value'] ?? 0,
                                'today_progress' => $goal['today_progress'] ?? 0,
                                'is_completed' => $goal['is_completed'] ?? false,
                            ]);
                        }
                    } catch (\Illuminate\Database\IntegrityConstraintViolationException $e) {
                        // If constraint violation occurs (race condition), ignore and continue
                        // The record was likely created by another process
                        continue;
                    }
                }
            }

            // 4. Migrate Achievements
            if (isset($guestData['achievements']) && is_array($guestData['achievements'])) {
                foreach ($guestData['achievements'] as $ach) {
                    try {
                        UserAchievement::updateOrCreate([
                            'user_id' => $user->id,
                            'achievement_id' => $ach['achievement_id'],
                        ], [
                            'unlocked_at' => $ach['unlocked_at'] ?? now(),
                        ]);
                    } catch (\Illuminate\Database\IntegrityConstraintViolationException $e) {
                        // If constraint violation occurs, ignore and continue
                        continue;
                    }
                }
            }
        });

        // Invalidate profile statistics caching
        app(ProfileStatisticsService::class)->invalidateCache($user->id);
    }
}
