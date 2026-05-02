<?php
// app/Http/Controllers/TafsirBookController.php

namespace App\Http\Controllers;

use App\Models\TafsirBook;
use Illuminate\Http\Request;

class TafsirBookController extends Controller
{
    /**
     * Display a listing of the tafsir books.
     */
    public function index(Request $request)
    {
        $query = TafsirBook::withCount('tafsirs');

        // فلتەر بەپێی زمان
        if ($request->filled('language_code')) {
            $query->where('language_code', $request->language_code);
        }

        // گەڕان بەپێی ناو یان نووسەر
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%');
            });
        }

        // فلتەر بەپێی دۆخی چالاکی
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $tafsirBooks = $query->orderBy('name')
            ->paginate($request->per_page ?? 12)
            ->withQueryString();

        $languages = $this->getAvailableLanguages();
        $stats = [
            'total_books' => TafsirBook::count(),
            'total_tafsirs' => \App\Models\Tafsir::count(),
            'active_books' => TafsirBook::where('is_active', true)->count(),
        ];

        return view('tafsir-books.index', compact('tafsirBooks', 'languages', 'stats'));
    }

    /**
     * Show the form for creating a new tafsir book.
     */
    public function create()
    {
        $this->authorizeAdmin();

        $languages = $this->getAvailableLanguages();

        return view('tafsir-books.create', compact('languages'));
    }

    /**
     * Store a newly created tafsir book in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tafsir_books,name',
            'author' => 'nullable|string|max:255',
            'language_code' => 'nullable|string|max:10',
            'short_description' => 'nullable|string',
            'source' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $tafsirBook = TafsirBook::create($validated);

        return redirect()
            ->route('tafsir-books.show', $tafsirBook)
            ->with('success', __('tafsir_books.messages.created'));
    }

    /**
     * Display the specified tafsir book.
     */
    public function show(TafsirBook $tafsirBook)
    {
        $tafsirBook->load(['tafsirs.ayah.surah']);

        $tafsirs = $tafsirBook->tafsirs()
            ->with(['ayah.surah'])
            ->orderBy('ayah_id')
            ->paginate(20);

        return view('tafsir-books.show', compact('tafsirBook', 'tafsirs'));
    }

    /**
     * Show the form for editing the specified tafsir book.
     */
    public function edit(TafsirBook $tafsirBook)
    {
        $this->authorizeAdmin();

        $languages = $this->getAvailableLanguages();

        return view('tafsir-books.edit', compact('tafsirBook', 'languages'));
    }

    /**
     * Update the specified tafsir book in storage.
     */
    public function update(Request $request, TafsirBook $tafsirBook)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tafsir_books,name,' . $tafsirBook->id,
            'author' => 'nullable|string|max:255',
            'language_code' => 'nullable|string|max:10',
            'short_description' => 'nullable|string',
            'source' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $tafsirBook->update($validated);

        return redirect()
            ->route('tafsir-books.show', $tafsirBook)
            ->with('success', __('tafsir_books.messages.updated'));
    }

    /**
     * Remove the specified tafsir book from storage.
     */
    public function destroy(TafsirBook $tafsirBook)
    {
        $this->authorizeAdmin();

        // پشکنینی ئایا تەفسیری هەیە
        if ($tafsirBook->tafsirs()->count() > 0) {
            return back()->with('error', __('tafsir_books.messages.has_tafsirs'));
        }

        $tafsirBook->delete();

        return redirect()
            ->route('tafsir-books.index')
            ->with('success', __('tafsir_books.messages.deleted'));
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(TafsirBook $tafsirBook)
    {
        $this->authorizeAdmin();

        $tafsirBook->update(['is_active' => !$tafsirBook->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $tafsirBook->is_active,
            'message' => $tafsirBook->is_active 
                ? __('tafsir_books.messages.activated') 
                : __('tafsir_books.messages.deactivated'),
        ]);
    }

    /**
     * Get available languages.
     */
    private function getAvailableLanguages(): array
    {
        return [
            'ar' => 'العربية (Arabic)',
            'ku' => 'کوردی (Kurdish)',
            'en' => 'English',
            'fa' => 'فارسی (Persian)',
            'tr' => 'Türkçe (Turkish)',
            'ur' => 'اردو (Urdu)',
            'id' => 'Bahasa Indonesia',
            'ms' => 'Bahasa Melayu',
        ];
    }

    /**
     * Authorize admin access.
     */
    private function authorizeAdmin(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, __('common.unauthorized'));
        }
    }
}