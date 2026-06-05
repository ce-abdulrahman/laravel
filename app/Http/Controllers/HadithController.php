<?php

namespace App\Http\Controllers;

use App\Models\Hadith;
use App\Models\HadithCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HadithController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');

        $query = Hadith::query()->with('category')->orderBy('category_id')->orderBy('order');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('arabic_text', 'like', "%{$search}%")
                  ->orWhere('translation_ku', 'like', "%{$search}%")
                  ->orWhere('narrator', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        $hadiths = $query->paginate(20)->withQueryString();
        $categories = HadithCategory::orderBy('order')->get();

        return view('hadiths.index', [
            'hadiths' => $hadiths,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('hadiths.create', [
            'hadith' => new Hadith([
                'order' => 0,
                'is_active' => true,
            ]),
            'categories' => HadithCategory::orderBy('order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateHadith($request);

        Hadith::create($validated);

        return redirect()
            ->route('hadiths.index')
            ->with('success', 'فەرموودەکە بە سەرکەوتوویی دروستکرا.');
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

        return redirect()
            ->route('hadiths.index')
            ->with('success', 'فەرموودەکە بە سەرکەوتوویی نوێکرایەوە.');
    }

    public function destroy(Hadith $hadith)
    {
        $hadith->delete();

        return redirect()
            ->route('hadiths.index')
            ->with('success', 'فەرموودەکە بە سەرکەوتوویی سڕایەوە.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateHadith(Request $request, ?Hadith $hadith = null): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:hadith_categories,id'],
            'arabic_text' => ['required', 'string'],
            'translation_ku' => ['required', 'string'],
            'translation_en' => ['nullable', 'string'],
            'narrator' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'explanation_ku' => ['nullable', 'string'],
            'explanation_en' => ['nullable', 'string'],
            'order' => ['required', 'integer'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
