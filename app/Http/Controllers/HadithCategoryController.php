<?php

namespace App\Http\Controllers;

use App\Models\HadithCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HadithCategoryController extends Controller implements HasMiddleware
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
        $query = HadithCategory::query()->with('translations')->orderBy('order');

        if ($search !== '') {
            $query->whereTranslationLikeAny('name', $search);
        }

        $categories = $query->paginate(15)->withQueryString();
        $activeLanguages = \App\Models\Language::activeList();

        return view('hadith-categories.index', [
            'categories' => $categories,
            'search'     => $search,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    public function create(): View
    {
        return view('hadith-categories.create', [
            'category' => new HadithCategory([
                'is_active' => true,
                'order'     => 0,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);

        $category = HadithCategory::create($validated);
        if (isset($validated['translations'])) {
            $category->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('hadith-categories.index')
            ->with('success', __('hadith_categories.messages.created'));
    }

    public function show(HadithCategory $hadithCategory): View
    {
        $hadithCategory->loadMissing('translations');
        $activeLanguages = \App\Models\Language::activeList();

        return view('hadith-categories.show', [
            'category' => $hadithCategory,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    public function edit(HadithCategory $hadithCategory): View
    {
        return view('hadith-categories.edit', [
            'category' => $hadithCategory,
        ]);
    }

    public function update(Request $request, HadithCategory $hadithCategory)
    {
        $validated = $this->validateCategory($request, $hadithCategory);

        $hadithCategory->update($validated);
        if (isset($validated['translations'])) {
            $hadithCategory->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('hadith-categories.index')
            ->with('success', __('hadith_categories.messages.updated'));
    }

    public function destroy(HadithCategory $hadithCategory)
    {
        $hadithCategory->delete();

        return redirect()
            ->route('hadith-categories.index')
            ->with('success', __('hadith_categories.messages.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCategory(Request $request, ?HadithCategory $category = null): array
    {
        $rules = [
            'icon'    => ['nullable', 'string', 'max:255'],
            'order'   => ['required', 'integer'],
            'translations' => ['required', 'array'],
        ];

        $customAttributes = [
            'icon' => __('hadith_categories.fields.icon'),
            'order' => __('hadith_categories.fields.order'),
            'is_active' => __('hadith_categories.fields.is_active'),
        ];

        foreach (\App\Models\Language::activeList() as $lang) {
            $isRequired = in_array($lang->code, ['ku', 'ar']);
            $rules["translations.{$lang->code}.name"] = $isRequired ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'];
            $customAttributes["translations.{$lang->code}.name"] = __('hadith_categories.fields.name_' . $lang->code) ?? "Name ({$lang->name})";
        }

        $validated = $request->validate($rules, [], $customAttributes);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
