<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    /**
     * Display a listing of the user's bookmarks.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Bookmark::where('user_id', $user->id)
            ->with(['ayah.surah']);

        // فلتەر بەپێی سورەت
        if ($request->filled('surah_id')) {
            $query->whereHas('ayah', function ($q) use ($request) {
                $q->where('surah_id', $request->surah_id);
            });
        }

        // گەڕان بەپێی تێبینی
        if ($request->filled('search')) {
            $query->where('note', 'like', '%' . $request->search . '%');
        }

        $bookmarks = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15)
            ->withQueryString();

        $surahs = Surah::orderBy('id')->get();

        $stats = [
            'total_bookmarks' => Bookmark::where('user_id', $user->id)->count(),
            'bookmarks_with_notes' => Bookmark::where('user_id', $user->id)
                ->whereNotNull('note')
                ->where('note', '!=', '')
                ->count(),
            'recent_bookmarks' => Bookmark::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];

        return view('bookmarks.index', compact('bookmarks', 'surahs', 'stats'));
    }

    /**
     * Store a newly created bookmark in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'note' => 'nullable|string|max:500',
        ]);

        // پشکنینی ئایا پێشتر نیشانە کراوە
        $exists = Bookmark::where('user_id', $user->id)
            ->where('ayah_id', $validated['ayah_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => __('bookmarks.messages.already_bookmarked'),
            ], 400);
        }

        $bookmark = Bookmark::create([
            'user_id' => $user->id,
            'ayah_id' => $validated['ayah_id'],
            'note' => $validated['note'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('bookmarks.messages.created'),
            'bookmark' => $bookmark,
        ]);
    }

    /**
     * Toggle bookmark (add if not exists, remove if exists).
     */
    public function toggle(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
        ]);

        $bookmark = Bookmark::where('user_id', $user->id)
            ->where('ayah_id', $validated['ayah_id'])
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            return response()->json([
                'success' => true,
                'bookmarked' => false,
                'message' => __('bookmarks.messages.removed'),
            ]);
        } else {
            Bookmark::create([
                'user_id' => $user->id,
                'ayah_id' => $validated['ayah_id'],
            ]);
            return response()->json([
                'success' => true,
                'bookmarked' => true,
                'message' => __('bookmarks.messages.created'),
            ]);
        }
    }

    /**
     * Display the specified bookmark.
     */
    public function show(Bookmark $bookmark)
    {
        $this->authorize('view', $bookmark);

        $bookmark->load(['ayah.surah', 'user']);

        // ئایەتی پێشوو و داهاتوو
        $nextAyah = Ayah::where('surah_id', $bookmark->ayah->surah_id)
            ->where('ayah_number', '>', $bookmark->ayah->ayah_number)
            ->orderBy('ayah_number')
            ->first();

        $prevAyah = Ayah::where('surah_id', $bookmark->ayah->surah_id)
            ->where('ayah_number', '<', $bookmark->ayah->ayah_number)
            ->orderBy('ayah_number', 'desc')
            ->first();

        // پشکنینی ئایا ئەم ئایەتە نیشانە کراوە
        $isBookmarked = true;
        $isFavorite = Favorite::where('user_id', Auth::id())
            ->where('ayah_id', $bookmark->ayah_id)
            ->exists();

        return view('bookmarks.show', compact('bookmark', 'nextAyah', 'prevAyah', 'isBookmarked', 'isFavorite'));
    }

    /**
     * Show the form for editing the specified bookmark.
     */
    public function edit(Bookmark $bookmark)
    {
        $this->authorize('update', $bookmark);

        $bookmark->load(['ayah.surah']);

        return view('bookmarks.edit', compact('bookmark'));
    }

    /**
     * Update the specified bookmark in storage.
     */
    public function update(Request $request, Bookmark $bookmark)
    {
        $this->authorize('update', $bookmark);

        $validated = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $bookmark->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('bookmarks.messages.updated'),
                'bookmark' => $bookmark,
            ]);
        }

        return redirect()
            ->route('bookmarks.show', $bookmark)
            ->with('success', __('bookmarks.messages.updated'));
    }

    /**
     * Remove the specified bookmark from storage.
     */
    public function destroy(Bookmark $bookmark)
    {
        $this->authorize('delete', $bookmark);

        $bookmark->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('bookmarks.messages.deleted'),
            ]);
        }

        return redirect()
            ->route('bookmarks.index')
            ->with('success', __('bookmarks.messages.deleted'));
    }

    /**
     * Export bookmarks as JSON.
     */
    public function export()
    {
        $user = Auth::user();

        $bookmarks = Bookmark::where('user_id', $user->id)
            ->with(['ayah.surah'])
            ->get()
            ->map(function ($bookmark) {
                return [
                    'surah_id' => $bookmark->ayah->surah_id,
                    'surah_name' => $bookmark->ayah->surah->name_ar,
                    'ayah_number' => $bookmark->ayah->ayah_number,
                    'ayah_text' => $bookmark->ayah->text_uthmani,
                    'note' => $bookmark->note,
                    'created_at' => $bookmark->created_at->toISOString(),
                ];
            });

        return response()->json([
            'user' => $user->name,
            'total' => $bookmarks->count(),
            'bookmarks' => $bookmarks,
        ], 200, [
            'Content-Disposition' => 'attachment; filename="bookmarks_' . date('Y-m-d') . '.json"',
        ]);
    }
}