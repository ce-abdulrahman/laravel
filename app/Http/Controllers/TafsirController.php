<?php
// app/Http/Controllers/TafsirController.php

namespace App\Http\Controllers;

use App\Models\Tafsir;
use App\Models\TafsirBook;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;

class TafsirController extends Controller
{
    /**
     * Display a listing of the tafsirs.
     */
    public function index(Request $request)
    {
        $query = Tafsir::with(['ayah.surah', 'tafsirBook'])->active();

        // فلتەر بەپێی کتێبی تەفسیر
        if ($request->filled('tafsir_book_id')) {
            $query->where('tafsir_book_id', $request->tafsir_book_id);
        }

        // فلتەر بەپێی سورەت
        if ($request->filled('surah_id')) {
            $query->whereHas('ayah', function ($q) use ($request) {
                $q->where('surah_id', $request->surah_id);
            });
        }

        // گەڕان بەپێی ناوەڕۆک
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('content', 'like', '%' . $request->search . '%')
                  ->orWhere('short_content', 'like', '%' . $request->search . '%');
            });
        }

        $tafsirs = $query->orderBy('ayah_id')
            ->orderBy('tafsir_book_id')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $tafsirBooks = TafsirBook::orderBy('name')->get();
        $surahs = Surah::orderBy('id')->get();

        $stats = [
            'total_tafsirs' => Tafsir::count(),
            'total_books' => TafsirBook::count(),
            'total_ayahs_with_tafsir' => Tafsir::distinct('ayah_id')->count('ayah_id'),
        ];

        return view('tafsirs.index', compact(
            'tafsirs', 'tafsirBooks', 'surahs', 'stats'
        ));
    }

    /**
     * Show the form for creating a new tafsir.
     */
    public function create(Request $request)
    {
        $this->authorizeAdmin();

        $tafsirBooks = TafsirBook::active()->orderBy('name')->get();
        $ayahs = Ayah::with('surah')
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        $selectedBook = null;
        $selectedAyah = null;

        if ($request->filled('tafsir_book_id')) {
            $selectedBook = TafsirBook::find($request->tafsir_book_id);
        }

        if ($request->filled('ayah_id')) {
            $selectedAyah = Ayah::with('surah')->find($request->ayah_id);
        }

        return view('tafsirs.create', compact(
            'tafsirBooks', 'ayahs', 'selectedBook', 'selectedAyah'
        ));
    }

    /**
     * Store a newly created tafsir in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'tafsir_book_id' => 'required|exists:tafsir_books,id',
            'content' => 'required|string',
            'short_content' => 'nullable|string',
            'source_reference' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // پشکنینی دووبارە نەبوون
        $exists = Tafsir::where('ayah_id', $validated['ayah_id'])
            ->where('tafsir_book_id', $validated['tafsir_book_id'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['tafsir_book_id' => __('tafsirs.validation.tafsir_exists')]);
        }

        $tafsir = Tafsir::create($validated);

        return redirect()
            ->route('tafsirs.show', $tafsir)
            ->with('success', __('tafsirs.messages.created'));
    }

    /**
     * Display the specified tafsir.
     */
    public function show(Tafsir $tafsir)
    {
        $tafsir->load(['ayah.surah', 'tafsirBook']);

        $otherTafsirs = Tafsir::where('ayah_id', $tafsir->ayah_id)
            ->where('id', '!=', $tafsir->id)
            ->with('tafsirBook')
            ->get();

        return view('tafsirs.show', compact('tafsir', 'otherTafsirs'));
    }

    /**
     * Show the form for editing the specified tafsir.
     */
    public function edit(Tafsir $tafsir)
    {
        $this->authorizeAdmin();

        $tafsirBooks = TafsirBook::orderBy('name')->get();
        $ayahs = Ayah::with('surah')
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        return view('tafsirs.edit', compact('tafsir', 'tafsirBooks', 'ayahs'));
    }

    /**
     * Update the specified tafsir in storage.
     */
    public function update(Request $request, Tafsir $tafsir)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'tafsir_book_id' => 'required|exists:tafsir_books,id',
            'content' => 'required|string',
            'short_content' => 'nullable|string',
            'source_reference' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // پشکنینی دووبارە نەبوون
        $exists = Tafsir::where('ayah_id', $validated['ayah_id'])
            ->where('tafsir_book_id', $validated['tafsir_book_id'])
            ->where('id', '!=', $tafsir->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['tafsir_book_id' => __('tafsirs.validation.tafsir_exists')]);
        }

        $tafsir->update($validated);

        return redirect()
            ->route('tafsirs.show', $tafsir)
            ->with('success', __('tafsirs.messages.updated'));
    }

    /**
     * Remove the specified tafsir from storage.
     */
    public function destroy(Tafsir $tafsir)
    {
        $this->authorizeAdmin();

        $tafsir->delete();

        return redirect()
            ->route('tafsirs.index')
            ->with('success', __('tafsirs.messages.deleted'));
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Tafsir $tafsir)
    {
        $this->authorizeAdmin();

        $tafsir->update(['is_active' => !$tafsir->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $tafsir->is_active,
            'message' => $tafsir->is_active 
                ? __('tafsirs.messages.activated') 
                : __('tafsirs.messages.deactivated'),
        ]);
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

    public function import(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $json = file_get_contents($request->file('file')->getRealPath());
        $tafsirs = json_decode($json, true);

        if (! is_array($tafsirs)) {
            return back()->with('error', 'Invalid JSON file structure.');
        }

        $tafsirBooks = TafsirBook::pluck('id', 'name')->toArray();
        $ayahs = [];
        $imported = 0;

        foreach ($tafsirs as $tafsirData) {
            $surahNumber = $tafsirData['surah_number'] ?? null;
            $ayahNumber = $tafsirData['ayah_number'] ?? null;
            $ayahId = $tafsirData['ayah_id'] ?? null;
            $tafsirBookName = $tafsirData['tafsir_book_name'] ?? null;
            $tafsirBookId = $tafsirData['tafsir_book_id'] ?? null;
            $content = $tafsirData['content'] ?? null;

            if (empty($content)) {
                continue;
            }

            if (empty($tafsirBookId)) {
                if (empty($tafsirBookName)) {
                    continue;
                }
                if (!isset($tafsirBooks[$tafsirBookName])) {
                    $book = TafsirBook::create([
                        'name' => $tafsirBookName,
                        'name_ku' => $tafsirBookName,
                        'language_code' => $tafsirData['language_code'] ?? 'ku',
                        'is_active' => true,
                    ]);
                    $tafsirBooks[$tafsirBookName] = $book->id;
                }
                $tafsirBookId = $tafsirBooks[$tafsirBookName];
            }

            if (empty($ayahId)) {
                if (empty($surahNumber) || empty($ayahNumber)) {
                    continue;
                }
                $key = "{$surahNumber}_{$ayahNumber}";
                if (!isset($ayahs[$key])) {
                    $ayah = Ayah::whereHas('surah', function ($q) use ($surahNumber) {
                        $q->where('number', $surahNumber);
                    })->where('ayah_number', $ayahNumber)->first();

                    if (!$ayah) {
                        continue;
                    }
                    $ayahs[$key] = $ayah->id;
                }
                $ayahId = $ayahs[$key];
            }

            Tafsir::updateOrCreate(
                [
                    'ayah_id' => $ayahId,
                    'tafsir_book_id' => $tafsirBookId,
                ],
                [
                    'content' => $content,
                    'short_content' => $tafsirData['short_content'] ?? null,
                    'source_reference' => $tafsirData['source_reference'] ?? null,
                    'is_active' => filter_var($tafsirData['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ]
            );
            $imported++;
        }

        return redirect()->route('tafsirs.index')->with('success', "Imported {$imported} Tafsirs successfully.");
    }
}