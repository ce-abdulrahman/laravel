<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the user's favorites.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Favorite::where('user_id', $user->id)
            ->with(['ayah.surah']);

        // فلتەر بەپێی سورەت
        if ($request->filled('surah_id')) {
            $query->whereHas('ayah', function ($q) use ($request) {
                $q->where('surah_id', $request->surah_id);
            });
        }

        $favorites = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $surahs = Surah::orderBy('id')->get();

        $stats = [
            'total_favorites' => Favorite::where('user_id', $user->id)->count(),
            'unique_surahs' => Favorite::where('user_id', $user->id)
                ->join('ayahs', 'favorites.ayah_id', '=', 'ayahs.id')
                ->distinct('ayahs.surah_id')
                ->count('ayahs.surah_id'),
            'recent_favorites' => Favorite::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];

        return view('favorites.index', compact('favorites', 'surahs', 'stats'));
    }

    /**
     * Store a newly created favorite in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
        ]);

        // پشکنینی ئایا پێشتر زیاد کراوە
        $exists = Favorite::where('user_id', $user->id)
            ->where('ayah_id', $validated['ayah_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => __('favorites.messages.already_favorited'),
            ], 400);
        }

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'ayah_id' => $validated['ayah_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => __('favorites.messages.created'),
            'favorite' => $favorite,
        ]);
    }

    /**
     * Toggle favorite (add if not exists, remove if exists).
     */
    public function toggle(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
        ]);

        $favorite = Favorite::where('user_id', $user->id)
            ->where('ayah_id', $validated['ayah_id'])
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'success' => true,
                'favorited' => false,
                'message' => __('favorites.messages.removed'),
            ]);
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'ayah_id' => $validated['ayah_id'],
            ]);
            return response()->json([
                'success' => true,
                'favorited' => true,
                'message' => __('favorites.messages.created'),
            ]);
        }
    }

    /**
     * Remove the specified favorite from storage.
     */
    public function destroy(Favorite $favorite)
    {
        $this->authorize('delete', $favorite);

        $favorite->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('favorites.messages.deleted'),
            ]);
        }

        return redirect()
            ->route('favorites.index')
            ->with('success', __('favorites.messages.deleted'));
    }

    /**
     * Remove multiple favorites at once.
     */
    public function bulkDelete(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:favorites,id',
        ]);

        Favorite::where('user_id', $user->id)
            ->whereIn('id', $validated['ids'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => __('favorites.messages.bulk_deleted'),
        ]);
    }

    /**
     * Check if an ayah is favorited.
     */
    public function check(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
        ]);

        $exists = Favorite::where('user_id', $user->id)
            ->where('ayah_id', $validated['ayah_id'])
            ->exists();

        return response()->json([
            'favorited' => $exists,
        ]);
    }
}