<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $bookmarks = Bookmark::where('user_id', $request->user()->id)
                            ->with(['ayah.surah', 'ayah.translations' => function ($q) {
                                $q->where('is_active', true);
                            }])
                            ->orderBy('created_at', 'desc')
                            ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $bookmarks
        ]);
    }

    public function toggle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ayah_id' => 'required|exists:ayahs,id',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $bookmark = Bookmark::where('user_id', $request->user()->id)
                           ->where('ayah_id', $request->ayah_id)
                           ->first();

        if ($bookmark) {
            $bookmark->delete();
            $message = 'Bookmark removed';
            $isBookmarked = false;
        } else {
            Bookmark::create([
                'user_id' => $request->user()->id,
                'ayah_id' => $request->ayah_id,
                'note' => $request->note,
            ]);
            $message = 'Bookmark added';
            $isBookmarked = true;
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'is_bookmarked' => $isBookmarked,
                'ayah_id' => $request->ayah_id
            ]
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $bookmark = Bookmark::where('user_id', $request->user()->id)->findOrFail($id);
        $bookmark->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark deleted successfully'
        ]);
    }
}
