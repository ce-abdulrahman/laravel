<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\Surah;
use App\Models\Ayah;
use App\Models\Tafsir;
use App\Models\Hadith;
use App\Models\HadithCategory;
use App\Models\Adhkar;
use App\Models\AdhkarCategory;
use App\Models\Reciter;
use App\Models\TajweedRule;
use App\Models\TajweedRuleCategory;
use App\Models\PrayerTime;
use App\Models\Translation;

class ExportContentPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:export {category? : The category to export (quran, tajweed, tafsir, hadith, adhkar, seerah, sahaba, allah_names, prayer_database, translations, audio_metadata)} {--all : Export all categories}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate, compile, optimize, and generate offline-first packages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Unified Package Exporter Pipeline...');

        $exportAll = $this->option('all');
        $category = $this->argument('category');

        if (!$exportAll && !$category) {
            $this->error('Please specify a category or use the --all flag.');
            return 1;
        }

        $categories = [
            'quran',
            'tajweed',
            'tafsir',
            'hadith',
            'adhkar',
            'seerah',
            'sahaba',
            'allah_names',
            'prayer_database',
            'translations',
            'audio_metadata'
        ];

        $targetCategories = $exportAll ? $categories : [$category];

        // Ensure directories exist
        $localPackagesDir = database_path('packages');
        $publicPackagesDir = public_path('packages');
        $flutterAssetsDir = base_path('../quran_mobile/assets/data/packages');

        File::ensureDirectoryExists($localPackagesDir);
        File::ensureDirectoryExists($publicPackagesDir);
        if (File::exists(base_path('../quran_mobile/assets/data'))) {
            File::ensureDirectoryExists($flutterAssetsDir);
        }

        foreach ($targetCategories as $cat) {
            if (!in_array($cat, $categories)) {
                $this->error("Unknown category: {$cat}");
                continue;
            }

            $this->info("Exporting category: {$cat}...");

            if ($cat === 'tajweed') {
                $this->exportTajweed($localPackagesDir, $publicPackagesDir, $flutterAssetsDir);
            } else {
                $this->exportGeneralCategory($cat, $localPackagesDir, $publicPackagesDir, $flutterAssetsDir);
            }
        }

        $this->info('Export Pipeline Completed Successfully!');
        return 0;
    }

    /**
     * Export Tajweed rule segments per Surah.
     */
    private function exportTajweed($localDir, $publicDir, $flutterDir)
    {
        $this->info('Compiling Tajweed rule presets and colors...');
        
        // Build color palette map
        $rules = TajweedRule::with('translations')->where('is_active', true)->orderBy('priority')->get();
        $uniqueColors = $rules->pluck('color_code')->filter()->unique()->values()->toArray();
        $colorMap = [];
        foreach ($uniqueColors as $idx => $color) {
            $colorMap[$color] = $idx + 1; // 1-based index
        }

        // Export tajweed_rules.json configuration
        $categoriesList = TajweedRuleCategory::with(['translations', 'tajweedRules' => function($q) {
            $q->where('is_active', true)->orderBy('priority');
        }])->orderBy('order')->get()->map(function ($cat) use ($colorMap) {
            return [
                'id' => $cat->id,
                'slug' => $cat->slug,
                'name_ku' => $cat->translations->firstWhere('locale', 'ku')?->name ?? '',
                'name_ar' => $cat->translations->firstWhere('locale', 'ar')?->name ?? '',
                'name' => $cat->translations->firstWhere('locale', 'en')?->name ?? '',
                'order' => $cat->order,
                'rules' => $cat->tajweedRules->map(fn($item) => [
                    'id' => $item->id,
                    'slug' => $item->slug,
                    'name_ar' => $item->translations->firstWhere('locale', 'ar')?->name ?? $item->name,
                    'name' => $item->translations->firstWhere('locale', 'en')?->name ?? $item->name,
                    'name_ku' => $item->translations->firstWhere('locale', 'ku')?->name ?? $item->name,
                    'color_code' => $item->color_code,
                    'color_id' => $colorMap[$item->color_code] ?? 1,
                    'description_ku' => $item->translations->firstWhere('locale', 'ku')?->description ?? $item->description,
                    'description' => $item->translations->firstWhere('locale', 'en')?->description ?? $item->description,
                ])->toArray()
            ];
        })->toArray();

        $rulesJson = json_encode($categoriesList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        File::ensureDirectoryExists("{$localDir}/tajweed");
        File::put("{$localDir}/tajweed/tajweed_rules.json", $rulesJson);

        // Export individual Surah packages (114 packages)
        for ($s = 1; $s <= 114; $s++) {
            $surah = Surah::where('number', $s)->first();
            if (!$surah) continue;

            $ayahs = Ayah::with('tajweedSegments.tajweedRule')
                ->where('surah_id', $surah->id)
                ->active()
                ->orderBy('ayah_number')
                ->get();

            $surahSegments = [];

            foreach ($ayahs as $a) {
                $text = $a->text_uthmani;
                $rawSegments = $a->tajweedSegments;
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
                        if ($this->connectsToLeft($prevChar) && $this->connectsToRight($currChar)) {
                            $connectsToLeft = true;
                        }
                    }

                    if ($end > 0 && $end < mb_strlen($text)) {
                        $lastChar = mb_substr($text, $end - 1, 1);
                        $nextChar = mb_substr($text, $end, 1);
                        if ($this->connectsToLeft($lastChar) && $this->connectsToRight($nextChar)) {
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
                        'text_segment' => $seg->text_segment ?? '',
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
                        // Re-evaluate connects_to_right flag for the merged boundary
                        $last['connects_to_right'] = $p['connects_to_right'];
                    } else {
                        $compressed[] = $p;
                    }
                }

                $surahSegments[] = [
                    'ayah_number' => $a->ayah_number,
                    'segments' => $compressed,
                ];
            }

            // Write surah directories
            $surahFolder = str_pad($s, 3, '0', STR_PAD_LEFT);
            $surahLocalDir = "{$localDir}/tajweed/{$surahFolder}";
            File::ensureDirectoryExists($surahLocalDir);

            // Compute payload
            $dataJson = json_encode($surahSegments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $checksum = hash('sha256', $dataJson);

            // Write segments and manifest
            File::put("{$surahLocalDir}/segments.json", $dataJson);
            
            $manifest = [
                'package' => "tajweed_surah_{$s}",
                'version' => $this->getCategoryVersion("tajweed_surah_{$s}"),
                'schema' => 1,
                'checksum' => $checksum,
                'records' => count($surahSegments),
                'generated_at' => now()->toIso8601String(),
            ];
            File::put("{$surahLocalDir}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));

            // Package into public zip file for OTA updates
            $zipPath = "{$publicDir}/tajweed_surah_{$s}.zip";
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFromString('segments.json', $dataJson);
                $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
                $zip->addFromString('checksum.sha256', $checksum);
                $zip->close();
            }

            // Copy plain assets directly to Flutter assets for local install seeding
            if (File::exists($flutterDir)) {
                $flutterSurahDir = "{$flutterDir}/tajweed/{$surahFolder}";
                File::ensureDirectoryExists($flutterSurahDir);
                File::copy("{$surahLocalDir}/segments.json", "{$flutterSurahDir}/segments.json");
                File::copy("{$surahLocalDir}/manifest.json", "{$flutterSurahDir}/manifest.json");
            }
        }

        // Copy rules master file to flutter assets as well
        if (File::exists($flutterDir)) {
            File::ensureDirectoryExists("{$flutterDir}/tajweed");
            File::copy("{$localDir}/tajweed/tajweed_rules.json", "{$flutterDir}/tajweed/tajweed_rules.json");
        }

        $this->info('Tajweed segments packages created successfully.');
    }

    /**
     * Export general categories (Hadith, Adhkar, Quran etc).
     */
    private function exportGeneralCategory($cat, $localDir, $publicDir, $flutterDir)
    {
        // Reuse original package data generation
        $data = $this->getPackageData($cat);
        if (empty($data)) return;

        File::ensureDirectoryExists("{$localDir}/{$cat}");
        $dataJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        File::put("{$localDir}/{$cat}/data.json", $dataJson);

        $checksum = hash('sha256', $dataJson);
        $manifest = [
            'package' => $cat,
            'version' => $this->getCategoryVersion($cat),
            'schema' => 1,
            'checksum' => $checksum,
            'records' => count($data),
            'generated_at' => now()->toIso8601String(),
        ];
        File::put("{$localDir}/{$cat}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));

        // Create Public ZIP for OTA
        $zipPath = "{$publicDir}/{$cat}.zip";
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $zip->addFromString('data.json', $dataJson);
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
            $zip->addFromString('checksum.sha256', $checksum);
            $zip->close();
        }

        // Copy to Flutter Assets for Seeding
        if (File::exists($flutterDir)) {
            $flutterCatDir = "{$flutterDir}/{$cat}";
            File::ensureDirectoryExists($flutterCatDir);
            File::copy("{$localDir}/{$cat}/data.json", "{$flutterCatDir}/data.json");
            File::copy("{$localDir}/{$cat}/manifest.json", "{$flutterCatDir}/manifest.json");
        }

        $this->info("General package {$cat} built successfully.");
    }

    private function getCategoryVersion($cat)
    {
        return (int) (cache()->get("pkg_version_{$cat}") ?? 1);
    }

    private function connectsToLeft($char)
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

    private function connectsToRight($char)
    {
        if (empty($char)) return false;
        $code = mb_ord($char, 'UTF-8');
        if ($code < 0x0600 || $code > 0x06FF) return false;
        if ($code === 0x0621) return false; // Hamza
        return true;
    }

    private function getPackageData($cat)
    {
        switch ($cat) {
            case 'quran':
                $surahsList = Surah::with('translations')->active()->get();
                $surahs = $surahsList->map(function ($s) {
                    return [
                        'type' => 'surah',
                        'number' => $s->number,
                        'name_ar' => $s->translations->firstWhere('locale', 'ar')?->name ?? '',
                        'name_en' => $s->translations->firstWhere('locale', 'en')?->name ?? '',
                        'name_ku' => $s->translations->firstWhere('locale', 'ku')?->name ?? '',
                        'total_ayahs' => $s->ayah_count,
                        'revelation_type' => $s->revelation_type,
                        'page_start' => $s->page_start,
                        'page_end' => $s->page_end,
                    ];
                })->toArray();

                $ayahsList = Ayah::with(['translations'])->active()->get();
                $ayahs = $ayahsList->map(function ($a) {
                    return [
                        'type' => 'ayah',
                        'id' => $a->id,
                        'surah_number' => $a->surah ? $a->surah->number : 1,
                        'ayah_number' => $a->ayah_number,
                        'text_uthmani' => $a->text_uthmani,
                        'page_number' => $a->page_number,
                        'juz_number' => $a->juz_number,
                        'hizb_number' => $a->hizb_number,
                        'rub_number' => $a->rub_number,
                        'translations' => $a->translations->map(fn($t) => [
                            'language_code' => $t->language_code,
                            'content' => $t->content,
                        ])->toArray(),
                    ];
                })->toArray();

                return array_merge($surahs, $ayahs);

            case 'tafsir':
                return Tafsir::with('ayah.surah')->get()->map(function ($t) {
                    return [
                        'surah_number' => $t->ayah ? $t->ayah->surah->number : 1,
                        'ayah_number' => $t->ayah ? $t->ayah->ayah_number : 1,
                        'text' => $t->content,
                        'slug' => 'tafsir-' . ($t->ayah ? $t->ayah->surah->number : 1) . '-' . ($t->ayah ? $t->ayah->ayah_number : 1),
                        'version' => 1,
                        'updated_at' => $t->updated_at ? $t->updated_at->toIso8601String() : now()->toIso8601String(),
                    ];
                })->toArray();

            case 'hadith':
                return Hadith::with('category.translations')->where('is_active', true)->get()->map(function ($h) {
                    return [
                        'id' => $h->id,
                        'category_id' => $h->category_id,
                        'category_name_ar' => $h->category ? ($h->category->translations->firstWhere('locale', 'ar')?->name ?? 'عام') : 'عام',
                        'category_name_ku' => $h->category ? ($h->category->translations->firstWhere('locale', 'ku')?->name ?? 'گشتی') : 'گشتی',
                        'arabic_text' => $h->arabic_text,
                        'translation_ku' => $h->translation_ku,
                        'translation_en' => $h->translation_en,
                        'narrator' => $h->narrator,
                        'source' => $h->source,
                        'explanation_ku' => $h->explanation_ku,
                        'explanation_en' => $h->explanation_en,
                        'order' => $h->order,
                        'is_active' => $h->is_active,
                        'slug' => $h->slug ?? "hadith-{$h->id}",
                        'version' => $h->version ?? 1,
                    ];
                })->toArray();

            case 'adhkar':
                return AdhkarCategory::with(['translations', 'adhkars'])->get()->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name_ar' => $cat->translations->firstWhere('locale', 'ar')?->name ?? '',
                        'name_ku' => $cat->translations->firstWhere('locale', 'ku')?->name ?? '',
                        'name_en' => $cat->translations->firstWhere('locale', 'en')?->name ?? '',
                        'icon' => $cat->icon,
                        'order' => $cat->order,
                        'adhkars' => $cat->adhkars->map(fn($item) => [
                            'id' => $item->id,
                            'arabic_text' => $item->arabic_text,
                            'translation_ku' => $item->translation_ku,
                            'translation_en' => $item->translation_en,
                            'description' => $item->description,
                            'count' => $item->count,
                            'source' => $item->source,
                            'version' => $item->version ?? 1,
                        ])->toArray()
                    ];
                })->toArray();

            case 'seerah':
                $path = storage_path('app/packages/seerah.json');
                return File::exists($path) ? json_decode(File::get($path), true) : [];

            case 'sahaba':
                $path = storage_path('app/packages/sahaba.json');
                return File::exists($path) ? json_decode(File::get($path), true) : [];

            case 'allah_names':
                $path = storage_path('app/packages/allah_names.json');
                return File::exists($path) ? json_decode(File::get($path), true) : [];

            case 'prayer_database':
                return PrayerTime::with('city')->get()->map(function ($pt) {
                    return [
                        'city_id' => $pt->city_id,
                        'latitude' => $pt->city ? $pt->city->lat : 36.19,
                        'longitude' => $pt->city ? $pt->city->lng : 44.01,
                        'date' => $pt->date,
                        'fajr' => substr($pt->fajr, 0, 5),
                        'sunrise' => substr($pt->sunrise, 0, 5),
                        'dhuhr' => substr($pt->dhuhr, 0, 5),
                        'asr' => substr($pt->asr, 0, 5),
                        'maghrib' => substr($pt->maghrib, 0, 5),
                        'isha' => substr($pt->isha, 0, 5),
                    ];
                })->toArray();

            case 'translations':
                return Translation::with('ayah.surah')->get()->map(function ($t) {
                    return [
                        'surah_number' => $t->ayah ? $t->ayah->surah->number : 1,
                        'ayah_number' => $t->ayah ? $t->ayah->ayah_number : 1,
                        'language_code' => $t->language_code,
                        'content' => $t->content,
                    ];
                })->toArray();

            case 'audio_metadata':
                return Reciter::with('ayahTimings')->get()->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'name_ku' => $r->name_ku ?? $r->name ?? '',
                        'name_ar' => $r->name_ar ?? $r->name ?? '',
                        'type' => $r->type ?? 'arabic',
                        'bio_ku' => $r->bio_ku ?? '',
                        'image_asset' => $r->image_asset ?? '',
                        'sample_audio_url' => $r->sample_audio_url ?? '',
                        'download_base_url' => $r->download_base_url ?? '',
                        'slug' => $r->slug ?? "reciter-{$r->id}",
                        'version' => $r->version ?? 1,
                        'surah_timings' => $r->ayahTimings->map(function ($t) {
                            $timingData = [];
                            if ($t->timing_file_path && File::exists(storage_path('app/' . $t->timing_file_path))) {
                                $timingData = json_decode(File::get(storage_path('app/' . $t->timing_file_path)), true);
                            }
                            return [
                                'surah_id' => $t->surah_id,
                                'timing_data' => $timingData,
                            ];
                        })->toArray()
                    ];
                })->toArray();

            default:
                return [];
        }
    }
}
