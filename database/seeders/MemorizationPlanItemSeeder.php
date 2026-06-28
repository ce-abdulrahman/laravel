<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * MemorizationPlanItemSeeder
 *
 * Runs AFTER MemorizationPlanSeeder.
 *
 * Responsibilities
 * ────────────────
 * 1. Validate every plan has at least one item — regenerate if missing.
 * 2. Guarantee day_number sequences start at 1 with no gaps.
 * 3. Recalculate target_date so it always equals plan.start_date + (day_number - 1).
 * 4. Remove duplicate ayah ranges within the same plan.
 * 5. Normalize surah_id: must always be the surah containing from_ayah_id.
 *    (nullable only when plan.type = 'custom' AND the range crosses multiple surahs
 *     — even then, we store the starting surah).
 * 6. Print a statistical report of the final dataset.
 */
class MemorizationPlanItemSeeder extends Seeder
{
    use QuranDataHelper;

    // ─────────────────────────────────────────────────────────────────────
    // ENTRY POINT
    // ─────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->initQuranData();
        $this->command->info('► Validating and enriching plan items …');

        $plans = DB::table('memorization_plans')
            ->select(['id', 'user_id', 'plan_type as type', 'title as name', 'start_date', 'daily_target_value', 'daily_target_type'])
            ->get();

        $stats = [
            'plans_checked'          => 0,
            'plans_missing_items'    => 0,
            'items_regenerated'      => 0,
            'day_number_gaps_fixed'  => 0,
            'target_dates_fixed'     => 0,
            'duplicates_removed'     => 0,
            'surah_ids_corrected'    => 0,
        ];

        foreach ($plans as $plan) {
            $stats['plans_checked']++;
            $startDate = Carbon::parse($plan->start_date);

            // ── 1. Ensure plan has items ─────────────────────────────────
            $items = DB::table('memorization_plan_items')
                ->where('memorization_plan_id', $plan->id)
                ->orderBy('day_number')
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                $this->command->warn("  Plan #{$plan->id} has no items — regenerating …");
                $generated = $this->regenerateMissingItems($plan);
                $stats['plans_missing_items']++;
                $stats['items_regenerated'] += $generated;

                // Re-fetch for subsequent checks
                $items = DB::table('memorization_plan_items')
                    ->where('memorization_plan_id', $plan->id)
                    ->orderBy('day_number')
                    ->get();
            }

            if ($items->isEmpty()) {
                continue; // Can't regenerate (no surah data available)
            }

            // ── 2. Remove duplicate ayah ranges ─────────────────────────
            $seen       = [];
            $duplicates = [];

            foreach ($items as $item) {
                $rangeKey = "{$item->from_ayah_id}:{$item->to_ayah_id}";
                if (isset($seen[$rangeKey])) {
                    $duplicates[] = $item->id;
                } else {
                    $seen[$rangeKey] = $item->id;
                }
            }

            if (!empty($duplicates)) {
                DB::table('memorization_plan_items')
                    ->whereIn('id', $duplicates)
                    ->delete();
                $stats['duplicates_removed'] += count($duplicates);

                // Reload without duplicates
                $items = DB::table('memorization_plan_items')
                    ->where('memorization_plan_id', $plan->id)
                    ->orderBy('from_ayah_id')
                    ->get();
            }

            // ── 3. Fix day_number sequence (must start at 1, no gaps) ───
            $orderedItems  = $items->sortBy('from_ayah_id')->values();
            $needsReorder  = false;

            foreach ($orderedItems as $idx => $item) {
                if ((int) $item->day_number !== $idx + 1) {
                    $needsReorder = true;
                    break;
                }
            }

            if ($needsReorder) {
                foreach ($orderedItems as $idx => $item) {
                    DB::table('memorization_plan_items')
                        ->where('id', $item->id)
                        ->update(['day_number' => $idx + 1]);
                }
                $stats['day_number_gaps_fixed']++;
                $orderedItems = $orderedItems->map(function ($item, $idx) {
                    $item->day_number = $idx + 1;
                    return $item;
                });
            }

            // ── 4. Recalculate target_dates ──────────────────────────────
            $dateUpdates = [];
            foreach ($orderedItems as $item) {
                $expected = $startDate->copy()->addDays((int) $item->day_number - 1)->toDateString();
                if ($item->target_date !== $expected) {
                    $dateUpdates[$expected][] = $item->id;
                }
            }

            foreach ($dateUpdates as $correctDate => $ids) {
                DB::table('memorization_plan_items')
                    ->whereIn('id', $ids)
                    ->update(['target_date' => $correctDate]);
                $stats['target_dates_fixed'] += count($ids);
            }

            // ── 5. Normalize surah_id ────────────────────────────────────
            $surahUpdates = [];
            foreach ($orderedItems as $item) {
                $correct = $this->getSurahForGlobalId((int) $item->from_ayah_id);
                if ((int) $item->surah_id !== $correct['surah_id']) {
                    $surahUpdates[$correct['surah_id']][] = $item->id;
                }
            }

            foreach ($surahUpdates as $correctSurahId => $ids) {
                DB::table('memorization_plan_items')
                    ->whereIn('id', $ids)
                    ->update(['surah_id' => $correctSurahId]);
                $stats['surah_ids_corrected'] += count($ids);
            }
        }

        // ── Print report ─────────────────────────────────────────────────
        $this->printReport($stats);
    }

    // ─────────────────────────────────────────────────────────────────────
    // MISSING ITEM REGENERATION
    // ─────────────────────────────────────────────────────────────────────


        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║   Quran Memorization Module — Database Seeder        ║');
        $this->command->info('╚══════════════════════════════════════════════════════╝');
        $this->command->info('');

        $start = microtime(true);

        $this->call([
            MemorizationPlanSeeder::class,       // Step 1: plans + items
            MemorizationPlanItemSeeder::class,   // Step 2: validate + enrich
            MemorizationReviewSeeder::class,     // Step 3: review history
        ]);

        $elapsed = round(microtime(true) - $start, 2);

        $this->command->info('');
        $this->command->info("✔ All seeders completed in {$elapsed}s.");
        $this->command->info('');
        
    /**
     * Rebuilds plan items for a plan that has none.
     * Derives the ayah range from the plan's type and name.
     * Returns the number of items inserted.
     */
    private function regenerateMissingItems(object $plan): int
    {
        // Infer range from plan type using simple heuristics
        [$startGlobal, $endGlobal] = $this->inferRangeFromPlan($plan);

        $maxAyahId = DB::table('ayahs')->max('id') ?? 7;
        if ($maxAyahId < 100) {
            $startGlobal = 1;
            $endGlobal = $maxAyahId;
        } else {
            $startGlobal = max(1, min($startGlobal, $maxAyahId));
            $endGlobal = max(1, min($endGlobal, $maxAyahId));
        }

        $dailyAyahs = $this->inferDailyAyahs($plan);
        $startDate  = Carbon::parse($plan->start_date);
        $now        = Carbon::now();
        $items      = [];
        $current    = $startGlobal;
        $dayNumber  = 1;

        while ($current <= $endGlobal) {
            $dayEnd    = min($current + $dailyAyahs - 1, $endGlobal);
            $surahInfo = $this->getSurahForGlobalId($current);

            $items[] = [
                'memorization_plan_id' => $plan->id,
                'surah_id'     => $surahInfo['surah_id'],
                'from_ayah_id' => $current,
                'to_ayah_id'   => $dayEnd,
                'day_number'   => $dayNumber,
                'target_date'  => $startDate->copy()->addDays($dayNumber - 1)->toDateString(),
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            $current   = $dayEnd + 1;
            $dayNumber++;
        }

        if (empty($items)) {
            return 0;
        }

        foreach (array_chunk($items, 500) as $chunk) {
            DB::table('memorization_plan_items')->insert($chunk);
        }

        return count($items);
    }

    /**
     * Infer ayah range for a plan based on its type.
     * Falls back to Juz 1 if type is unrecognisable.
     *
     * @return array{int, int}
     */
    private function inferRangeFromPlan(object $plan): array
    {
        $type = $plan->type ?? 'custom';

        switch ($type) {
            case 'full_quran':
                return [1, 6236];

            case 'juz':
                // Try to parse juz number from plan name "Juz N"
                if (preg_match('/Juz\s+(\d+)/i', $plan->name ?? '', $m)) {
                    $juzId = (int) $m[1];
                    $range = $this->juzRange($juzId);
                    return [$range['start'], $range['end']];
                }
                return $this->juzRange(random_int(1, 30));

            case 'surah':
                // Try to parse surah number from plan name
                foreach (array_keys(self::SURAH_NAMES) as $id) {
                    if (str_contains($plan->name ?? '', self::SURAH_NAMES[$id])) {
                        return [
                            $this->surahFirstGlobalId($id),
                            $this->surahLastGlobalId($id),
                        ];
                    }
                }
                // Default to Al-Baqarah
                return [$this->surahFirstGlobalId(2), $this->surahLastGlobalId(2)];

            default: // custom or unknown → small range (Juz 30)
                $range = $this->juzRange(30);
                return [$range['start'], $range['end']];
        }
    }

    /** Convert plan's daily_target to approximate ayahs per day. */
    private function inferDailyAyahs(object $plan): int
    {
        $value = (int) ($plan->daily_target_value ?? 5);
        $type  = $plan->daily_target_type ?? 'ayahs';

        return match ($type) {
            'ayahs' => max(1, $value),
            'pages' => max(1, $value * 15),
            'juz'   => max(1, $value * 207),
            'hizb'  => max(1, $value * 104),
            default => max(1, $value),
        };
    }

    // ─────────────────────────────────────────────────────────────────────
    // REPORT
    // ─────────────────────────────────────────────────────────────────────

    private function printReport(array $stats): void
    {
        $totalItems  = DB::table('memorization_plan_items')->count();
        $totalPlans  = DB::table('memorization_plans')->count();
        $usersCount  = DB::table('memorization_plans')->distinct('user_id')->count('user_id');

        $this->command->info('');
        $this->command->info('── Plan Item Validation Report ──────────────────────────');
        $this->command->info("  Plans checked:            {$stats['plans_checked']}");
        $this->command->info("  Plans with missing items: {$stats['plans_missing_items']}");
        $this->command->info("  Items regenerated:        {$stats['items_regenerated']}");
        $this->command->info("  Day-number gaps fixed:    {$stats['day_number_gaps_fixed']}");
        $this->command->info("  Target dates corrected:   {$stats['target_dates_fixed']}");
        $this->command->info("  Duplicate items removed:  {$stats['duplicates_removed']}");
        $this->command->info("  Surah IDs corrected:      {$stats['surah_ids_corrected']}");
        $this->command->info('─────────────────────────────────────────────────────────');
        $this->command->info("  Final state → {$usersCount} users, {$totalPlans} plans, {$totalItems} items");
        $this->command->info('');

        // Per-plan-type breakdown
        $breakdown = DB::table('memorization_plans')
            ->selectRaw('plan_type as type, COUNT(*) as plan_count, SUM(
                (SELECT COUNT(*) FROM memorization_plan_items WHERE memorization_plan_id = memorization_plans.id)
            ) as item_count')
            ->groupBy('plan_type')
            ->get();

        foreach ($breakdown as $row) {
            $avg = $row->plan_count > 0
                ? round($row->item_count / $row->plan_count, 1)
                : 0;
            $this->command->line("  [{$row->type}] {$row->plan_count} plans, {$row->item_count} items (avg {$avg}/plan)");
        }
    }
}
