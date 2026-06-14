<?php

namespace App\Http\Controllers;

use App\Models\ReminderLog;
use App\Models\ReminderStatistic;
use App\Models\ReminderTemplate;
use App\Models\User;
use App\Models\UserReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * AdminReminderController — User monitoring and analytics dashboard.
 */
class AdminReminderController extends Controller
{
    /**
     * GET /reminders/users — User reminder monitoring.
     */
    public function users(Request $request): View
    {
        $q = $request->input('q');
        $type = $request->input('type');

        $query = User::query()
            ->with(['reminders' => fn($q) => $q->where('enabled', true)])
            ->withCount(['reminderLogs as logs_count'])
            ->when($q, fn($q2) => $q2->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%"))
            ->orderByDesc('logs_count');

        $users = $query->paginate(25)->withQueryString();
        $types = ReminderTemplate::allTypes();

        return view('reminders.users', compact('users', 'types'));
    }

    /**
     * GET /reminders/analytics — Analytics dashboard.
     */
    public function analytics(Request $request): View
    {
        $days = (int) $request->input('days', 30);
        $days = in_array($days, [7, 14, 30, 90]) ? $days : 30;

        $cacheKey = "reminders:analytics:{$days}";

        $stats = Cache::remember($cacheKey, 300, function () use ($days) {
            $since = Carbon::now('UTC')->subDays($days);

            $totalSent    = ReminderLog::where('sent_at', '>=', $since)->count();
            $totalOpened  = ReminderLog::where('sent_at', '>=', $since)->whereNotNull('opened_at')->count();
            $totalFailed  = ReminderLog::where('sent_at', '>=', $since)->where('status', ReminderLog::STATUS_FAILED)->count();
            $totalSnoozed = ReminderLog::where('sent_at', '>=', $since)->where('status', ReminderLog::STATUS_SNOOZED)->count();
            $openRate     = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
            $activeUsers  = ReminderLog::where('sent_at', '>=', $since)->distinct('user_id')->count('user_id');

            // Most effective type (highest open rate)
            $byType = ReminderLog::where('sent_at', '>=', $since)
                ->selectRaw('notification_type, COUNT(*) as cnt, SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opens')
                ->groupBy('notification_type')
                ->orderByRaw('opens / NULLIF(cnt, 0) DESC')
                ->first();

            // Daily chart data (last 14 days)
            $chartData = ReminderStatistic::where('date', '>=', Carbon::now('UTC')->subDays(14)->toDateString())
                ->orderBy('date')
                ->get(['date', 'sent_count', 'opened_count', 'open_rate']);

            return compact(
                'totalSent', 'totalOpened', 'totalFailed', 'totalSnoozed',
                'openRate', 'activeUsers', 'byType', 'chartData'
            );
        });

        // Active reminder user count
        $activeReminders = UserReminder::where('enabled', true)->count();

        return view('reminders.analytics', compact('stats', 'activeReminders', 'days'));
    }

    /**
     * POST /reminders/{reminder}/test — Send a test notification (admin only).
     */
    public function test(Request $request, ReminderTemplate $reminder): JsonResponse
    {
        $locale = $request->input('locale', app()->getLocale());

        // In a real implementation this would dispatch a FCM or APNS push.
        // For now, we return the preview payload so admin can verify.
        $payload = [
            'type'    => $reminder->type,
            'icon'    => $reminder->icon,
            'title'   => $reminder->getTranslation('title', $locale) ?? $reminder->key,
            'body'    => $reminder->getTranslation('body', $locale) ?? '',
            'version' => $reminder->version,
        ];

        // Log the test send
        ReminderLog::create([
            'user_id'           => $request->user()->id,
            'template_id'       => $reminder->id,
            'notification_type' => $reminder->type,
            'notification_id'   => 'admin_test_' . $reminder->id,
            'sent_at'           => Carbon::now('UTC'),
            'status'            => ReminderLog::STATUS_SENT,
            'device_platform'   => 'web',
            'payload_json'      => $payload,
        ]);

        return response()->json([
            'success' => true,
            'preview' => $payload,
            'message' => __('reminders.messages.test_sent'),
        ]);
    }
}
