<?php

namespace App\Http\Controllers;

use App\Models\AdhkarCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdhkarCategoryController extends Controller implements HasMiddleware
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
        $query = AdhkarCategory::query()->with('translations')->orderBy('order');

        if ($search !== '') {
            $query->whereTranslationLikeAny('name', $search);
        }

        $categories = $query->paginate(15)->withQueryString();
        $activeLanguages = \App\Models\Language::activeList();

        return view('adhkar-categories.index', [
            'categories' => $categories,
            'search'     => $search,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    public function create(): View
    {
        return view('adhkar-categories.create', [
            'category' => new AdhkarCategory([
                'is_active' => true,
                'order'     => 0,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);

        $category = AdhkarCategory::create($validated);
        if (isset($validated['translations'])) {
            $category->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('adhkar-categories.index')
            ->with('success', __('adhkar_categories.messages.created'));
    }

    public function show(AdhkarCategory $adhkarCategory): View
    {
        $adhkarCategory->loadMissing('translations');
        $activeLanguages = \App\Models\Language::activeList();

        return view('adhkar-categories.show', [
            'category' => $adhkarCategory,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    public function edit(AdhkarCategory $adhkarCategory): View
    {
        return view('adhkar-categories.edit', [
            'category' => $adhkarCategory,
        ]);
    }

    public function update(Request $request, AdhkarCategory $adhkarCategory)
    {
        $validated = $this->validateCategory($request, $adhkarCategory);

        $adhkarCategory->update($validated);
        if (isset($validated['translations'])) {
            $adhkarCategory->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('adhkar-categories.index')
            ->with('success', __('adhkar_categories.messages.updated'));
    }

    public function destroy(AdhkarCategory $adhkarCategory)
    {
        $adhkarCategory->delete();

        return redirect()
            ->route('adhkar-categories.index')
            ->with('success', __('adhkar_categories.messages.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCategory(Request $request, ?AdhkarCategory $category = null): array
    {
        $rules = [
            'icon'    => ['nullable', 'string', 'max:255'],
            'order'   => ['required', 'integer'],
            'translations' => ['required', 'array'],
        ];

        $customAttributes = [
            'icon' => __('adhkar_categories.fields.icon'),
            'order' => __('adhkar_categories.fields.order'),
            'is_active' => __('adhkar_categories.fields.is_active'),
        ];

        foreach (\App\Models\Language::activeList() as $lang) {
            $isRequired = in_array($lang->code, ['ku', 'ar']);
            $rules["translations.{$lang->code}.name"] = $isRequired ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'];
            $customAttributes["translations.{$lang->code}.name"] = __('adhkar_categories.fields.name_' . $lang->code) ?? "Name ({$lang->name})";
        }

        $validated = $request->validate($rules, [], $customAttributes);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
