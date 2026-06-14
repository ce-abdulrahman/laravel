<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SettingEntry;
use App\Models\FingerprintSetting;
use App\Models\FingerprintStatistic;
use App\Models\FingerprintSessionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FingerprintAdminController extends Controller
{
    /**
     * Display the fingerprint dashboard analytics.
     */
    public function dashboard()
    {
        $totalUsers = FingerprintStatistic::where('total_sessions', '>', 0)->count();
        $totalSessions = FingerprintStatistic::sum('total_sessions');
        $avgDuration = FingerprintSessionLog::avg('duration_seconds') ?? 0;

        // Get most used feedback profile (haptic)
        $mostUsedHaptic = FingerprintSetting::select('haptic_profile', DB::raw('count(*) as qty'))
            ->groupBy('haptic_profile')
            ->orderByDesc('qty')
            ->first()?->haptic_profile ?? 'normal';

        // 1. Session growth over time (last 30 days)
        $usageGrowth = FingerprintSessionLog::selectRaw("DATE(created_at) as session_date, count(*) as count, SUM(touch_count) as total_count")
            ->groupBy('session_date')
            ->orderBy('session_date')
            ->limit(30)
            ->get();

        // 2. Counting mode distribution
        $modeDistribution = FingerprintSetting::selectRaw('count_mode, count(*) as count')
            ->groupBy('count_mode')
            ->get();

        // 3. Hourly activity distribution
        $activeHours = FingerprintSessionLog::selectRaw("strftime('%H', created_at) as hour, count(*) as count")
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return view('admin.fingerprint.dashboard', compact(
            'totalUsers',
            'totalSessions',
            'avgDuration',
            'mostUsedHaptic',
            'usageGrowth',
            'modeDistribution',
            'activeHours'
        ));
    }

    /**
     * Display user statistics list.
     */
    public function users(Request $request)
    {
        $search = $request->get('q');

        $query = User::query()
            ->join('fingerprint_statistics', 'users.id', '=', 'fingerprint_statistics.user_id')
            ->leftJoin('fingerprint_settings', 'users.id', '=', 'fingerprint_settings.user_id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'fingerprint_statistics.total_sessions',
                'fingerprint_statistics.total_counts',
                'fingerprint_statistics.avg_touch_rate',
                'fingerprint_settings.count_mode as preferred_mode',
                'fingerprint_settings.haptic_profile',
                DB::raw('(SELECT AVG(fingerprint_session_logs.duration_seconds) 
                          FROM fingerprint_session_logs 
                          JOIN tasbih_sessions ON fingerprint_session_logs.session_id = tasbih_sessions.id 
                          WHERE tasbih_sessions.user_id = users.id) as avg_duration')
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('fingerprint_statistics.total_counts')->paginate(25);

        return view('admin.fingerprint.users', compact('users', 'search'));
    }

    /**
     * Display global settings.
     */
    public function settings()
    {
        $maxHoldDuration = SettingEntry::where('key', 'fingerprint_max_hold_duration')->value('value') ?? 10;
        $maxContinuousRate = SettingEntry::where('key', 'fingerprint_max_continuous_rate')->value('value') ?? 10;
        $modeEnabled = SettingEntry::where('key', 'fingerprint_mode_enabled')->value('value') ?? '1';
        $blindEnabled = SettingEntry::where('key', 'fingerprint_blind_mode_enabled')->value('value') ?? '1';
        $focusEnabled = SettingEntry::where('key', 'fingerprint_focus_mode_enabled')->value('value') ?? '1';

        return view('admin.fingerprint.settings', compact(
            'maxHoldDuration',
            'maxContinuousRate',
            'modeEnabled',
            'blindEnabled',
            'focusEnabled'
        ));
    }

    /**
     * Update global settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'fingerprint_max_hold_duration' => 'required|integer|min:1',
            'fingerprint_max_continuous_rate' => 'required|integer|min:1',
        ]);

        SettingEntry::updateOrCreate(
            ['key' => 'fingerprint_max_hold_duration'],
            ['value' => $validated['fingerprint_max_hold_duration']]
        );

        SettingEntry::updateOrCreate(
            ['key' => 'fingerprint_max_continuous_rate'],
            ['value' => $validated['fingerprint_max_continuous_rate']]
        );

        SettingEntry::updateOrCreate(
            ['key' => 'fingerprint_mode_enabled'],
            ['value' => $request->has('fingerprint_mode_enabled') ? '1' : '0']
        );

        SettingEntry::updateOrCreate(
            ['key' => 'fingerprint_blind_mode_enabled'],
            ['value' => $request->has('fingerprint_blind_mode_enabled') ? '1' : '0']
        );

        SettingEntry::updateOrCreate(
            ['key' => 'fingerprint_focus_mode_enabled'],
            ['value' => $request->has('fingerprint_focus_mode_enabled') ? '1' : '0']
        );

        return redirect()->route('admin.fingerprint.settings')
            ->with('success', 'Fingerprint global configurations saved successfully.');
    }
}
