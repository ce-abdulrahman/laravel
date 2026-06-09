<?php

namespace App\Http\Controllers;

use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SurahController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin', except: ['index', 'show']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $surahsQuery = Surah::query()->with('translations')->orderBy('number');

        if ($search !== '') {
            $surahsQuery->where(function ($query) use ($search) {
                $query
                    ->where('number', $search)
                    ->orWhereTranslationLikeAny('name', $search);
            });
        }

        $surahs = $surahsQuery->paginate(20)->withQueryString();
        $activeLanguages = \App\Models\Language::activeList();

        return view('surahs.index', [
            'surahs' => $surahs,
            'search' => $search,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    public function create(): View
    {
        return view('surahs.create', [
            'surah' => new Surah([
                'is_active' => true,
                'revelation_type' => 'meccan',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSurah($request);

        $surah = Surah::create($validated);
        if (isset($validated['translations'])) {
            $surah->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('surahs.show', $surah)
            ->with('success', __('surah.messages.created'));
    }

    public function show(Surah $surah): View
    {
        $surah->loadMissing('translations');
        $activeLanguages = \App\Models\Language::activeList();

        return view('surahs.show', [
            'surah' => $surah,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    public function edit(Surah $surah): View
    {
        return view('surahs.edit', [
            'surah' => $surah,
        ]);
    }

    public function update(Request $request, Surah $surah)
    {
        $validated = $this->validateSurah($request, $surah);

        $surah->update($validated);
        if (isset($validated['translations'])) {
            $surah->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('surahs.show', $surah)
            ->with('success', __('surah.messages.updated'));
    }

    public function destroy(Surah $surah)
    {
        $surah->delete();

        return redirect()
            ->route('surahs.index')
            ->with('success', __('surah.messages.deleted'));
    }

    public function import(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, __('common.unauthorized'));
        }

        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $json = file_get_contents($request->file('file')->getRealPath());
        $surahs = json_decode($json, true);

        if (! is_array($surahs)) {
            return back()->with('error', 'Invalid JSON file structure.');
        }

        $imported = 0;
        foreach ($surahs as $surahData) {
            $number = isset($surahData['number']) ? (int) $surahData['number'] : null;
            $nameAr = trim((string) ($surahData['name_ar'] ?? ''));
            $revelationType = strtolower((string) ($surahData['revelation_type'] ?? 'meccan'));
            $ayahCount = isset($surahData['ayah_count']) ? (int) $surahData['ayah_count'] : null;

            if (! $number || $nameAr === '' || ! in_array($revelationType, ['meccan', 'medinan'], true) || ! $ayahCount) {
                continue;
            }

            $surah = Surah::updateOrCreate(
                ['number' => $number],
                [
                    'revelation_type' => $revelationType,
                    'ayah_count' => $ayahCount,
                    'page_start' => isset($surahData['page_start']) ? (int) $surahData['page_start'] : null,
                    'page_end' => isset($surahData['page_end']) ? (int) $surahData['page_end'] : null,
                    'juz_start' => isset($surahData['juz_start']) ? (int) $surahData['juz_start'] : null,
                    'juz_end' => isset($surahData['juz_end']) ? (int) $surahData['juz_end'] : null,
                    'description' => $surahData['description'] ?? null,
                    'is_active' => filter_var($surahData['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ]
            );

            $translations = array_filter([
                'ar' => ['name' => $nameAr],
                'ku' => ['name' => trim((string) ($surahData['name_ku'] ?? ''))],
                'en' => ['name' => trim((string) ($surahData['name_en'] ?? ''))],
            ], fn ($payload) => isset($payload['name']) && $payload['name'] !== '');

            if (! empty($translations)) {
                $surah->saveTranslationsFromArray($translations);
            }

            $imported++;
        }

        return redirect()->route('surahs.index')->with('success', "Imported {$imported} Surahs successfully.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSurah(Request $request, ?Surah $surah = null): array
    {
        $supportedRevelationTypes = ['meccan', 'medinan', 'Meccan', 'Medinan'];

        $rules = [
            'number' => [
                'required',
                'integer',
                'min:1',
                'max:114',
                Rule::unique('surahs', 'number')->ignore($surah),
            ],
            'revelation_type' => ['required', 'string', Rule::in($supportedRevelationTypes)],
            'ayah_count' => ['required', 'integer', 'min:1', 'max:400'],
            'page_start' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'page_end' => ['nullable', 'integer', 'min:1', 'max:1000', 'gte:page_start'],
            'juz_start' => ['nullable', 'integer', 'min:1', 'max:30'],
            'juz_end' => ['nullable', 'integer', 'min:1', 'max:30', 'gte:juz_start'],
            'description' => ['nullable', 'string'],
            'translations' => ['required', 'array'],
        ];

        $customAttributes = [
            'number' => __('surah.fields.number'),
            'revelation_type' => __('surah.fields.revelation_type'),
            'ayah_count' => __('surah.fields.ayah_count'),
            'page_start' => __('surah.fields.page_start'),
            'page_end' => __('surah.fields.page_end'),
            'juz_start' => __('surah.fields.juz_start'),
            'juz_end' => __('surah.fields.juz_end'),
            'description' => __('surah.fields.description'),
            'is_active' => __('surah.fields.is_active'),
            'translations' => __('surah.sections.translations'),
        ];

        foreach (\App\Models\Language::activeList() as $lang) {
            $isAr = $lang->code === 'ar';
            $rules["translations.{$lang->code}.name"] = $isAr ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'];
            $customAttributes["translations.{$lang->code}.name"] = __('surah.fields.name_' . $lang->code) ?? "Name ({$lang->name})";
        }

        $validated = $request->validate($rules, [], $customAttributes);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['revelation_type'] = strtolower((string) $validated['revelation_type']);

        return $validated;
    }
}
