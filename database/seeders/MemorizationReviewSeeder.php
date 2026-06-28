<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MemorizationReviewSeeder
 *
 * Simulates realistic human Quran memorization review behaviour:
 *
 *  • Spaced-repetition intervals (strong ayahs reviewed less often,
 *    weak ayahs reviewed more often with shorter gaps)
 *  • Learning curve (early reviews show more mistakes; later show improvement)
 *  • SM-2-style ease factor and interval tracking
 *  • Review date respects target_date (can't review before an item is due)
 *  • 200-1 000 reviews per user
 *
 * Assumed schema — memorization_reviews:
 *   id, user_id, plan_item_id, ayah_id (global ID),
 *   review_date (date), result (perfect|good|fair|needs_work|forgot),
 *   ease_factor (decimal 1.3–3.0), interval_days (int),
 *   next_review_date (date), notes (text nullable),
 *   created_at, updated_at
 */
class MemorizationReviewSeeder extends Seeder
{
    use QuranDataHelper;

    // ─────────────────────────────────────────────────────────────────────
    // RESULT DISTRIBUTION CONSTANTS
    // ─────────────────────────────────────────────────────────────────────

    /** Overall target distribution (used to sanity-check generated data). */
    private const TARGET_OVERALL = [
        'perfect'    => 30,
        'good'       => 30,
        'fair'       => 20,
        'needs_work' => 15,
        'forgot'     => 5,
    ];

    /** Weights for the very first review of any ayah (cold-start). */
    private const W_INITIAL = [
        'perfect'    => 10,
        'good'       => 20,
        'fair'       => 30,
        'needs_work' => 30,
        'forgot'     => 10,
    ];

    /** Weights for a weak ayah (weakness ≥ 0.6) regardless of phase. */
    private const W_WEAK = [
        'perfect'    => 8,
        'good'       => 17,
        'fair'       => 25,
        'needs_work' => 35,
        'forgot'     => 15,
    ];

    /** Weights during the active learning / improvement phase. */
    private const W_IMPROVING = [
        'perfect'    => 20,
        'good'       => 30,
        'fair'       => 25,
        'needs_work' => 18,
        'forgot'     => 7,
    ];

    /** Weights for a strong ayah that's been reviewed multiple times. */
    private const W_STRONG = [
        'perfect'    => 45,
        'good'       => 35,
        'fair'       => 12,
        'needs_work' => 6,
        'forgot'     => 2,
    ];

    // ─────────────────────────────────────────────────────────────────────
    // SPACED REPETITION INTERVAL TABLES (days)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Interval schedule for STRONG ayahs.
     * Exponential growth: 1, 3, 7, 14, 30, 60 days between reviews.
     */
    private const INTERVALS_STRONG = [1, 3, 7, 14, 30, 60];

    /**
     * Interval schedule for WEAK ayahs.
     * Short, frequent repetition before extending.
     */
    private const INTERVALS_WEAK = [1, 1, 1, 2, 2, 3, 4, 5, 7];

    // ─────────────────────────────────────────────────────────────────────
    // SM-2 EASE FACTOR IMPACT PER RESULT
    // ─────────────────────────────────────────────────────────────────────
    private const EASE_IMPACT = [
        'perfect'    => +0.10,
        'good'       =>  0.00,
        'fair'       => -0.14,
        'needs_work' => -0.20,
        'forgot'     => -0.32,
    ];

    // ─────────────────────────────────────────────────────────────────────
    // INTERVAL MULTIPLIERS PER RESULT
    // ─────────────────────────────────────────────────────────────────────
    private const INTERVAL_MULTIPLIER = [
        'perfect'    => 1.30,
        'good'       => 1.00,
        'fair'       => 0.70,
        'needs_work' => 0.35,
        'forgot'     => 0.10,
    ];

    // ─────────────────────────────────────────────────────────────────────
    // ENTRY POINT
    // ─────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->initQuranData();

        Schema::disableForeignKeyConstraints();
        DB::table('memorization_reviews')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->command->info('► Generating memorization reviews …');

        $now              = Carbon::now();
        $pendingReviews   = [];
        $totalReviews     = 0;
        $usersProcessed   = 0;

        // Load users that have plans
        $userIds = DB::table('memorization_plans')
            ->distinct()
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            // Load all plan items for this user, ordered by scheduled date
            $planItems = DB::table('memorization_plan_items')
                ->join('memorization_plans', 'memorization_plans.id', '=', 'memorization_plan_items.memorization_plan_id')
                ->where('memorization_plans.user_id', $userId)
                ->where('memorization_plan_items.target_date', '<=', $now->toDateString())
                ->select([
                    'memorization_plan_items.id',
                    'memorization_plan_items.from_ayah_id',
                    'memorization_plan_items.to_ayah_id',
                    'memorization_plan_items.target_date',
                    'memorization_plan_items.day_number',
                    'memorization_plans.status as plan_status',
                ])
                ->orderBy('memorization_plan_items.target_date')
                ->get()
                ->toArray();

            if (empty($planItems)) {
                continue;
            }

            $userReviews = $this->generateUserReviews($userId, $planItems, $now);
            $totalReviews += count($userReviews);
            $pendingReviews = array_merge($pendingReviews, $userReviews);
            $usersProcessed++;

            // Flush to DB every 500 records
            if (count($pendingReviews) >= 500) {
                DB::table('memorization_reviews')->insert($pendingReviews);
                $pendingReviews = [];
            }
        }

        // Flush remainder
        if (!empty($pendingReviews)) {
            DB::table('memorization_reviews')->insert($pendingReviews);
        }

        $this->command->info("✔ Generated {$totalReviews} review records across {$usersProcessed} users.");
        $this->printResultDistribution();
    }

    // ─────────────────────────────────────────────────────────────────────
    // REVIEW GENERATION PER USER
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Generates all review records for one user across all their plan items.
     *
     * Strategy:
     *  - Assign each item a "weakness score" (0 = very strong, 1 = very weak)
     *  - Score is influenced by item position in the plan (later = weaker,
     *    less time to practice) plus randomness for realism
     *  - Weak items get more, closer-spaced reviews with worse results
     *  - Strong items get fewer, wider-spaced reviews with better results
     *  - Cap at 1 000 reviews per user to meet data volume requirements
     */
    private function generateUserReviews(int $userId, array $planItems, Carbon $now): array
    {
        $reviews     = [];
        $totalItems  = count($planItems);
        $reviewCap   = random_int(200, 1000); // Per-user review budget

        foreach ($planItems as $idx => $item) {
            if (count($reviews) >= $reviewCap) {
                break;
            }

            // ── Compute weakness score ────────────────────────────────────
            // Items later in the plan are less practiced → weaker
            $positionBias  = $idx / max(1, $totalItems - 1);           // 0.0 → 1.0
            $randomNoise   = (random_int(-25, 25)) / 100;              // ±0.25
            $weaknessScore = (float) max(0.0, min(1.0, $positionBias + $randomNoise));

            // ── Determine how many reviews to generate ───────────────────
            $maxReviews     = $this->maxReviewCount($weaknessScore);
            $reviewCount    = random_int(1, $maxReviews);
            $reviewCount    = min($reviewCount, $reviewCap - count($reviews));

            // ── Generate review dates via spaced repetition ───────────────
            $targetDate     = Carbon::parse($item->target_date);
            $reviewDates    = $this->buildReviewDates($targetDate, $reviewCount, $weaknessScore, $now);

            if (empty($reviewDates)) {
                continue;
            }

            // ── Build review records ──────────────────────────────────────
            $easeFactor = 2.5; // SM-2 starting ease factor

            foreach ($reviewDates as $reviewIdx => $reviewDate) {
                $result     = $this->pickResult($weaknessScore, $reviewIdx, count($reviewDates));
                $easeFactor = $this->updateEaseFactor($easeFactor, $result);
                $intervalSchedule = $weaknessScore >= 0.5
                    ? self::INTERVALS_WEAK
                    : self::INTERVALS_STRONG;
                $baseInterval = $intervalSchedule[min($reviewIdx, count($intervalSchedule) - 1)];
                $intervalDays = $this->applyResultMultiplier($baseInterval, $result);
                $nextReview   = $reviewDate->copy()->addDays($intervalDays);

                $reviews[] = [
                    'user_id'          => $userId,
                    'ayah_id'          => (int) $item->from_ayah_id,
                    'review_date'      => $reviewDate->toDateString(),
                    'review_level'     => $reviewIdx === 0 ? 'learning' : 'reviewing',
                    'result'           => $result,
                    'notes'            => $this->generateNote($result),
                    'created_at'       => $reviewDate->toDateTimeString(),
                    'updated_at'       => $reviewDate->toDateTimeString(),
                ];
            }
        }

        return $reviews;
    }

    // ─────────────────────────────────────────────────────────────────────
    // REVIEW DATE BUILDER (Spaced Repetition Engine)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Builds a chronological list of review Carbon dates for one plan item.
     *
     * Rules:
     *  - First review happens on or shortly after target_date (0-3 day delay)
     *  - Subsequent reviews use the appropriate interval table
     *  - Reviews cannot be in the future (capped at $now)
     *  - Small jitter (±1 day) simulates human inconsistency
     *
     * @return Carbon[]
     */
    private function buildReviewDates(
        Carbon $targetDate,
        int    $count,
        float  $weaknessScore,
        Carbon $now
    ): array {
        $dates     = [];
        $intervals = $weaknessScore >= 0.5 ? self::INTERVALS_WEAK : self::INTERVALS_STRONG;

        // First review: same day as target, or 1-3 days late (human delay)
        $current = $targetDate->copy()->addDays(random_int(0, 3));

        if ($current->gt($now)) {
            return []; // Item not yet reached
        }

        for ($i = 0; $i < $count; $i++) {
            if ($current->gt($now)) {
                break;
            }

            $dates[] = $current->copy();

            // Next review interval from schedule + human jitter
            $interval = $intervals[min($i, count($intervals) - 1)];
            $jitter   = random_int(-1, 2); // humans aren't perfect schedulers
            $current  = $current->copy()->addDays(max(1, $interval + $jitter));
        }

        return $dates;
    }

    // ─────────────────────────────────────────────────────────────────────
    // RESULT SELECTION (Learning Curve Engine)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Picks a result string weighted by:
     *  - weaknessScore (0 = strong, 1 = weak)
     *  - reviewIndex relative to total (learning curve: starts rough, improves)
     */
    private function pickResult(float $weaknessScore, int $reviewIdx, int $totalReviews): string
    {
        // Phase within this item's review history (0 = first, 1 = last)
        $phase = $totalReviews > 1 ? $reviewIdx / ($totalReviews - 1) : 1.0;

        if ($reviewIdx === 0) {
            // Very first review: always coldest weights
            $weights = $weaknessScore >= 0.6 ? self::W_WEAK : self::W_INITIAL;
        } elseif ($weaknessScore >= 0.6) {
            // Persistently weak ayah
            $weights = $phase < 0.5 ? self::W_WEAK : self::W_IMPROVING;
        } elseif ($phase < 0.35) {
            // Early reviews for a medium-strength ayah
            $weights = self::W_IMPROVING;
        } elseif ($phase < 0.65) {
            // Middle reviews
            $weights = $weaknessScore > 0.3 ? self::W_IMPROVING : self::W_STRONG;
        } else {
            // Later reviews: should be getting better
            $weights = $weaknessScore > 0.5 ? self::W_IMPROVING : self::W_STRONG;
        }

        return $this->weightedRandom($weights);
    }

    // ─────────────────────────────────────────────────────────────────────
    // SM-2 CALCULATIONS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Updates the SM-2 ease factor based on the review result.
     * Clamped to [1.3, 3.0] per SM-2 specification.
     */
    private function updateEaseFactor(float $current, string $result): float
    {
        $delta = self::EASE_IMPACT[$result] ?? 0.0;
        return max(1.3, min(3.0, $current + $delta));
    }

    /**
     * Applies result-based multiplier to a base interval and adds small jitter.
     */
    private function applyResultMultiplier(int $baseInterval, string $result): int
    {
        $multiplier = self::INTERVAL_MULTIPLIER[$result] ?? 1.0;
        $days       = (int) round($baseInterval * $multiplier);
        return max(1, $days);
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPER: Review Count
    // ─────────────────────────────────────────────────────────────────────

    /**
     * How many reviews to generate for one item given its weakness score.
     *
     * Weak ayahs → 5-12 reviews (needs lots of repetition)
     * Medium     → 3-7 reviews
     * Strong     → 2-4 reviews
     */
    private function maxReviewCount(float $weaknessScore): int
    {
        if ($weaknessScore >= 0.65) return random_int(5, 12);
        if ($weaknessScore >= 0.35) return random_int(3, 7);
        return random_int(2, 4);
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPER: Realistic Notes
    // ─────────────────────────────────────────────────────────────────────

    /** Generates an optional short note (30 % of reviews). */
    private function generateNote(string $result): ?string
    {
        if (random_int(1, 100) > 30) {
            return null;
        }

        $pool = match ($result) {
            'perfect'    => [
                'Recited perfectly with tajweed',
                'Very smooth and fluent',
                'Excellent recall',
                'Zero hesitation',
            ],
            'good'       => [
                'Minor hesitation in the middle',
                'Good but rushed the ending',
                'Almost perfect',
                'Tajweed needs slight polish',
            ],
            'fair'       => [
                'Hesitated twice',
                'Confused the order of ayahs',
                'Pronunciation errors on some words',
                'Needed 1 hint',
            ],
            'needs_work' => [
                'Forgot several words',
                'Mixed up with adjacent ayah',
                'Needs daily review for a week',
                'Very slow — lacks fluency',
            ],
            'forgot'     => [
                'Completely forgot this portion',
                'Need to restart from scratch',
                'No recall at all',
                'Severely weak — urgent revision needed',
            ],
            default      => [null],
        };

        return $pool[array_rand($pool)];
    }

    // ─────────────────────────────────────────────────────────────────────
    // STATISTICS REPORT
    // ─────────────────────────────────────────────────────────────────────

    private function printResultDistribution(): void
    {
        $total = DB::table('memorization_reviews')->count();

        if ($total === 0) {
            return;
        }

        $this->command->info('');
        $this->command->info('── Review Result Distribution ───────────────────────────');

        $results = DB::table('memorization_reviews')
            ->selectRaw('result, COUNT(*) as cnt')
            ->groupBy('result')
            ->orderByRaw('cnt DESC')
            ->get();

        foreach ($results as $row) {
            $pct  = round(($row->cnt / $total) * 100, 1);
            $bar  = str_repeat('█', (int) ($pct / 2));
            $this->command->line(sprintf(
                '  %-12s %5d  (%5.1f%%)  %s',
                $row->result,
                $row->cnt,
                $pct,
                $bar
            ));
        }

        $this->command->info("  Total: {$total} reviews");

        $avgPerUser = DB::table('memorization_reviews')
            ->selectRaw('AVG(cnt) as avg')
            ->fromSub(
                DB::table('memorization_reviews')
                    ->selectRaw('user_id, COUNT(*) as cnt')
                    ->groupBy('user_id'),
                'per_user'
            )
            ->value('avg');

        $this->command->info(sprintf('  Avg per user: %.0f', $avgPerUser ?? 0));
        $this->command->info('');
    }
}
