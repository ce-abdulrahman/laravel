<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TasbihSession;
use App\Models\TasbihSessionLog;
use App\Models\TasbihSessionAggregate;
use App\Services\SessionLifecycleService;
use App\Services\SessionAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class TasbihSessionAdminController extends Controller
{
    public function __construct(
        private readonly SessionLifecycleService $lifecycleService,
        private readonly SessionAnalyticsService $analyticsService
    ) {}

    /**
     * Display the sessions overview.
     */
    public function overview()
    {
        $totalSessions = TasbihSession::count();
        $activeSessions = TasbihSession::where('status', 'active')->count();
        
        $avgDuration = TasbihSession::where('status', 'completed')
            ->avg('duration_seconds') ?? 0;
            
        $totalDhikr = TasbihSession::sum('total_count');

        $topDhikrs = TasbihSession::join('tasbihs', 'tasbih_sessions.dhikr_id', '=', 'tasbihs.id')
            ->selectRaw('tasbihs.name, COUNT(tasbih_sessions.id) as sessions_count, SUM(tasbih_sessions.total_count) as total_dhikr')
            ->groupBy('tasbihs.name')
            ->orderByDesc('total_dhikr')
            ->limit(5)
            ->get();

        $mostActiveUsers = User::join('tasbih_sessions', 'users.id', '=', 'tasbih_sessions.user_id')
            ->selectRaw('users.name, users.email, COUNT(tasbih_sessions.id) as sessions_count, SUM(tasbih_sessions.total_count) as total_dhikr')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_dhikr')
            ->limit(5)
            ->get();

        return view('admin.tasbih-sessions.overview', compact(
            'totalSessions',
            'activeSessions',
            'avgDuration',
            'totalDhikr',
            'topDhikrs',
            'mostActiveUsers'
        ));
    }

    /**
     * Display a paginated list of sessions.
     */
    public function index(Request $request)
    {
        $search = $request->get('q');
        $status = $request->get('status');
        $date = $request->get('date');

        $query = TasbihSession::query()->with(['user', 'dhikr']);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('session_date', $date);
        }

        if ($request->has('export')) {
            $format = $request->get('export') === 'json' ? 'json' : 'csv';
            return $this->export($query->orderBy('start_time', 'desc')->get(), $format);
        }

        $sessions = $query->orderBy('start_time', 'desc')->paginate(25);

        return view('admin.tasbih-sessions.index', compact('sessions', 'search', 'status', 'date'));
    }

    /**
     * Show session detail & timeline scrubber data.
     */
    public function show(int $id)
    {
        $session = TasbihSession::with(['user', 'dhikr'])->findOrFail($id);
        
        $logs = TasbihSessionLog::where('session_id', $id)
            ->orderBy('timestamp', 'asc')
            ->get();

        // Build Visual Scrubber Data: Taps per second graph coordinates
        $scrubberData = $this->buildScrubberTimeline($session, $logs);

        return view('admin.tasbih-sessions.show', compact('session', 'logs', 'scrubberData'));
    }

    /**
     * Force close stuck active/paused sessions.
     */
    public function forceClose(int $id)
    {
        $session = TasbihSession::findOrFail($id);
        
        if ($session->status !== 'completed') {
            $this->lifecycleService->resolveOrphanSession($session);
            return redirect()->route('admin.sessions.show', $id)
                ->with('success', 'Session force-closed and locked successfully.');
        }

        return redirect()->route('admin.sessions.show', $id)
            ->with('error', 'Session is already completed.');
    }

    /**
     * Delete a session.
     */
    public function destroy(int $id)
    {
        $session = TasbihSession::findOrFail($id);
        $userId = $session->user_id;
        $date = $session->session_date->toDateString();

        $session->delete();

        // Recalculate aggregates for the user on that date
        $user = User::find($userId);
        if ($user) {
            $this->analyticsService->aggregateDailyStats($user, $date);
        }

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Session deleted successfully and aggregates updated.');
    }

    /**
     * Analytics and trends page.
     */
    public function analytics()
    {
        // Total active users participation rate
        $totalUsers = User::where('role', 'user')->where('status', true)->count();
        $sessionsUsers = TasbihSessionAggregate::distinct('user_id')->count('user_id');
        $participationRate = $totalUsers > 0 ? round(($sessionsUsers / $totalUsers) * 100, 1) : 0;

        // Peak duration trend (daily average session length last 30 days)
        $dailyDurations = TasbihSession::where('status', 'completed')
            ->where('session_date', '>=', now()->subDays(30))
            ->selectRaw('session_date, AVG(duration_seconds) as avg_duration, SUM(total_count) as total_dhikr')
            ->groupBy('session_date')
            ->orderBy('session_date')
            ->get();

        // Fatigue rate index: how counts taper off as sessions grow long
        // Group taps per minute by minute buckets: Min 1-3, Min 4-6, Min 7-10, Min >10
        $fatigueStats = DB::table('tasbih_sessions')
            ->where('status', 'completed')
            ->where('duration_seconds', '>', 0)
            ->selectRaw('
                CASE 
                    WHEN duration_seconds <= 180 THEN "1-3 mins"
                    WHEN duration_seconds <= 360 THEN "4-6 mins"
                    WHEN duration_seconds <= 600 THEN "7-10 mins"
                    ELSE ">10 mins"
                END as duration_bucket,
                AVG(avg_per_minute) as avg_rate
            ')
            ->groupBy('duration_bucket')
            ->get();

        return view('admin.tasbih-sessions.analytics', compact(
            'participationRate',
            'dailyDurations',
            'fatigueStats'
        ));
    }

    /**
     * Export session data in CSV or JSON formats.
     */
    private function export($sessions, string $format)
    {
        if ($format === 'json') {
            $data = $sessions->map(function ($s) {
                return [
                    'session_id' => $s->id,
                    'user_name' => $s->user?->name,
                    'user_email' => $s->user?->email,
                    'dhikr' => $s->dhikr?->name ?? $s->custom_dhikr_name ?? 'General',
                    'start_time' => $s->start_time->toIso8601String(),
                    'end_time' => $s->end_time ? $s->end_time->toIso8601String() : null,
                    'duration_seconds' => $s->duration_seconds,
                    'total_count' => $s->total_count,
                    'avg_per_minute' => $s->avg_per_minute,
                    'status' => $s->status
                ];
            });

            return Response::json($data, 200, [
                'Content-Disposition' => 'attachment; filename="tasbih_sessions_' . date('Y_m_d') . '.json"',
            ]);
        }

        // CSV Export
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tasbih_sessions_' . date('Y_m_d') . '.csv"',
        ];

        $callback = function () use ($sessions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Session ID', 'User', 'Email', 'Dhikr', 'Start Time', 'End Time', 'Duration (s)', 'Count', 'Avg Rate', 'Status']);

            foreach ($sessions as $s) {
                fputcsv($file, [
                    $s->id,
                    $s->user?->name,
                    $s->user?->email,
                    $s->dhikr?->name ?? $s->custom_dhikr_name ?? 'General',
                    $s->start_time,
                    $s->end_time,
                    $s->duration_seconds,
                    $s->total_count,
                    $s->avg_per_minute,
                    $s->status
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Compute visual scrubber timeline array representing increments per second.
     */
    private function buildScrubberTimeline(TasbihSession $session, $logs): array
    {
        if ($logs->isEmpty()) {
            return [];
        }

        $startTime = strtotime($session->start_time);
        $endTime = $session->end_time ? strtotime($session->end_time) : time();
        $totalSpan = max(1, $endTime - $startTime);

        // Group into 2-second resolution blocks for visualization
        $binSize = 2;
        $numBins = (int) ceil($totalSpan / $binSize);
        $timeline = array_fill(0, $numBins, 0);
        $markers = [];

        foreach ($logs as $log) {
            $logTime = strtotime($log->timestamp);
            $offset = max(0, $logTime - $startTime);
            $binIndex = (int) floor($offset / $binSize);
            
            if ($binIndex >= $numBins) {
                $binIndex = $numBins - 1;
            }

            if ($log->event_type === 'increment') {
                $timeline[$binIndex] += $log->value;
            } else {
                // Keep markers (pauses, resumes, stops)
                $markers[] = [
                    'type' => $log->event_type,
                    'offset_seconds' => $offset,
                    'count_at_point' => $log->value ?? 0,
                    'time_label' => date('H:i:s', $logTime),
                ];
            }
        }

        // Construct chart payload
        $points = [];
        for ($i = 0; $i < $numBins; $i++) {
            $points[] = [
                'second' => $i * $binSize,
                'taps' => $timeline[$i],
                'rate_per_minute' => round(($timeline[$i] / $binSize) * 60)
            ];
        }

        return [
            'duration' => $totalSpan,
            'points' => $points,
            'markers' => $markers,
        ];
    }
}
