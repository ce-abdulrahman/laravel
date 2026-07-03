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

class BuildContentPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate offline-first content packages (ZIP + Manifest)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting content packages generation...');

        $packagesDir = public_path('packages');
        File::ensureDirectoryExists($packagesDir);

        $categories = [
            'quran',
            'tafsir',
            'hadith',
            'adhkar',
            'seerah',
            'sahaba',
            'allah_names',
            'prayer_database',
            'translations',
            'audio_metadata',
            'tajweed',
        ];

        foreach ($categories as $cat) {
            $this->info("Building package: {$cat}...");
            $data = $this->getPackageData($cat);

            if (empty($data) && in_array($cat, ['seerah', 'sahaba', 'allah_names'])) {
                $this->warn("Static package {$cat} file missing from storage, skipping...");
                continue;
            }

            // Encode to data.json
            $dataJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $tempZip = tempnam(sys_get_temp_dir(), 'pkg_') . '.zip';

            // Create ZIP archive
            $zip = new \ZipArchive();
            if ($zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                // Compute checksum of data.json
                $checksumData = hash('sha256', $dataJson);
                
                // Construct inner manifest for the client's internal verification
                $innerManifest = [
                    'package' => $cat,
                    'version' => $this->getPackageVersion($cat),
                    'supports_delta' => false,
                    'checksum' => $checksumData,
                ];

                $zip->addFromString('data.json', $dataJson);
                $zip->addFromString('manifest.json', json_encode($innerManifest, JSON_PRETTY_PRINT));
                $zip->addFromString('checksum.sha256', $checksumData);
                $zip->close();
            } else {
                $this->error("Failed to create ZIP archive for package: {$cat}");
                continue;
            }

            // Move compiled ZIP package to public/packages
            $targetZipPath = "{$packagesDir}/{$cat}.zip";
            if (File::exists($targetZipPath)) {
                File::delete($targetZipPath);
            }
            File::move($tempZip, $targetZipPath);

            // Compute ZIP checksum and signature
            $zipChecksum = hash_file('sha256', $targetZipPath);
            $signature = 'signed_' . $zipChecksum; // HMAC can be: hash_hmac('sha256', $zipChecksum, config('app.key'));

            // Write master server manifest
            $manifest = [
                'package' => $cat,
                'version' => $this->getPackageVersion($cat),
                'minimum_app_version' => '1.0.0',
                'recommended_app_version' => '1.1.0',
                'schema_version' => 1,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
                'checksum' => $zipChecksum,
                'records' => count($data),
                'compressed_size' => File::size($targetZipPath),
                'uncompressed_size' => strlen($dataJson),
                'signature' => $signature,
                'supports_delta' => false,
                'dependencies' => $this->getPackageDependencies($cat),
            ];

            File::put("{$packagesDir}/{$cat}_manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));
            $this->info("Package {$cat} built successfully!");
        }

        $this->info('All content packages built successfully.');
        return 0;
    }

    private function getPackageVersion(string $cat): int
    {
        return (int) (cache()->get("pkg_version_{$cat}") ?? 1);
    }

    private function getPackageDependencies(string $cat): array
    {
        switch ($cat) {
            case 'tafsir':
            case 'translations':
                return ['quran'];
            case 'audio_metadata':
                return ['quran'];
            default:
                return [];
        }
    }

    private function getPackageData(string $cat): array
    {
        switch ($cat) {
            case 'quran':
                $surahsList = Surah::with('translations')->active()->get();
                $surahs = $surahsList->map(function ($s) {
                    return [
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

                $ayahsList = Ayah::with(['translations', 'tajweedSegments.tajweedRule'])->active()->get();
                $ayahs = $ayahsList->map(function ($a) {
                    return [
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
                        'tajweed_segments' => $a->tajweedSegments->map(fn($s) => [
                            'start_index' => $s->start_index,
                            'end_index' => $s->end_index,
                            'rule' => $s->tajweedRule ? $s->tajweedRule->slug : '',
                        ])->toArray(),
                    ];
                })->toArray();

                return array_merge($surahs, $ayahs);

            case 'tafsir':
                return Tafsir::with('ayah.surah')->get()->map(function ($t) {
                    return [
                        'id' => $t->id,
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
                            } else if ($t->timing_file_path && File::exists(public_path($t->timing_file_path))) {
                                $timingData = json_decode(File::get(public_path($t->timing_file_path)), true);
                            }
                            return [
                                'surah_id' => $t->surah_id,
                                'timing_data' => $timingData,
                            ];
                        })->toArray()
                    ];
                })->toArray();

            case 'tajweed':
                return TajweedRuleCategory::with(['translations', 'tajweedRules.translations'])->get()->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'slug' => $cat->slug,
                        'name_ku' => $cat->translations->firstWhere('locale', 'ku')?->name ?? '',
                        'name_ar' => $cat->translations->firstWhere('locale', 'ar')?->name ?? '',
                        'name' => $cat->translations->firstWhere('locale', 'en')?->name ?? '',
                        'order' => $cat->order,
                        'rules' => $cat->tajweedRules->map(fn($item) => [
                            'slug' => $item->slug,
                            'name_ar' => $item->translations->firstWhere('locale', 'ar')?->name ?? $item->name,
                            'name' => $item->translations->firstWhere('locale', 'en')?->name ?? $item->name,
                            'name_ku' => $item->translations->firstWhere('locale', 'ku')?->name ?? $item->name,
                            'color_code' => $item->color_code,
                            'description_ku' => $item->translations->firstWhere('locale', 'ku')?->description ?? $item->description,
                            'description' => $item->translations->firstWhere('locale', 'en')?->description ?? $item->description,
                        ])->toArray()
                    ];
                })->toArray();

            default:
                return [];
        }
    }
}
