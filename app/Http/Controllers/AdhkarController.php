<?php

namespace App\Http\Controllers;

use App\Models\Adhkar;
use App\Models\AdhkarCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdhkarController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');

        $query = Adhkar::query()->with('category')->orderBy('category_id')->orderBy('order');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('arabic_text', 'like', "%{$search}%")
                  ->orWhere('translation_ku', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        $adhkars = $query->paginate(20)->withQueryString();
        $categories = AdhkarCategory::orderBy('order')->get();

        return view('adhkars.index', [
            'adhkars' => $adhkars,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('adhkars.create', [
            'adhkar' => new Adhkar([
                'count' => 1,
                'order' => 0,
            ]),
            'categories' => AdhkarCategory::orderBy('order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAdhkar($request);

        Adhkar::create($validated);

        return redirect()
            ->route('adhkars.index')
            ->with('success', 'زیکرەکە بە سەرکەوتوویی دروستکرا.');
    }

    public function edit(Adhkar $adhkar): View
    {
        return view('adhkars.edit', [
            'adhkar' => $adhkar,
            'categories' => AdhkarCategory::orderBy('order')->get(),
        ]);
    }

    public function update(Request $request, Adhkar $adhkar)
    {
        $validated = $this->validateAdhkar($request, $adhkar);

        $adhkar->update($validated);

        return redirect()
            ->route('adhkars.index')
            ->with('success', 'زیکرەکە بە سەرکەوتوویی نوێکرایەوە.');
    }

    public function destroy(Adhkar $adhkar)
    {
        $adhkar->delete();

        return redirect()
            ->route('adhkars.index')
            ->with('success', 'زیکرەکە بە سەرکەوتوویی سڕایەوە.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAdhkar(Request $request, ?Adhkar $adhkar = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:adhkar_categories,id'],
            'arabic_text' => ['required', 'string'],
            'translation_ku' => ['nullable', 'string'],
            'translation_en' => ['nullable', 'string'],
            'count' => ['required', 'integer', 'min:1'],
            'source' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['required', 'integer'],
        ]);
    }
}
