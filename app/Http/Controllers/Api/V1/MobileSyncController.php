<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ayah;
use App\Models\Bookmark;
use App\Models\ReadingHistory;
use App\Models\Note;
use App\Models\MemorizationPlan;
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
            ->leftJoin('surah_translations as st', function ($join) {
                $join->on('st.surah_id', '=', 'surahs.id')
                     ->where('st.locale', '=', 'ar');
            })
            ->select([
                'bookmarks.ayah_id as ayah_id',
                'ayahs.surah_id as surah_id',
                'ayahs.ayah_number as ayah_number',
                'ayahs.text_uthmani as text_uthmani',
                'st.name as surah_name_ar',
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

    public function syncBookmarks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bookmark_id' => 'required|string',
            'surah_number' => 'required|integer',
            'ayah_number' => 'required|integer',
            'created_at' => 'required|string',
            'updated_at' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->user()->id;
        $bookmarkId = $request->bookmark_id;
        $surahNumber = (int) $request->surah_number;
        $ayahNumber = (int) $request->ayah_number;
        $createdAt = \Carbon\Carbon::parse($request->created_at);
        $updatedAt = \Carbon\Carbon::parse($request->updated_at);

        $ayah = Ayah::where('surah_id', $surahNumber)->where('ayah_number', $ayahNumber)->first();

        $bookmark = Bookmark::where('user_id', $userId)->where('bookmark_id', $bookmarkId)->first();

        if ($bookmark) {
            $remoteUpdated = \Carbon\Carbon::parse($bookmark->updated_at);
            if ($updatedAt->greaterThan($remoteUpdated)) {
                $bookmark->timestamps = false;
                $bookmark->update([
                    'surah_number' => $surahNumber,
                    'ayah_number' => $ayahNumber,
                    'ayah_id' => $ayah ? $ayah->id : null,
                    'updated_at' => $updatedAt,
                ]);
            }
        } else {
            $bookmark = new Bookmark([
                'user_id' => $userId,
                'bookmark_id' => $bookmarkId,
                'surah_number' => $surahNumber,
                'ayah_number' => $ayahNumber,
                'ayah_id' => $ayah ? $ayah->id : null,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
            $bookmark->timestamps = false;
            $bookmark->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Bookmark synced successfully',
        ], 200);
    }

    public function syncNotes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'note_id' => 'required|string',
            'surah_number' => 'required|integer',
            'ayah_number' => 'required|integer',
            'content' => 'required|string',
            'created_at' => 'required|string',
            'updated_at' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->user()->id;
        $noteId = $request->note_id;
        $surahNumber = (int) $request->surah_number;
        $ayahNumber = (int) $request->ayah_number;
        $content = $request->content;
        $createdAt = \Carbon\Carbon::parse($request->created_at);
        $updatedAt = \Carbon\Carbon::parse($request->updated_at);

        $note = Note::where('user_id', $userId)->where('note_id', $noteId)->first();

        if ($note) {
            $remoteUpdated = \Carbon\Carbon::parse($note->updated_at);
            if ($updatedAt->greaterThan($remoteUpdated)) {
                $note->timestamps = false;
                $note->update([
                    'surah_number' => $surahNumber,
                    'ayah_number' => $ayahNumber,
                    'content' => $content,
                    'updated_at' => $updatedAt,
                ]);
            }
        } else {
            $note = new Note([
                'user_id' => $userId,
                'note_id' => $noteId,
                'surah_number' => $surahNumber,
                'ayah_number' => $ayahNumber,
                'content' => $content,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
            $note->timestamps = false;
            $note->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Note synced successfully',
        ], 200);
    }

    public function syncMemorizationPlans(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|string',
            'surah_id' => 'required|integer',
            'from_ayah' => 'required|integer',
            'to_ayah' => 'required|integer',
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'created_at' => 'required|string',
            'updated_at' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->user()->id;
        $planId = $request->plan_id;
        $surahId = (int) $request->surah_id;
        $fromAyah = (int) $request->from_ayah;
        $toAyah = (int) $request->to_ayah;
        $status = $request->status;
        $notes = $request->notes;
        $createdAt = \Carbon\Carbon::parse($request->created_at);
        $updatedAt = \Carbon\Carbon::parse($request->updated_at);

        $plan = MemorizationPlan::where('user_id', $userId)->where('plan_id', $planId)->first();

        if ($plan) {
            $remoteUpdated = \Carbon\Carbon::parse($plan->updated_at);
            if ($updatedAt->greaterThan($remoteUpdated)) {
                $plan->timestamps = false;
                $plan->update([
                    'surah_id' => $surahId,
                    'from_ayah' => $fromAyah,
                    'to_ayah' => $toAyah,
                    'status' => $status,
                    'notes' => $notes,
                    'updated_at' => $updatedAt,
                ]);
            }
        } else {
            $plan = new MemorizationPlan([
                'user_id' => $userId,
                'plan_id' => $planId,
                'surah_id' => $surahId,
                'from_ayah' => $fromAyah,
                'to_ayah' => $toAyah,
                'status' => $status,
                'notes' => $notes,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
            $plan->timestamps = false;
            $plan->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Memorization plan synced successfully',
        ], 200);
    }

    public function showPlan(Request $request, $id)
    {
        $plan = MemorizationPlan::where('user_id', $request->user()->id)
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('plan_id', $id);
                } else {
                    $q->where('plan_id', $id);
                }
            })
            ->first();

        if (!$plan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Plan not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'plan_id' => $plan->plan_id ?? (string) $plan->id,
                'surah_id' => (int) $plan->surah_id,
                'from_ayah' => (int) $plan->from_ayah,
                'to_ayah' => (int) $plan->to_ayah,
                'status' => $plan->status,
                'notes' => $plan->notes,
                'created_at' => $plan->created_at->toIso8601String(),
                'updated_at' => $plan->updated_at->toIso8601String(),
            ]
        ]);
    }

    public function syncInbox(Request $request)
    {
        $userId = $request->user()->id;
        $since = $request->query('since');

        $plansQuery = MemorizationPlan::where('user_id', $userId);
        $notesQuery = Note::where('user_id', $userId);

        if ($since) {
            try {
                $sinceDt = \Carbon\Carbon::parse($since);
                $plansQuery->where('updated_at', '>', $sinceDt);
                $notesQuery->where('updated_at', '>', $sinceDt);
            } catch (\Exception $e) {
                // Ignore parsing errors and return all
            }
        }

        $plans = $plansQuery->get()->map(function ($plan) {
            return [
                'plan_id' => $plan->plan_id ?? (string) $plan->id,
                'surah_id' => (int) $plan->surah_id,
                'from_ayah' => (int) $plan->from_ayah,
                'to_ayah' => (int) $plan->to_ayah,
                'status' => $plan->status,
                'notes' => $plan->notes,
                'created_at' => $plan->created_at->toIso8601String(),
                'updated_at' => $plan->updated_at->toIso8601String(),
            ];
        });

        $notes = $notesQuery->get()->map(function ($note) {
            return [
                'note_id' => $note->note_id,
                'surah_number' => (int) $note->surah_number,
                'ayah_number' => (int) $note->ayah_number,
                'content' => $note->content,
                'created_at' => $note->created_at->toIso8601String(),
                'updated_at' => $note->updated_at->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'memorization_plans' => $plans,
                'notes' => $notes,
            ]
        ]);
    }
}

