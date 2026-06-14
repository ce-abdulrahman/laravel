<?php

namespace App\Services;

use App\Models\User;
use App\Models\TasbihSession;
use App\Models\TasbihSessionLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SessionLifecycleService
{
    public function __construct(
        private readonly SessionIntegrationService $integrationService,
        private readonly SessionAnalyticsService $analyticsService
    ) {}

    /**
     * Start a new tasbih session.
     * Enforces single active session per user at the DB/transaction level.
     */
    public function startSession(User $user, ?int $dhikrId = null, ?string $customDhikrName = null): TasbihSession
    {
        return DB::transaction(function () use ($user, $dhikrId, $customDhikrName) {
            // Find and close any existing orphan active sessions first
            $activeSession = TasbihSession::where('user_id', $user->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if ($activeSession) {
                $this->resolveOrphanSession($activeSession);
            }

            $now = Carbon::now('UTC');
            $sessionDate = Carbon::now('Asia/Baghdad')->toDateString();

            // Create new session
            $session = TasbihSession::create([
                'user_id' => $user->id,
                'dhikr_id' => $dhikrId,
                'custom_dhikr_name' => $customDhikrName,
                'start_time' => $now,
                'session_date' => $sessionDate,
                'status' => 'active',
                'total_count' => 0,
                'duration_seconds' => 0,
                'avg_per_minute' => 0.00,
            ]);

            // Log start event
            $uuid = (string) \Illuminate\Support\Str::uuid();
            TasbihSessionLog::create([
                'session_id' => $session->id,
                'event_uuid' => $uuid,
                'event_type' => 'start',
                'value' => 0,
                'timestamp' => $now,
            ]);

            // Cache active session ID
            Cache::put("tasbih:active_session:{$user->id}", $session->id, 86400);

            return $session;
        });
    }

    /**
     * Increment dhikr counter (receives batch from client).
     */
    public function syncBatchIncrements(User $user, int $sessionId, array $increments): TasbihSession
    {
        return DB::transaction(function () use ($user, $sessionId, $increments) {
            $session = TasbihSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->status !== 'active' && $session->status !== 'paused') {
                throw new \Exception('Cannot increment a completed or locked session.');
            }

            $totalBatchCount = 0;

            foreach ($increments as $inc) {
                $uuid = $inc['event_uuid'] ?? null;
                if (!$uuid) {
                    continue;
                }

                // Check for deduplication
                if (TasbihSessionLog::where('event_uuid', $uuid)->exists()) {
                    continue;
                }

                $value = (int) ($inc['value'] ?? 1);
                $timestamp = isset($inc['timestamp']) ? Carbon::parse($inc['timestamp'], 'UTC') : Carbon::now('UTC');

                TasbihSessionLog::create([
                    'session_id' => $session->id,
                    'event_uuid' => $uuid,
                    'event_type' => 'increment',
                    'value' => $value,
                    'timestamp' => $timestamp,
                ]);

                $totalBatchCount += $value;
            }

            if ($totalBatchCount > 0) {
                $session->increment('total_count', $totalBatchCount);
            }

            return $session;
        });
    }

    /**
     * Pause an active session.
     */
    public function pauseSession(User $user, int $sessionId, string $eventUuid, ?string $timestamp = null): TasbihSession
    {
        return DB::transaction(function () use ($user, $sessionId, $eventUuid, $timestamp) {
            // Deduplicate event_uuid
            if (TasbihSessionLog::where('event_uuid', $eventUuid)->exists()) {
                return TasbihSession::findOrFail($sessionId);
            }

            $session = TasbihSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->status !== 'active') {
                return $session;
            }

            $time = $timestamp ? Carbon::parse($timestamp, 'UTC') : Carbon::now('UTC');

            TasbihSessionLog::create([
                'session_id' => $session->id,
                'event_uuid' => $eventUuid,
                'event_type' => 'pause',
                'value' => $session->total_count,
                'timestamp' => $time,
            ]);

            $session->status = 'paused';
            $session->save();

            return $session;
        });
    }

    /**
     * Resume a paused session.
     */
    public function resumeSession(User $user, int $sessionId, string $eventUuid, ?string $timestamp = null): TasbihSession
    {
        return DB::transaction(function () use ($user, $sessionId, $eventUuid, $timestamp) {
            if (TasbihSessionLog::where('event_uuid', $eventUuid)->exists()) {
                return TasbihSession::findOrFail($sessionId);
            }

            $session = TasbihSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->status !== 'paused') {
                return $session;
            }

            $time = $timestamp ? Carbon::parse($timestamp, 'UTC') : Carbon::now('UTC');

            TasbihSessionLog::create([
                'session_id' => $session->id,
                'event_uuid' => $eventUuid,
                'event_type' => 'resume',
                'value' => $session->total_count,
                'timestamp' => $time,
            ]);

            $session->status = 'active';
            $session->save();

            return $session;
        });
    }

    /**
     * End, calculate stats, and lock the completed session.
     */
    public function endSession(User $user, int $sessionId, string $eventUuid, ?int $finalCount = null, ?string $timestamp = null): TasbihSession
    {
        return DB::transaction(function () use ($user, $sessionId, $eventUuid, $finalCount, $timestamp) {
            $session = TasbihSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->status === 'completed') {
                return $session; // Already ended and locked
            }

            $time = $timestamp ? Carbon::parse($timestamp, 'UTC') : Carbon::now('UTC');

            // Log end event (if not duplicated)
            if (!TasbihSessionLog::where('event_uuid', $eventUuid)->exists()) {
                TasbihSessionLog::create([
                    'session_id' => $session->id,
                    'event_uuid' => $eventUuid,
                    'event_type' => 'end',
                    'value' => $finalCount ?? $session->total_count,
                    'timestamp' => $time,
                ]);
            }

            if ($finalCount !== null) {
                $session->total_count = $finalCount;
            }

            $session->end_time = $time;
            $session->status = 'completed';

            // Calculate exact active duration and rate
            $this->recalculateSessionDurations($session);
            $session->save();

            // Clear cache
            Cache::forget("tasbih:active_session:{$user->id}");

            // Integrate with outer systems (daily goals, streaks, achievements, leaderboard)
            $this->integrationService->integrateSession($user, $session);

            // Rebuild aggregates
            $this->analyticsService->aggregateDailyStats($user, $session->session_date->toDateString());

            return $session;
        });
    }

    /**
     * Closes an orphaned active session gracefully by looking at the last event timestamp.
     */
    public function resolveOrphanSession(TasbihSession $session): void
    {
        Log::info("Resolving orphaned active session #{$session->id} for User #{$session->user_id}");

        $lastLog = TasbihSessionLog::where('session_id', $session->id)
            ->orderBy('timestamp', 'desc')
            ->first();

        $endTime = $lastLog ? $lastLog->timestamp : Carbon::now('UTC');

        $session->end_time = $endTime;
        $session->status = 'completed';

        // Add final end event log
        $uuid = (string) \Illuminate\Support\Str::uuid();
        TasbihSessionLog::create([
            'session_id' => $session->id,
            'event_uuid' => $uuid,
            'event_type' => 'end',
            'value' => $session->total_count,
            'timestamp' => $endTime,
        ]);

        $this->recalculateSessionDurations($session);
        $session->save();

        // Run integrations
        $this->integrationService->integrateSession($session->user, $session);
        $this->analyticsService->aggregateDailyStats($session->user, $session->session_date->toDateString());
    }

    /**
     * Core Algorithm: Computes total duration excluding paused intervals.
     */
    private function recalculateSessionDurations(TasbihSession $session): void
    {
        $logs = TasbihSessionLog::where('session_id', $session->id)
            ->orderBy('timestamp', 'asc')
            ->get();

        $totalDuration = 0;
        $lastActiveStart = null;

        foreach ($logs as $log) {
            $type = $log->event_type;
            
            if ($type === 'start' || $type === 'resume') {
                $lastActiveStart = $log->timestamp;
            } elseif ($type === 'pause' || $type === 'end') {
                if ($lastActiveStart) {
                    $diff = $log->timestamp->diffInSeconds($lastActiveStart);
                    $totalDuration += $diff;
                    $lastActiveStart = null;
                }
            }
        }

        // Catch edge cases if end event timestamp is missing but end_time is populated
        if ($lastActiveStart && $session->end_time) {
            $diff = $session->end_time->diffInSeconds($lastActiveStart);
            $totalDuration += $diff;
        }

        $session->duration_seconds = max(1, $totalDuration);

        // Calculate average dhikr per minute
        $minutes = $session->duration_seconds / 60;
        $session->avg_per_minute = round($session->total_count / $minutes, 2);
    }
}
