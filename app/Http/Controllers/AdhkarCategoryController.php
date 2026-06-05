<?php

namespace App\Http\Controllers;

use App\Models\AdhkarCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdhkarCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $query = AdhkarCategory::query()->orderBy('order');

        if ($search !== '') {
            $query->where('name_ku', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
        }

        $categories = $query->paginate(15)->withQueryString();

        return view('adhkar-categories.index', [
            'categories' => $categories,
            'search'     => $search,
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

        AdhkarCategory::create($validated);

        return redirect()
            ->route('adhkar-categories.index')
            ->with('success', __('adhkar_categories.messages.created'));
    }

    public function show(AdhkarCategory $adhkarCategory): View
    {
        return view('adhkar-categories.show', [
            'category' => $adhkarCategory,
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
        $validated = $request->validate([
            'name_ku' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'icon'    => ['nullable', 'string', 'max:255'],
            'order'   => ['required', 'integer'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
