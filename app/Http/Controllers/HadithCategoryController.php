<?php

namespace App\Http\Controllers;

use App\Models\HadithCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HadithCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $query = HadithCategory::query()->orderBy('order');

        if ($search !== '') {
            $query->where('name_ku', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
        }

        $categories = $query->paginate(15)->withQueryString();

        return view('hadith-categories.index', [
            'categories' => $categories,
            'search'     => $search,
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

        HadithCategory::create($validated);

        return redirect()
            ->route('hadith-categories.index')
            ->with('success', __('hadith_categories.messages.created'));
    }

    public function show(HadithCategory $hadithCategory): View
    {
        return view('hadith-categories.show', [
            'category' => $hadithCategory,
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
