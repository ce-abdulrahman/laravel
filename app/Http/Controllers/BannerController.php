<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $query = Banner::query()->with('surah')->orderBy('order');

        if ($search !== '') {
            $query->where('verse', 'like', "%{$search}%")
                  ->orWhere('title_arabic', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
        }

        $banners = $query->paginate(15)->withQueryString();

        return view('banners.index', [
            'banners' => $banners,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('banners.create', [
            'banner' => new Banner([
                'is_active' => true,
                'order' => 0,
            ]),
            'surahs' => Surah::orderBy('number')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateBanner($request);
        
        Banner::create($validated);

        return redirect()
            ->route('banners.index')
            ->with('success', 'بانەرەکە بە سەرکەوتوویی دروستکرا.');
    }

    public function edit(Banner $banner): View
    {
        return view('banners.edit', [
            'banner' => $banner,
            'surahs' => Surah::orderBy('number')->get(),
        ]);
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $this->validateBanner($request, $banner);

        $banner->update($validated);

        return redirect()
            ->route('banners.index')
            ->with('success', 'بانەرەکە بە سەرکەوتوویی نوێکرایەوە.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return redirect()
            ->route('banners.index')
            ->with('success', 'بانەرەکە بە سەرکەوتوویی سڕایەوە.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBanner(Request $request, ?Banner $banner = null): array
    {
        $validated = $request->validate([
            'title_arabic' => ['nullable', 'string', 'max:500'],
            'verse' => ['required', 'string'],
            'source' => ['nullable', 'string', 'max:255'],
            'surah_id' => ['nullable', 'integer', 'exists:surahs,id'],
            'ayah_number' => ['nullable', 'integer', 'min:1'],
            'order' => ['required', 'integer'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
