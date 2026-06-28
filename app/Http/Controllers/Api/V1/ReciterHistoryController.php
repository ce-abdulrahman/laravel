<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Reciter;
use App\Models\ReciterUsageHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReciterHistoryController extends Controller
{
    /**
     * Log a reciter select event.
     * POST /api/v1/reciters/{id}/select
     */
    public function select(Request $request, $id)
    {
        $reciter = Reciter::active()->findOrFail($id);
        $user = Auth::user() ?? $request->user('sanctum');
        $userId = $user ? $user->id : null;

        $history = ReciterUsageHistory::where('reciter_id', $reciter->id)
            ->where('user_id', $userId)
            ->first();

        if ($history) {
            $history->increment('usage_count');
            $history->update(['last_used_at' => now()]);
        } else {
            $history = ReciterUsageHistory::create([
                'reciter_id' => $reciter->id,
                'user_id' => $userId,
                'last_used_at' => now(),
                'usage_count' => 1,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => [
                'reciter_id' => $reciter->id,
                'usage_count' => $history->usage_count,
                'last_used_at' => $history->last_used_at->toIso8601String(),
            ]
        ]);
    }

    /**
     * Return recently selected reciters.
     * GET /api/v1/reciters/recent
     */
    public function recent(Request $request)
    {
        $user = Auth::user() ?? $request->user('sanctum');
        $userId = $user ? $user->id : null;

        $history = ReciterUsageHistory::with('reciter')
            ->where('user_id', $userId)
            ->orderBy('last_used_at', 'desc')
            ->orderBy('usage_count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                return $item->reciter;
            })
            ->filter();

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $history->values()
        ]);
    }
}
