<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ayah;
use App\Models\Surah;
use App\Models\Translation;
use App\Models\Tafsir;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across Quran text, translations, and tafsirs
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'type' => 'nullable|in:all,ayah,translation,tafsir,surah',
            'surah_id' => 'nullable|integer|exists:surahs,number',
            'language_code' => 'nullable|string|size:2',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $request->q;
        $type = $request->type ?? 'all';
        $perPage = $request->per_page ?? 20;
        $results = [];

        // Search in Ayahs (Uthmani text)
        if (in_array($type, ['all', 'ayah'])) {
            $ayahQuery = Ayah::active()
                ->with(['surah'])
                ->where(function ($q) use ($query) {
                    $q->where('text_uthmani', 'like', "%{$query}%")
                      ->orWhere('text_simple', 'like', "%{$query}%");
                });

            if ($request->has('surah_id')) {
                $ayahQuery->whereHas('surah', function ($q) use ($request) {
                    $q->where('number', $request->surah_id);
                });
            }

            $ayahs = $ayahQuery->orderBy('surah_id')
                              ->orderBy('ayah_number')
                              ->limit(50)
                              ->get()
                              ->map(function ($ayah) use ($query) {
                                  return [
                                      'type' => 'ayah',
                                      'id' => $ayah->id,
                                      'surah_id' => $ayah->surah_id,
                                      'surah_name' => $ayah->surah->name_en ?? $ayah->surah->name_ar,
                                      'surah_name_ar' => $ayah->surah->name_ar,
                                      'ayah_number' => $ayah->ayah_number,
                                      'text' => $ayah->text_uthmani,
                                      'text_simple' => $ayah->text_simple,
                                      'highlight' => $this->highlightText($ayah->text_uthmani, $query),
                                      'page_number' => $ayah->page_number,
                                      'juz_number' => $ayah->juz_number,
                                  ];
                              });

            $results['ayahs'] = $ayahs;
        }

        // Search in Translations
        if (in_array($type, ['all', 'translation'])) {
            $translationQuery = Translation::where('is_active', true)
                ->with(['ayah.surah'])
                ->where('content', 'like', "%{$query}%");

            if ($request->has('language_code')) {
                $translationQuery->where('language_code', $request->language_code);
            }

            if ($request->has('surah_id')) {
                $translationQuery->whereHas('ayah.surah', function ($q) use ($request) {
                    $q->where('number', $request->surah_id);
                });
            }

            $translations = $translationQuery->limit(50)
                ->get()
                ->map(function ($translation) use ($query) {
                    return [
                        'type' => 'translation',
                        'id' => $translation->id,
                        'ayah_id' => $translation->ayah_id,
                        'surah_id' => $translation->ayah->surah_id,
                        'surah_name' => $translation->ayah->surah->name_en ?? $translation->ayah->surah->name_ar,
                        'ayah_number' => $translation->ayah->ayah_number,
                        'language_code' => $translation->language_code,
                        'translator' => $translation->translator_name,
                        'text' => $translation->content,
                        'highlight' => $this->highlightText($translation->content, $query),
                        'original_text' => $translation->ayah->text_uthmani,
                    ];
                });

            $results['translations'] = $translations;
        }

        // Search in Tafsirs
        if (in_array($type, ['all', 'tafsir'])) {
            $tafsirQuery = Tafsir::active()
                ->with(['ayah.surah', 'tafsirBook'])
                ->where(function ($q) use ($query) {
                    $q->where('content', 'like', "%{$query}%")
                      ->orWhere('short_content', 'like', "%{$query}%");
                });

            if ($request->has('surah_id')) {
                $tafsirQuery->whereHas('ayah.surah', function ($q) use ($request) {
                    $q->where('number', $request->surah_id);
                });
            }

            $tafsirs = $tafsirQuery->limit(50)
                ->get()
                ->map(function ($tafsir) use ($query) {
                    return [
                        'type' => 'tafsir',
                        'id' => $tafsir->id,
                        'ayah_id' => $tafsir->ayah_id,
                        'surah_id' => $tafsir->ayah->surah_id,
                        'surah_name' => $tafsir->ayah->surah->name_en ?? $tafsir->ayah->surah->name_ar,
                        'ayah_number' => $tafsir->ayah->ayah_number,
                        'tafsir_book' => $tafsir->tafsirBook->name ?? null,
                        'author' => $tafsir->tafsirBook->author ?? null,
                        'text' => $tafsir->short_content ?? $this->truncateText($tafsir->content, 200),
                        'full_text' => $tafsir->content,
                        'highlight' => $this->highlightText($tafsir->short_content ?? $tafsir->content, $query),
                    ];
                });

            $results['tafsirs'] = $tafsirs;
        }

        // Search in Surahs
        if (in_array($type, ['all', 'surah'])) {
            $surahs = Surah::active()
                ->where(function ($q) use ($query) {
                    $q->where('name_ar', 'like', "%{$query}%")
                      ->orWhere('name_en', 'like', "%{$query}%")
                      ->orWhere('name_ku', 'like', "%{$query}%")
                      ->orWhere('name_transliteration', 'like', "%{$query}%");
                })
                ->limit(20)
                ->get()
                ->map(function ($surah) use ($query) {
                    return [
                        'type' => 'surah',
                        'id' => $surah->id,
                        'number' => $surah->number,
                        'name_ar' => $surah->name_ar,
                        'name_en' => $surah->name_en,
                        'name_ku' => $surah->name_ku,
                        'revelation_type' => $surah->revelation_type,
                        'ayah_count' => $surah->ayah_count,
                    ];
                });

            $results['surahs'] = $surahs;
        }

        // Get total counts
        $counts = [
            'ayahs' => count($results['ayahs'] ?? []),
            'translations' => count($results['translations'] ?? []),
            'tafsirs' => count($results['tafsirs'] ?? []),
            'surahs' => count($results['surahs'] ?? []),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Search results',
            'query' => $query,
            'type' => $type,
            'counts' => $counts,
            'data' => $results,
        ]);
    }

    /**
     * Quick search suggestions (for autocomplete)
     */
    public function suggestions(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:50',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $query = $request->q;
        $limit = $request->limit ?? 10;

        $suggestions = [];

        // Surah name suggestions
        $surahs = Surah::active()
            ->where('name_ar', 'like', "{$query}%")
            ->orWhere('name_en', 'like', "{$query}%")
            ->orWhere('name_transliteration', 'like', "{$query}%")
            ->limit(5)
            ->get()
            ->map(function ($surah) {
                return [
                    'type' => 'surah',
                    'id' => $surah->id,
                    'text' => $surah->name_en ?? $surah->name_ar,
                    'subtitle' => "Surah {$surah->number} - {$surah->ayah_count} Ayahs",
                ];
            });

        $suggestions = array_merge($suggestions, $surahs->toArray());

        // Ayah text suggestions
        $ayahs = Ayah::active()
            ->with('surah')
            ->where('text_uthmani', 'like', "%{$query}%")
            ->limit($limit)
            ->get()
            ->map(function ($ayah) {
                return [
                    'type' => 'ayah',
                    'id' => $ayah->id,
                    'text' => $this->truncateText($ayah->text_simple ?? $ayah->text_uthmani, 50),
                    'subtitle' => "{$ayah->surah->name_en}:{$ayah->ayah_number}",
                ];
            });

        $suggestions = array_merge($suggestions, $ayahs->toArray());

        // Translation suggestions
        $translations = Translation::where('is_active', true)
            ->with('ayah.surah')
            ->where('content', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function ($translation) {
                return [
                    'type' => 'translation',
                    'id' => $translation->ayah_id,
                    'text' => $this->truncateText($translation->content, 50),
                    'subtitle' => "Translation - {$translation->ayah->surah->name_en}:{$translation->ayah->ayah_number}",
                ];
            });

        $suggestions = array_merge($suggestions, $translations->toArray());

        return response()->json([
            'status' => 'success',
            'data' => $suggestions,
        ]);
    }

    /**
     * Advanced search with filters
     */
    public function advanced(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'surah_id' => 'nullable|integer|exists:surahs,number',
            'juz_number' => 'nullable|integer|min:1|max:30',
            'page_number' => 'nullable|integer|min:1|max:604',
            'revelation_type' => 'nullable|in:meccan,medinan',
            'language_code' => 'nullable|string|size:2',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $request->q;
        $perPage = $request->per_page ?? 20;

        $ayahQuery = Ayah::active()
            ->with([
                'surah',
                'translations' => function ($q) use ($request) {
                    $q->where('is_active', true);
                    if ($request->has('language_code')) {
                        $q->where('language_code', $request->language_code);
                    }
                },
                'tafsirs' => function ($q) {
                    $q->active()->with('tafsirBook')->limit(1);
                }
            ])
            ->where(function ($q) use ($query) {
                $q->where('text_uthmani', 'like', "%{$query}%")
                  ->orWhere('text_simple', 'like', "%{$query}%");
            });

        // Apply filters
        if ($request->has('surah_id')) {
            $ayahQuery->whereHas('surah', function ($q) use ($request) {
                $q->where('number', $request->surah_id);
            });
        }

        if ($request->has('juz_number')) {
            $ayahQuery->where('juz_number', $request->juz_number);
        }

        if ($request->has('page_number')) {
            $ayahQuery->where('page_number', $request->page_number);
        }

        if ($request->has('revelation_type')) {
            $ayahQuery->whereHas('surah', function ($q) use ($request) {
                $q->where('revelation_type', $request->revelation_type);
            });
        }

        $results = $ayahQuery->orderBy('surah_id')
                            ->orderBy('ayah_number')
                            ->paginate($perPage);

        // Add highlight to results
        $results->getCollection()->transform(function ($ayah) use ($query) {
            $ayah->highlight_uthmani = $this->highlightText($ayah->text_uthmani, $query);
            $ayah->highlight_simple = $ayah->text_simple ? $this->highlightText($ayah->text_simple, $query) : null;
            return $ayah;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Advanced search results',
            'query' => $query,
            'filters' => $request->only(['surah_id', 'juz_number', 'page_number', 'revelation_type']),
            'data' => $results,
        ]);
    }

    /**
     * Search by Juz
     */
    public function searchByJuz(Request $request, $juzNumber)
    {
        $ayahs = Ayah::active()
            ->with('surah')
            ->where('juz_number', $juzNumber)
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $ayahs,
        ]);
    }

    /**
     * Search by Page
     */
    public function searchByPage(Request $request, $pageNumber)
    {
        $ayahs = Ayah::active()
            ->with(['surah', 'translations'])
            ->where('page_number', $pageNumber)
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $ayahs,
        ]);
    }

    /**
     * Get all searchable Juz numbers with their ranges
     */
    public function getJuzList()
    {
        $juzList = \DB::table('ayahs')
            ->select('juz_number')
            ->selectRaw('MIN(surah_id) as start_surah_id')
            ->selectRaw('MAX(surah_id) as end_surah_id')
            ->selectRaw('MIN(ayah_number) as start_ayah')
            ->selectRaw('MAX(ayah_number) as end_ayah')
            ->whereNotNull('juz_number')
            ->groupBy('juz_number')
            ->orderBy('juz_number')
            ->get()
            ->map(function ($juz) {
                $startSurah = Surah::find($juz->start_surah_id);
                $endSurah = Surah::find($juz->end_surah_id);

                return [
                    'juz_number' => $juz->juz_number,
                    'start_surah' => [
                        'id' => $startSurah->id,
                        'name' => $startSurah->name_en ?? $startSurah->name_ar,
                        'ayah' => $juz->start_ayah,
                    ],
                    'end_surah' => [
                        'id' => $endSurah->id,
                        'name' => $endSurah->name_en ?? $endSurah->name_ar,
                        'ayah' => $juz->end_ayah,
                    ],
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $juzList,
        ]);
    }

    /**
     * Helper: Add HTML highlight markers to text
     */
    private function highlightText($text, $query)
    {
        if (empty($text) || empty($query)) {
            return $text;
        }

        // Escape special regex characters
        $escapedQuery = preg_quote($query, '/');

        // Add highlight markers
        $highlighted = preg_replace(
            "/($escapedQuery)/iu",
            '<mark>$1</mark>',
            $text
        );

        return $highlighted;
    }

    /**
     * Helper: Truncate text to specified length
     */
    private function truncateText($text, $length = 100)
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . '...';
    }
}
