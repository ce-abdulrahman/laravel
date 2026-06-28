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
    $mappedAyahs = $ayahs->map(function ($ayah) {
        $translations = $ayah->translations->map(function ($t) {
            return [
                'id' => $t->id,
                'language_code' => $t->language_code,
                'content' => $t->content,
                'is_active' => (bool)$t->is_active,
            ];
        })->toArray();

        $segments = $ayah->tajweedSegments->map(function ($seg) {
            $rule = $seg->tajweedRule;
            return [
                'text_segment' => $seg->matched_text ?? '',
                'start_index' => $seg->start_index,
                'end_index' => $seg->end_index,
                'note' => $seg->note,
                'waqf_assumed' => (bool)($seg->waqf_assumed ?? false),
                'rule' => $rule ? [
                    'id' => $rule->id,
                    'slug' => $rule->slug,
                    'name' => $rule->name,
                    'name_ku' => $rule->name_ku,
                    'name_ar' => $rule->name_ar,
                    'color_code' => $rule->color_code,
                ] : null,
            ];
        })->toArray();

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
            'tajweed_segments' => $segments,
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
