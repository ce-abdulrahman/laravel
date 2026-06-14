<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\StreakService;
use Illuminate\Http\Request;

class StreakController extends Controller
{
    protected $streakService;

    public function __construct(StreakService $streakService)
    {
        $this->streakService = $streakService;
    }

    /**
     * Update or sync user streak.
     * Expects auth:sanctum middleware context.
     */
    public function update(Request $request)
    {
        // Try to retrieve user via sanctum guard if present
        $user = $request->user('sanctum');

        // Optional inputs from mobile client
        $mobileCurrent = $request->input('current_streak');
        $mobileLongest = $request->input('longest_streak');
        $mobileLastActivity = $request->input('last_activity_date');

        if (!$user) {
            // Guest mode: return the values sent by the mobile app as a success response
            return response()->json([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'current_streak' => $mobileCurrent !== null ? (int) $mobileCurrent : 0,
                    'longest_streak' => $mobileLongest !== null ? (int) $mobileLongest : 0,
                    'last_activity_date' => $mobileLastActivity,
                ]
            ]);
        }

        $streak = $this->streakService->updateStreak(
            $user,
            $mobileCurrent,
            $mobileLongest,
            $mobileLastActivity
        );

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => [
                'current_streak' => $streak->current_streak,
                'longest_streak' => $streak->longest_streak,
                'last_activity_date' => $streak->last_activity_date ? $streak->last_activity_date->toDateString() : null,
            ]
        ]);
    }
}
