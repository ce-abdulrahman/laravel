<?php

namespace App\Http\Controllers;

use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SurahController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $surahsQuery = Surah::query()->orderBy('number');

        if ($search !== '') {
            $surahsQuery->where(function ($query) use ($search) {
                $query
                    ->where('number', $search)
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_ku', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $surahs = $surahsQuery->paginate(20)->withQueryString();

        return view('surahs.index', [
            'surahs' => $surahs,
            'search' => $search,
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

        return redirect()
            ->route('surahs.show', $surah)
            ->with('success', __('surah.messages.created'));
    }

    public function show(Surah $surah): View
    {
        return view('surahs.show', [
            'surah' => $surah,
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

    /**
     * @return array<string, mixed>
     */
    private function validateSurah(Request $request, ?Surah $surah = null): array
    {
        $supportedRevelationTypes = ['meccan', 'medinan', 'Meccan', 'Medinan'];

        $validated = $request->validate([
            'number' => [
                'required',
                'integer',
                'min:1',
                'max:114',
                Rule::unique('surahs', 'number')->ignore($surah),
            ],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_ku' => ['nullable', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'revelation_type' => ['required', 'string', Rule::in($supportedRevelationTypes)],
            'ayah_count' => ['required', 'integer', 'min:1', 'max:400'],
            'page_start' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'page_end' => ['nullable', 'integer', 'min:1', 'max:1000', 'gte:page_start'],
            'juz_start' => ['nullable', 'integer', 'min:1', 'max:30'],
            'juz_end' => ['nullable', 'integer', 'min:1', 'max:30', 'gte:juz_start'],
            'description' => ['nullable', 'string'],
        ], [], [
            'number' => __('surah.fields.number'),
            'name_ar' => __('surah.fields.name_ar'),
            'name_ku' => __('surah.fields.name_ku'),
            'name_en' => __('surah.fields.name_en'),
            'revelation_type' => __('surah.fields.revelation_type'),
            'ayah_count' => __('surah.fields.ayah_count'),
            'page_start' => __('surah.fields.page_start'),
            'page_end' => __('surah.fields.page_end'),
            'juz_start' => __('surah.fields.juz_start'),
            'juz_end' => __('surah.fields.juz_end'),
            'description' => __('surah.fields.description'),
            'is_active' => __('surah.fields.is_active'),
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['revelation_type'] = strtolower((string) $validated['revelation_type']);

        return $validated;
    }
}
