<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * MemorizationPlanSeeder
 *
 * Generates production-quality Quran memorization plans and their daily items.
 *
 * Assumed schema
 * ──────────────
 * memorization_plans:
 *   id, user_id, name, type (full_quran|surah|juz|custom),
 *   level (beginner|medium|advanced),
 *   start_date (date), target_end_date (date),
 *   daily_target_type (ayahs|pages|juz|hizb),
 *   daily_target_value (int), status (active|paused|completed),
 *   created_at, updated_at
 *
 * memorization_plan_items:
 *   id, plan_id, surah_id (1-114),
 *   from_ayah_id (global ayah ID 1-6236),
 *   to_ayah_id   (global ayah ID 1-6236),
 *   day_number (int, starts at 1), target_date (date),
 *   created_at, updated_at
 *
 * Global ayah IDs use the Kufi convention (6 236 total).
 * Surah 1 → IDs 1-7 | Surah 2 → IDs 8-293 | … | Surah 114 → IDs 6231-6236
 */
class MemorizationPlanSeeder extends Seeder
{
    use QuranDataHelper;

    // ── Plan type weights (out of 100) ──────────────────────────────────
    private const PLAN_TYPE_WEIGHTS = [
        'surah'      => 40,
        'juz'        => 30,
        'custom'     => 20,
        'full_quran' => 10,
    ];

    // ── Popular surahs for single-surah plans ───────────────────────────
    private const POPULAR_SURAHS = [
        2, 3, 12, 18, 36, 55, 56, 67, 78,
        112, 113, 114,  // Short surahs — often first memorized
        1, 4, 19, 20,
    ];

    // ── Conversion: non-ayah target types → approximate ayahs ──────────
    private const TARGET_TYPE_AYAHS = [
        'pages' => 15,   // 1 mushaf page  ≈ 15 ayahs
        'juz'   => 207,  // 1 juz           ≈ 207 ayahs (6236 / 30)
        'hizb'  => 104,  // 1 hizb          ≈ 104 ayahs (207 / 2)
    ];

    // ── Status distribution ──────────────────────────────────────────────
    private const STATUS_WEIGHTS = [
        'active'    => 60,
        'paused'    => 20,
        'completed' => 20,
    ];

    // ── Level distribution ───────────────────────────────────────────────
    private const LEVEL_WEIGHTS = [
        'beginner' => 40,
        'medium'   => 35,
        'advanced' => 25,
    ];

    // ── Target-type distribution ─────────────────────────────────────────
    private const TARGET_TYPE_WEIGHTS = [
        'ayahs' => 50,
        'pages' => 25,
        'juz'   => 15,
        'hizb'  => 10,
    ];

    // ─────────────────────────────────────────────────────────────────────
    // ENTRY POINT
    // ─────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->initQuranData();

        // Truncate in safe FK order (reviews → items → plans)
        Schema::disableForeignKeyConstraints();
        DB::table('memorization_reviews')->truncate();
        DB::table('memorization_plan_items')->truncate();
        DB::table('memorization_plans')->truncate();
        Schema::enableForeignKeyConstraints();

        $this->command->info('► Seeding users …');
        $users = $this->ensureUsers(10);

        $this->command->info('► Generating memorization plans and items …');

        $now         = Carbon::now();
        $pendingItems = [];
        $planCount   = 0;
        $itemCount   = 0;

        foreach ($users as $user) {
            $plansForUser = random_int(1, 3);
            $usedTypes    = [];

            for ($p = 0; $p < $plansForUser; $p++) {
                // ── Pick plan configuration ──────────────────────────────
                $planType   = $this->pickPlanType($usedTypes);
                $usedTypes[] = $planType;

                $level       = $this->weightedRandom(self::LEVEL_WEIGHTS);
                $targetType  = $this->weightedRandom(self::TARGET_TYPE_WEIGHTS);
                $targetValue = $this->dailyTargetValue($level, $targetType);
                $dailyAyahs  = $this->toAyahsPerDay($targetValue, $targetType);

                // ── Determine ayah range ─────────────────────────────────
                [$startGlobal, $endGlobal, $planName] = $this->resolveRange($planType);

                // ── Dates ────────────────────────────────────────────────
                $startDate      = $now->copy()->subDays(random_int(0, 30));
                $totalAyahs     = $endGlobal - $startGlobal + 1;
                $estimatedDays  = (int) ceil($totalAyahs / $dailyAyahs);
                $targetEndDate  = $startDate->copy()->addDays($estimatedDays);
                $status         = $this->weightedRandom(self::STATUS_WEIGHTS);

                // Completed plans must have ended in the past
                if ($status === 'completed' && $targetEndDate->isFuture()) {
                    $startDate     = $now->copy()->subDays($estimatedDays + random_int(1, 30));
                    $targetEndDate = $startDate->copy()->addDays($estimatedDays);
                }

                // ── Insert plan ──────────────────────────────────────────
                $planId = DB::table('memorization_plans')->insertGetId([
                    'user_id'            => $user->id,
                    'title'              => $planName,
                    'plan_type'          => $planType,
                    'start_date'         => $startDate->toDateString(),
                    'target_end_date'    => $targetEndDate->toDateString(),
                    'daily_target_type'  => $targetType,
                    'daily_target_value' => $targetValue,
                    'status'             => $status,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);

                // ── Build plan items ─────────────────────────────────────
                $items      = $this->buildPlanItems(
                    $planId, $startGlobal, $endGlobal, $dailyAyahs, $startDate, $now
                );
                $itemCount += count($items);
                $pendingItems = array_merge($pendingItems, $items);
                $planCount++;

                // Flush to DB in chunks to avoid memory spikes
                if (count($pendingItems) >= 500) {
                    DB::table('memorization_plan_items')->insert($pendingItems);
                    $pendingItems = [];
                }
            }
        }

        // Flush remainder
        if (!empty($pendingItems)) {
            DB::table('memorization_plan_items')->insert($pendingItems);
        }

        $this->command->info("✔ Created {$planCount} plans with {$itemCount} daily items.");
    }

    // ─────────────────────────────────────────────────────────────────────
    // USER HELPERS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Return at least $minimum users, creating them if the table is empty.
     * Uses User::factory() when available, raw DB insert otherwise.
     */
    private function ensureUsers(int $minimum): \Illuminate\Support\Collection
    {
        $existing = DB::table('users')->count();

        if ($existing < $minimum) {
            $needed = $minimum - $existing;
            $now    = Carbon::now();
            $batch  = [];

            for ($i = 0; $i < $needed; $i++) {
                [$firstName, $lastName] = $this->randomArabicName();
                $email  = mb_strtolower("{$firstName}.{$lastName}" . random_int(100, 999) . '@example.com');
                $email  = str_replace("'", '', $email);  // sanitise apostrophes

                $batch[] = [
                    'name'              => "{$firstName} {$lastName}",
                    'username'          => strstr($email, '@', true),
                    'email'             => $email,
                    'email_verified_at' => $now,
                    'password'          => Hash::make('password'),
                    'created_at'        => $now->copy()->subDays(random_int(30, 365)),
                    'updated_at'        => $now,
                ];
            }

            DB::table('users')->insert($batch);
        }

        return DB::table('users')->limit($minimum + 5)->get();
    }

    // ─────────────────────────────────────────────────────────────────────
    // PLAN CONFIGURATION HELPERS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Pick a plan type, avoiding duplicates per user where possible.
     */
    private function pickPlanType(array $alreadyUsed): string
    {
        $available = array_filter(
            array_keys(self::PLAN_TYPE_WEIGHTS),
            fn ($t) => !in_array($t, $alreadyUsed, true)
        );

        // If all types used, start repeating from weighted pool
        $pool = empty($available)
            ? self::PLAN_TYPE_WEIGHTS
            : array_intersect_key(self::PLAN_TYPE_WEIGHTS, array_flip($available));

        return $this->weightedRandom($pool);
    }

    /**
     * Realistic daily target value based on skill level and unit type.
     */
    private function dailyTargetValue(string $level, string $targetType): int
    {
        return match ($targetType) {
            'ayahs' => match ($level) {
                'beginner' => random_int(3, 5),
                'medium'   => random_int(7, 10),
                default    => random_int(10, 20),
            },
            'pages' => match ($level) {
                'beginner' => 1,
                'medium'   => random_int(1, 2),
                default    => random_int(2, 4),
            },
            'juz' => 1,   // Even advanced learners target 1 juz/day maximum
            'hizb' => match ($level) {
                'beginner' => 1,
                'medium'   => random_int(1, 2),
                default    => random_int(2, 4),
            },
            default => 5,
        };
    }

    /**
     * Convert any target type to approximate ayahs per day for item generation.
     */
    private function toAyahsPerDay(int $targetValue, string $targetType): int
    {
        $multiplier = self::TARGET_TYPE_AYAHS[$targetType] ?? 1;
        return max(1, $targetValue * $multiplier);
    }

    // ─────────────────────────────────────────────────────────────────────
    // RANGE RESOLUTION
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Returns [startGlobalId, endGlobalId, planName] for a given plan type.
     *
     * @return array{int, int, string}
     */
    private function resolveRange(string $planType): array
    {
        $maxAyahId = DB::table('ayahs')->max('id') ?? 7;

        if ($maxAyahId < 100) {
            return [1, $maxAyahId, 'Surah ' . $this->surahName(1)];
        }

        return match ($planType) {
            'full_quran' => $this->rangeFullQuran(),
            'surah'      => $this->rangeSurah(),
            'juz'        => $this->rangeJuz(),
            'custom'     => $this->rangeCustom(),
            default      => $this->rangeFullQuran(),
        };
    }

    /** Full Quran: all 6 236 ayahs. */
    private function rangeFullQuran(): array
    {
        return [1, 6236, 'Full Quran (30 Juz)'];
    }

    /** Single surah from popular list. */
    private function rangeSurah(): array
    {
        $surahId  = self::POPULAR_SURAHS[array_rand(self::POPULAR_SURAHS)];
        $start    = $this->surahFirstGlobalId($surahId);
        $end      = $this->surahLastGlobalId($surahId);

        return [$start, $end, 'Surah ' . $this->surahName($surahId)];
    }

    /** Single juz. */
    private function rangeJuz(): array
    {
        $juzId = random_int(1, 30);
        $range = $this->juzRange($juzId);

        return [$range['start'], $range['end'], "Juz {$juzId}"];
    }

    /**
     * Custom range: starts at a random point, spans 2–10 surahs.
     * Keeps plans reasonably sized (max ~800 ayahs).
     */
    private function rangeCustom(): array
    {
        $startSurahId = random_int(1, 100); // leave room for end surah
        $surahSpan    = random_int(2, 8);
        $endSurahId   = min(114, $startSurahId + $surahSpan);

        $startAyah = random_int(1, max(1, (int) ($this->surahAyahCount($startSurahId) / 2)));
        $startGlob = $this->getGlobalId($startSurahId, $startAyah);
        $endGlob   = $this->surahLastGlobalId($endSurahId);

        $name = sprintf(
            'Custom: %s %d → %s',
            $this->surahName($startSurahId),
            $startAyah,
            $this->surahName($endSurahId)
        );

        return [$startGlob, $endGlob, $name];
    }

    // ─────────────────────────────────────────────────────────────────────
    // PLAN ITEM BUILDER
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Generates the daily memorization items for a plan.
     *
     * Rules implemented:
     *  • Sequential, non-overlapping, contiguous ayah ranges
     *  • Each item covers exactly $dailyAyahs (last item may be shorter)
     *  • surah_id = surah of the first ayah in that day's chunk
     *  • target_date = start_date + (day_number − 1) days
     *  • day_number is sequential from 1
     *
     * Example output for dailyAyahs = 5, start = Al-Fatiha:
     *  Day 1 → surah 1, from_ayah 1, to_ayah 5   (Al-Fatiha 1-5)
     *  Day 2 → surah 1, from_ayah 6, to_ayah 7;  (Al-Fatiha 6-7 + Al-Baqarah 1-3)
     *            surah_id = 1 (first ayah's surah)
     *  Day 3 → surah 2, from_ayah 11, to_ayah 15
     *  …
     */
    private function buildPlanItems(
        int    $planId,
        int    $startGlobal,
        int    $endGlobal,
        int    $dailyAyahs,
        Carbon $startDate,
        Carbon $now
    ): array {
        $items      = [];
        $current    = $startGlobal;
        $dayNumber  = 1;

        while ($current <= $endGlobal) {
            $dayEnd      = min($current + $dailyAyahs - 1, $endGlobal);
            $surahInfo   = $this->getSurahForGlobalId($current);
            $targetDate  = $startDate->copy()->addDays($dayNumber - 1)->toDateString();

            $items[] = [
                'memorization_plan_id' => $planId,
                'surah_id'             => $surahInfo['surah_id'],
                'from_ayah_id'         => $current,
                'to_ayah_id'           => $dayEnd,
                'day_number'           => $dayNumber,
                'target_date'          => $targetDate,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];

            $current   = $dayEnd + 1;
            $dayNumber++;
        }

        return $items;
    }

    // ─────────────────────────────────────────────────────────────────────
    // FAKE DATA HELPERS
    // ─────────────────────────────────────────────────────────────────────

    /** Returns [firstName, lastName] using realistic Muslim/Arabic names. */
    private function randomArabicName(): array
    {
        $firstNames = [
            'Ahmad', 'Muhammad', 'Ibrahim', 'Yusuf', 'Ali', 'Omar', 'Hassan',
            'Fatima', 'Aisha', 'Maryam', 'Zahra', 'Nour', 'Khalid', 'Bilal',
            'Tariq', 'Samir', 'Layla', 'Hana', 'Sara', 'Amira', 'Kareem',
            'Zainab', 'Hafsa', 'Umar', 'Anas', 'Salma', 'Rania', 'Dina',
        ];

        $lastNames = [
            'Al-Rashid', 'Al-Hassan', 'Al-Farsi', 'Al-Masri', 'Qureshi',
            'Al-Ansari', 'Al-Makki', 'Al-Madani', 'Tahir', 'Saleh',
            'Al-Husseini', 'Khalifa', 'Nasser', 'Karimi', 'Al-Saud',
            'Othman', 'Al-Faris', 'Shaikh', 'Malik', 'Al-Ameen',
        ];

        return [
            $firstNames[array_rand($firstNames)],
            $lastNames[array_rand($lastNames)],
        ];
    }
}
