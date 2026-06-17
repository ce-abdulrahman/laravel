<?php

namespace App\Services;

use App\Models\UserAyahProgress;
use App\Enums\MasteryLevel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SpacedRepetitionService
{
    public function logReview(int $userId, int $ayahId, string $result, string $reviewLevel = 'new'): UserAyahProgress
    {
        $progress = UserAyahProgress::firstOrCreate([
            'user_id' => $userId,
            'ayah_id' => $ayahId,
        ]);

        $today = Carbon::today();
        $progress->last_reviewed_at = now();
        $progress->last_review_result = $result;

        // mistakes count increment if forgot
        if ($result === 'forgot') {
            $progress->mistakes_count += 1;
        }

        // Spaced repetition scheduling logic
        switch ($result) {
            case 'perfect':
                $progress->review_count += 1;
                $progress->current_interval_days = $this->getIntervalForReviewCount($progress->review_count);
                $progress->next_review_date = $today->copy()->addDays($progress->current_interval_days);
                $progress->mastery_level = $this->getMasteryLevelForReviewCount($progress->review_count);
                break;

            case 'good':
                // Keep current interval
                if ($progress->current_interval_days <= 0) {
                    $progress->current_interval_days = $this->getIntervalForReviewCount($progress->review_count);
                }
                $progress->current_interval_days = max(1, $progress->current_interval_days);
                $progress->next_review_date = $today->copy()->addDays($progress->current_interval_days);
                // Keep same mastery level
                if ($progress->mastery_level === MasteryLevel::NotStarted) {
                    $progress->mastery_level = MasteryLevel::Learning;
                }
                break;

            case 'fair':
                // Reduce interval by 50% safely
                if ($progress->current_interval_days <= 0) {
                    $progress->current_interval_days = $this->getIntervalForReviewCount($progress->review_count);
                }
                $progress->current_interval_days = max(1, (int) round($progress->current_interval_days * 0.5));
                $progress->next_review_date = $today->copy()->addDays($progress->current_interval_days);
                // Keep same mastery level
                if ($progress->mastery_level === MasteryLevel::NotStarted) {
                    $progress->mastery_level = MasteryLevel::Learning;
                }
                break;

            case 'needs_work':
                // Schedule tomorrow
                $progress->current_interval_days = 1;
                $progress->next_review_date = $today->copy()->addDay();
                // Keep same mastery level
                if ($progress->mastery_level === MasteryLevel::NotStarted) {
                    $progress->mastery_level = MasteryLevel::Learning;
                }
                break;

            case 'forgot':
                // Reset cycle
                $progress->review_count = 0;
                $progress->current_interval_days = 1;
                $progress->next_review_date = $today->copy()->addDay();
                $progress->mastery_level = MasteryLevel::Learning;
                break;
        }

        // Sync memorize_status for backward compatibility
        $progress->memorize_status = $this->mapMasteryToStatus($progress->mastery_level);
        
        if (in_array($progress->memorize_status, ['memorized', 'mastered'])) {
            if (!$progress->last_memorized_at) {
                $progress->last_memorized_at = now();
            }
        }

        $progress->strength_score = $this->calculateStrengthScore($result);
        $progress->save();

        // Invalidate cache keys for dashboard and statistics
        $this->invalidateCache($userId);

        return $progress;
    }

    public function getCacheVersion(int $userId): int
    {
        return (int) Cache::rememberForever("user_{$userId}_cache_version", fn() => 1);
    }

    public function invalidateCache(int $userId): void
    {
        try {
            Cache::increment("user_{$userId}_cache_version");
        } catch (\Exception $e) {
            $version = (int) Cache::get("user_{$userId}_cache_version", 1);
            Cache::put("user_{$userId}_cache_version", $version + 1);
        }
    }

    private function getIntervalForReviewCount(int $count): int
    {
        return match ($count) {
            1 => 1,
            2 => 3,
            3 => 7,
            4 => 14,
            5 => 30,
            default => 90,
        };
    }

    private function getMasteryLevelForReviewCount(int $count): MasteryLevel
    {
        if ($count >= 6) return MasteryLevel::Mastered;
        if ($count == 5) return MasteryLevel::Strong;
        if ($count >= 3) return MasteryLevel::Memorized;
        return MasteryLevel::Learning;
    }

    private function mapMasteryToStatus(MasteryLevel $mastery): string
    {
        return match ($mastery) {
            MasteryLevel::Mastered => 'mastered',
            MasteryLevel::Strong, MasteryLevel::Memorized => 'memorized',
            MasteryLevel::Learning => 'learning',
            default => 'not_started',
        };
    }

    private function calculateStrengthScore(string $result): int
    {
        return match ($result) {
            'perfect' => 100,
            'good' => 80,
            'fair' => 60,
            'needs_work' => 40,
            'forgot' => 20,
            default => 50,
        };
    }
}
