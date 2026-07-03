<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Surah;
use App\Models\Ayah;
use App\Models\HadithCategory;
use App\Models\TajweedRuleCategory;
use App\Models\AdhkarCategory;
use App\Models\Tasbih;
use Illuminate\Support\Facades\File;

$assetsPath = 'c:/Users/kurdn/Desktop/my-quran/quran_mobile/assets/data';

echo "Starting export of all sections to $assetsPath...\n";

// 1. Export Quran Surahs & Ayahs (Split by Surah)
$quranDir = $assetsPath . '/quran';
if (!File::exists($quranDir)) {
    File::makeDirectory($quranDir, 0755, true);
}

// Build color palette map
$rulesList = \App\Models\TajweedRule::where('is_active', true)->orderBy('priority')->get();
$uniqueColors = $rulesList->pluck('color_code')->filter()->unique()->values()->toArray();
$colorMap = [];
foreach ($uniqueColors as $idx => $color) {
    $colorMap[$color] = $idx + 1; // 1-based index
}

$surahs = Surah::active()->orderBy('number')->get();
foreach ($surahs as $surah) {
    echo "Exporting Surah {$surah->number} ({$surah->name})...\n";
    $ayahs = Ayah::active()
        ->with([
            'translations' => function ($q) {
                $q->where('is_active', true);
            },
            'tajweedSegments.tajweedRule',
        ])
        ->where('surah_id', $surah->id)
        ->orderBy('ayah_number')
        ->get();

    // Map each ayah to match Mobile app expected format
    $mappedAyahs = $ayahs->map(function ($ayah) use ($colorMap) {
        $translations = $ayah->translations->map(function ($t) {
            return [
                'id' => $t->id,
                'language_code' => $t->language_code,
                'content' => $t->content,
                'is_active' => (bool)$t->is_active,
            ];
        })->toArray();

        $text = $ayah->text_uthmani;
        $rawSegments = $ayah->tajweedSegments;
        $processed = [];

        foreach ($rawSegments as $seg) {
            $start = $seg->start_index;
            $end = $seg->end_index;

            // 1. Validation Stage
            if ($start === null || $end === null || $start >= $end || $start < 0 || $end > mb_strlen($text)) {
                continue; // Skip invalid indices
            }

            $rule = $seg->tajweedRule;
            if (!$rule || !$rule->is_active) {
                continue; // Skip missing/inactive rules
            }

            // 2. Compiler Stage: Precompute joining ZWJ boundary flags
            $connectsToLeft = false;
            $connectsToRight = false;

            if ($start > 0 && $start < mb_strlen($text)) {
                $prevChar = mb_substr($text, $start - 1, 1);
                $currChar = mb_substr($text, $start, 1);
                if (connectsToLeft($prevChar) && connectsToRight($currChar)) {
                    $connectsToLeft = true;
                }
            }

            if ($end > 0 && $end < mb_strlen($text)) {
                $lastChar = mb_substr($text, $end - 1, 1);
                $nextChar = mb_substr($text, $end, 1);
                if (connectsToLeft($lastChar) && connectsToRight($nextChar)) {
                    $connectsToRight = true;
                }
            }

            $processed[] = [
                'start_index' => $start,
                'end_index' => $end,
                'rule_id' => $rule->id,
                'color_id' => $colorMap[$rule->color_code] ?? 1,
                'connects_to_left' => $connectsToLeft,
                'connects_to_right' => $connectsToRight,
                'text_segment' => $seg->matched_text ?? '',
            ];
        }

        // 3. Optimization Stage (Dedup, Sort, and Compress Adjacent Segments)
        usort($processed, function ($x, $y) {
            return $x['start_index'] <=> $y['start_index'];
        });

        $compressed = [];
        foreach ($processed as $p) {
            if (empty($compressed)) {
                $compressed[] = $p;
                continue;
            }
            $lastIdx = count($compressed) - 1;
            $last = &$compressed[$lastIdx];

            // Merge contiguous segments of the same rule and color
            if ($last['end_index'] === $p['start_index'] && $last['rule_id'] === $p['rule_id'] && $last['color_id'] === $p['color_id']) {
                $last['end_index'] = $p['end_index'];
                $last['connects_to_right'] = $p['connects_to_right'];
            } else {
                $compressed[] = $p;
            }
        }

        return [
            'id' => $ayah->id,
            'ayah_number' => $ayah->ayah_number,
            'text_uthmani' => $ayah->text_uthmani,
            'text_simple' => $ayah->text_simple,
            'page_number' => $ayah->page_number,
            'juz_number' => $ayah->juz_number,
            'hizb_number' => $ayah->hizb_number,
            'rub_number' => $ayah->rub_number,
            'translations' => $translations,
            'tajweed_segments' => $compressed,
        ];
    })->toArray();

    File::put($quranDir . "/surah_{$surah->number}.json", json_encode($mappedAyahs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// 1.5 Export Surahs List
echo "Exporting surahs list...\n";
$surahList = Surah::active()->orderBy('number')->get()->map(function ($s) {
    return [
        'number' => $s->number,
        'name_ar' => $s->name_ar,
        'name_ku' => $s->name_ku,
        'name_en' => $s->name_en,
        'revelation_type' => $s->revelation_type,
        'ayah_count' => $s->ayah_count,
        'page_start' => $s->page_start,
        'page_end' => $s->page_end,
    ];
})->toArray();
File::put($assetsPath . '/surahs.json', json_encode($surahList, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 2. Export Tajweed categories & rules
echo "Exporting tajweed rules...\n";
$tajweedCategories = TajweedRuleCategory::active()
    ->with(['tajweedRules' => function ($q) {
        $q->where('is_active', true)->orderBy('priority');
    }])
    ->orderBy('order')
    ->get()
    ->map(function ($cat) {
        return [
            'id' => $cat->id,
            'slug' => $cat->slug,
            'name' => $cat->name,
            'name_ku' => $cat->name_ku,
            'name_ar' => $cat->name_ar,
            'description_ku' => $cat->description_ku,
            'order' => $cat->order,
            'rules' => $cat->tajweedRules->map(function ($rule) {
                return [
                    'id' => $rule->id,
                    'slug' => $rule->slug,
                    'name' => $rule->name,
                    'name_ku' => $rule->name_ku,
                    'name_ar' => $rule->name_ar,
                    'color_code' => $rule->color_code,
                    'description' => $rule->description,
                    'description_ku' => $rule->description_ku,
                    'example_text' => $rule->example_text,
                    'priority' => $rule->priority,
                    'is_active' => (bool)$rule->is_active,
                ];
            })->toArray(),
        ];
    })->toArray();
File::put($assetsPath . '/tajweed_rules.json', json_encode($tajweedCategories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// 3. Export Hadiths (categorized)
echo "Exporting hadiths...\n";
$hadithCategories = HadithCategory::query()
    ->where('is_active', true)
    ->with(['hadiths' => function ($query) {
        $query->where('is_active', true)->orderBy('order');
    }])
    ->orderBy('order')
    ->get()
    ->map(function ($cat) {
        return [
            'id' => $cat->id,
            'name_ku' => $cat->name_ku,
            'name_ar' => $cat->name_ar,
            'order' => $cat->order,
            'is_active' => (bool)$cat->is_active,
            'hadiths' => $cat->hadiths->map(function ($h) {
                return [
                    'id' => $h->id,
                    'category_id' => $h->hadith_category_id ?? $h->category_id ?? 1,
                    'arabic_text' => $h->arabic_text,
                    'translation_ku' => $h->translation_ku,
                    'explanation_ku' => $h->explanation_ku,
                    'order' => $h->order,
                    'is_active' => (bool)$h->is_active,
                ];
            })->toArray(),
        ];
    })->toArray();

// Only overwrite hadiths.json if database has hadiths, otherwise keep the legacy 4000+ hadiths file!
$totalHadiths = 0;
foreach ($hadithCategories as $cat) {
    $totalHadiths += count($cat['hadiths']);
}
if ($totalHadiths > 0) {
    File::put($assetsPath . '/hadiths.json', json_encode($hadithCategories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo "Exported $totalHadiths hadiths in " . count($hadithCategories) . " categories.\n";
} else {
    echo "Skipping hadiths.json overwrite because no hadiths were found in the database.\n";
}

// 4. Export Adhkars
echo "Exporting adhkars...\n";
$adhkarCategories = AdhkarCategory::query()
    ->where('is_active', true)
    ->with(['adhkars' => function ($query) {
        $query->where('is_active', true)->orderBy('order');
    }])
    ->orderBy('order')
    ->get()
    ->map(function ($cat) {
        return [
            'id' => $cat->id,
            'name_ku' => $cat->name_ku,
            'name_ar' => $cat->name_ar,
            'order' => $cat->order,
            'is_active' => (bool)$cat->is_active,
            'adhkars' => $cat->adhkars->map(function ($a) {
                return [
                    'id' => $a->id,
                    'category_id' => $a->adhkar_category_id,
                    'arabic_text' => $a->arabic_text,
                    'translation_ku' => $a->translation_ku,
                    'counter' => $a->counter,
                    'order' => $a->order,
                    'is_active' => (bool)$a->is_active,
                ];
            })->toArray(),
        ];
    })->toArray();
File::put($assetsPath . '/adhkars.json', json_encode($adhkarCategories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Exported " . count($adhkarCategories) . " adhkar categories.\n";

// 5. Export Tasbihs
echo "Exporting tasbihs...\n";
$tasbihs = Tasbih::query()
    ->where('is_active', true)
    ->get()
    ->map(function ($t) {
        return [
            'id' => $t->id,
            'arabic_text' => $t->arabic_text,
            'translation_ku' => $t->translation_ku,
            'count_limit' => $t->count_limit,
            'is_active' => (bool)$t->is_active,
        ];
    })->toArray();
File::put($assetsPath . '/tasbihs.json', json_encode($tasbihs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Exported " . count($tasbihs) . " tasbihs.\n";

echo "Export completed successfully!\n";

function connectsToLeft($char)
{
    if (empty($char)) return false;
    $code = mb_ord($char, 'UTF-8');
    if ($code < 0x0600 || $code > 0x06FF) return false;

    $rightOnly = [
        0x0621, // Hamza
        0x0622, 0x0623, 0x0625, 0x0627, 0x0671, 0x0672, 0x0673, 0x0675, // Alifs
        0x062F, 0x0630, 0x0688, 0x0689, 0x068A, 0x068B, 0x068C, 0x068D, 0x068E, 0x068F, 0x0690, // Dals
        0x0631, 0x0632, 0x0691, 0x0692, 0x0693, 0x0694, 0x0695, 0x0696, 0x0697, 0x0698, 0x0699, // Ras
        0x0648, 0x0676, 0x0677, 0x06C4, 0x06C5, 0x06C6, 0x06C7, 0x06C8, 0x06C9, 0x06CA, 0x06CB, 0x06CF, // Waws
        0x0629, 0x06C0, 0x06C2 // Teh Marbuta
    ];
    return !in_array($code, $rightOnly);
}

function connectsToRight($char)
{
    if (empty($char)) return false;
    $code = mb_ord($char, 'UTF-8');
    if ($code < 0x0600 || $code > 0x06FF) return false;
    if ($code === 0x0621) return false; // Hamza
    return true;
}
