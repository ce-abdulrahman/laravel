<?php

namespace App\Http\Controllers;

use App\Models\Adhkar;
use App\Models\AdhkarCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdhkarController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin', except: ['index', 'show']),
        ];
    }
    public function index(Request $request): View
    {
        $search     = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');

        $query = Adhkar::query()->with(['category.translations', 'translations'])->orderBy('category_id')->orderBy('order');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('arabic_text', 'like', "%{$search}%")
                  ->orWhereTranslationLikeAny('translation', $search)
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        $adhkars    = $query->paginate(20)->withQueryString();
        $categories = AdhkarCategory::with('translations')->orderBy('order')->get();
        $activeLanguages = \App\Models\Language::activeList();

        return view('adhkars.index', [
            'adhkars'            => $adhkars,
            'categories'         => $categories,
            'selectedCategoryId' => $categoryId,
            'search'             => $search,
            'activeLanguages'    => $activeLanguages,
        ]);
    }

    public function create(): View
    {
        return view('adhkars.create', [
            'adhkar'     => new Adhkar(['count' => 1, 'order' => 0]),
            'categories' => AdhkarCategory::with('translations')->orderBy('order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAdhkar($request);

        $adhkar = Adhkar::create($validated);
        if (isset($validated['translations'])) {
            $adhkar->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('adhkars.index')
            ->with('success', __('adhkars.messages.created'));
    }

    public function show(Adhkar $adhkar): View
    {
        $adhkar->loadMissing(['category.translations', 'translations']);
        $activeLanguages = \App\Models\Language::activeList();

        return view('adhkars.show', [
            'adhkar' => $adhkar,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    public function edit(Adhkar $adhkar): View
    {
        return view('adhkars.edit', [
            'adhkar'     => $adhkar,
            'categories' => AdhkarCategory::orderBy('order')->get(),
        ]);
    }

    public function update(Request $request, Adhkar $adhkar)
    {
        $validated = $this->validateAdhkar($request, $adhkar);

        $adhkar->update($validated);
        if (isset($validated['translations'])) {
            $adhkar->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('adhkars.index')
            ->with('success', __('adhkars.messages.updated'));
    }

    public function destroy(Adhkar $adhkar)
    {
        $adhkar->delete();

        return redirect()
            ->route('adhkars.index')
            ->with('success', __('adhkars.messages.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAdhkar(Request $request, ?Adhkar $adhkar = null): array
    {
        $rules = [
            'category_id'    => ['required', 'integer', 'exists:adhkar_categories,id'],
            'arabic_text'    => ['required', 'string'],
            'count'          => ['required', 'integer', 'min:1'],
            'source'         => ['nullable', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'order'          => ['required', 'integer'],
            'translations'   => ['required', 'array'],
        ];

        $customAttributes = [
            'category_id' => __('adhkars.fields.category'),
            'arabic_text' => __('adhkars.fields.arabic_text'),
            'count' => __('adhkars.fields.count'),
            'source' => __('adhkars.fields.source'),
            'description' => __('adhkars.fields.description'),
            'order' => __('adhkars.fields.order'),
        ];

        foreach (\App\Models\Language::activeList() as $lang) {
            $rules["translations.{$lang->code}.translation"] = ['nullable', 'string'];
            $customAttributes["translations.{$lang->code}.translation"] = "Translation ({$lang->name})";
        }

        return $request->validate($rules, [], $customAttributes);
    }
}
