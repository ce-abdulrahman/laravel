<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AudioFavorite;
use App\Models\Reciter;
use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AudioFavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = AudioFavorite::where('user_id', $request->user()->id)->get();

        $reciterIds = [];
        $surahIds = [];

        foreach ($favorites as $fav) {
            $class = $fav->favoritable_type;
            if ($class === Reciter::class) {
                $reciterIds[] = $fav->favoritable_id;
            } elseif ($class === Surah::class) {
                $surahIds[] = $fav->favoritable_id;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'reciter_ids' => $reciterIds,
                'surah_ids' => $surahIds,
            ]
        ]);
    }

    public function toggle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'favoritable_type' => 'required|string|in:reciter,surah,Reciter,Surah',
            'favoritable_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $typeInput = strtolower($request->favoritable_type);
        $modelClass = $typeInput === 'reciter' ? Reciter::class : Surah::class;

        $exists = $modelClass::where('id', $request->favoritable_id)->exists();
        if (!$exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entity not found',
            ], 404);
        }

        $favorite = AudioFavorite::where('user_id', $request->user()->id)
            ->where('favoritable_type', $modelClass)
            ->where('favoritable_id', $request->favoritable_id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorite = false;
            $message = 'Favorite removed';
        } else {
            AudioFavorite::create([
                'user_id' => $request->user()->id,
                'favoritable_type' => $modelClass,
                'favoritable_id' => $request->favoritable_id,
            ]);
            $isFavorite = true;
            $message = 'Favorite added';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'is_favorite' => $isFavorite,
                'favoritable_type' => $typeInput,
                'favoritable_id' => (int) $request->favoritable_id
            ]
        ]);
    }
}
