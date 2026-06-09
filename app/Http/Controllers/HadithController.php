<?php

namespace App\Http\Controllers;

use App\Models\Hadith;
use App\Models\HadithCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HadithController extends Controller implements HasMiddleware
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
        $categoryId = $request->query('category_id');

        $query = Hadith::query()->with(['category.translations', 'translations'])->orderBy('category_id')->orderBy('order');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('arabic_text', 'like', "%{$search}%")
                  ->orWhereTranslationLikeAny('translation', $search)
                  ->orWhere('narrator', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        $hadiths = $query->paginate(20)->withQueryString();
        $categories = HadithCategory::with('translations')->orderBy('order')->get();
        $activeLanguages = \App\Models\Language::activeList();

        return view('hadiths.index', [
            'hadiths' => $hadiths,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId,
            'search' => $search,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    public function create(): View
    {
        return view('hadiths.create', [
            'hadith' => new Hadith([
                'order' => 0,
                'is_active' => true,
            ]),
            'categories' => HadithCategory::with('translations')->orderBy('order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateHadith($request);

        $hadith = Hadith::create($validated);
        if (isset($validated['translations'])) {
            $hadith->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('hadiths.index')
            ->with('success', __('hadiths.messages.created'));
    }

    public function show(Hadith $hadith): View
    {
        $hadith->loadMissing(['category.translations', 'translations']);
        $activeLanguages = \App\Models\Language::activeList();

        return view('hadiths.show', [
            'hadith' => $hadith,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    public function edit(Hadith $hadith): View
    {
        return view('hadiths.edit', [
            'hadith' => $hadith,
            'categories' => HadithCategory::orderBy('order')->get(),
        ]);
    }

    public function update(Request $request, Hadith $hadith)
    {
        $validated = $this->validateHadith($request, $hadith);

        $hadith->update($validated);
        if (isset($validated['translations'])) {
            $hadith->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('hadiths.index')
            ->with('success', __('hadiths.messages.updated'));
    }

    public function destroy(Hadith $hadith)
    {
        $hadith->delete();

        return redirect()
            ->route('hadiths.index')
            ->with('success', __('hadiths.messages.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateHadith(Request $request, ?Hadith $hadith = null): array
    {
        $rules = [
            'category_id' => ['required', 'integer', 'exists:hadith_categories,id'],
            'arabic_text' => ['required', 'string'],
            'narrator' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'order' => ['required', 'integer'],
            'translations' => ['required', 'array'],
        ];

        $customAttributes = [
            'category_id' => __('hadiths.fields.category_id'),
            'arabic_text' => __('hadiths.fields.arabic_text'),
            'narrator' => __('hadiths.fields.narrator'),
            'source' => __('hadiths.fields.source'),
            'order' => __('hadiths.fields.order'),
            'is_active' => __('hadiths.fields.is_active'),
        ];

        foreach (\App\Models\Language::activeList() as $lang) {
            $isKu = $lang->code === 'ku';
            $rules["translations.{$lang->code}.translation"] = $isKu ? ['required', 'string'] : ['nullable', 'string'];
            $rules["translations.{$lang->code}.explanation"] = ['nullable', 'string'];
            
            $customAttributes["translations.{$lang->code}.translation"] = "Translation ({$lang->name})";
            $customAttributes["translations.{$lang->code}.explanation"] = "Explanation ({$lang->name})";
        }

        $validated = $request->validate($rules, [], $customAttributes);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
