<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
                            ->with(['ayah.surah'])
                            ->orderBy('created_at', 'desc')
                            ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $favorites
        ]);
    }

    public function toggle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ayah_id' => 'required|exists:ayahs,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $favorite = Favorite::where('user_id', $request->user()->id)
                           ->where('ayah_id', $request->ayah_id)
                           ->first();

        if ($favorite) {
            $favorite->delete();
            $message = 'Favorite removed';
            $isFavorite = false;
        } else {
            Favorite::create([
                'user_id' => $request->user()->id,
                'ayah_id' => $request->ayah_id,
            ]);
            $message = 'Favorite added';
            $isFavorite = true;
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'is_favorite' => $isFavorite,
                'ayah_id' => $request->ayah_id
            ]
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $favorite = Favorite::where('user_id', $request->user()->id)->findOrFail($id);
        $favorite->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Favorite deleted successfully'
        ]);
    }
}
