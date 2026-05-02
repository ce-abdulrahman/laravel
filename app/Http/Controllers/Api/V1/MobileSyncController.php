<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ayah;
use App\Models\Bookmark;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Lightweight mobile sync endpoints (v1.1).
 *
 * Goal: minimal payload for Flutter while reusing Sanctum auth.
 */
class MobileSyncController extends Controller
{
    public function bookmarks(Request $request)
    {
        $userId = $request->user()->id;

        $rows = Bookmark::query()
            ->where('user_id', $userId)
            ->join('ayahs', 'ayahs.id', '=', 'bookmarks.ayah_id')
            ->join('surahs', 'surahs.id', '=', 'ayahs.surah_id')
            ->select([
                'bookmarks.ayah_id as ayah_id',
                'ayahs.surah_id as surah_id',
                'ayahs.ayah_number as ayah_number',
                'ayahs.text_uthmani as text_uthmani',
                'surahs.name_ar as surah_name_ar',
                'bookmarks.created_at as created_at',
            ])
            ->orderByDesc('bookmarks.created_at')
            ->get();

        // keep payload small: snippet on server
        $data = $rows->map(function ($r) {
            $text = (string) $r->text_uthmani;
            $snippet = mb_strlen($text) > 90 ? (mb_substr($text, 0, 90) . '…') : $text;

            return [
                'ayah_id' => (int) $r->ayah_id,
                'surah_id' => (int) $r->surah_id,
                'ayah_number' => (int) $r->ayah_number,
                'text_uthmani' => $snippet,
                'surah_name_ar' => $r->surah_name_ar,
                'created_at_ms' => optional($r->created_at)->getTimestampMs(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Merge-style sync: ensure these ayah_ids are bookmarked for the user.
     * Returns the full server bookmark list (same as GET bookmarks).
     */
    public function upsertBookmarks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ayah_ids' => 'required|array|min:1',
            'ayah_ids.*' => 'integer|exists:ayahs,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->user()->id;
        $ayahIds = array_values(array_unique($request->ayah_ids));

        foreach ($ayahIds as $ayahId) {
            Bookmark::query()->firstOrCreate([
                'user_id' => $userId,
                'ayah_id' => $ayahId,
            ]);
        }

        return $this->bookmarks($request);
    }

    public function lastRead(Request $request)
    {
        $userId = $request->user()->id;

        $row = ReadingHistory::query()
            ->where('user_id', $userId)
            ->join('ayahs', 'ayahs.id', '=', 'reading_histories.ayah_id')
            ->select([
                'reading_histories.ayah_id as ayah_id',
                'reading_histories.last_read_at as last_read_at',
                'ayahs.surah_id as surah_id',
                'ayahs.ayah_number as ayah_number',
            ])
            ->orderByDesc('reading_histories.last_read_at')
            ->first();

        if (! $row) {
            return response()->json([
                'status' => 'success',
                'data' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'ayah_id' => (int) $row->ayah_id,
                'surah_id' => (int) $row->surah_id,
                'ayah_number' => (int) $row->ayah_number,
                'last_read_at_ms' => optional($row->last_read_at)->getTimestampMs(),
            ],
        ]);
    }

    /**
     * Save last read position. Client may provide a timestamp (ms) to support latest-wins.
     */
    public function saveLastRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ayah_id' => 'required|integer|exists:ayahs,id',
            'last_read_at_ms' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->user()->id;
        $ayahId = (int) $request->ayah_id;

        $ayah = Ayah::query()->select(['id', 'surah_id', 'ayah_number'])->findOrFail($ayahId);

        $dt = $request->last_read_at_ms
            ? now()->setTimestampMs((int) $request->last_read_at_ms)
            : now();

        ReadingHistory::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'ayah_id' => $ayahId,
            ],
            [
                'last_read_at' => $dt,
                'seconds_spent' => 0,
            ]
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'ayah_id' => $ayahId,
                'surah_id' => (int) $ayah->surah_id,
                'ayah_number' => (int) $ayah->ayah_number,
                'last_read_at_ms' => $dt->getTimestampMs(),
            ],
        ]);
    }
}

