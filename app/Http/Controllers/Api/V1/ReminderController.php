<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\Reminders\ReminderCreated;
use App\Events\Reminders\ReminderOpened;
use App\Events\Reminders\ReminderSnoozed;
use App\Events\Reminders\ReminderUpdated;
use App\Http\Controllers\Controller;
use App\Models\ReminderLog;
use App\Models\ReminderTemplate;
use App\Models\UserReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * ReminderController — Mobile API for Smart Reminder System.
 *
 * All endpoints require Sanctum authentication.
 */
class ReminderController extends Controller
{
    /**
     * GET /api/v1/reminders
     * Return the authenticated user's reminder settings + active templates.
     */
    public function index(Request $request): JsonResponse
    {
        $user   = $request->user();
        $locale = $user->preferred_locale ?? app()->getLocale();

        // User's current reminder rows keyed by type
        $userReminders = UserReminder::where('user_id', $user->id)
            ->get()
            ->keyBy('reminder_type');

        // All active templates (from cache)
        $templates = ReminderTemplate::active()
            ->with('translations')
            ->ordered()
            ->get();

        $result = $templates->map(function (ReminderTemplate $template) use ($userReminders, $locale) {
            $userRow = $userReminders->get($template->type);
            return [
                'type'           => $template->type,
                'key'            => $template->key,
                'icon'           => $template->icon,
                'title'          => $template->getTranslation('title', $locale) ?? $template->key,
                'body'           => $template->getTranslation('body', $locale) ?? '',
                'priority'       => $template->priority,
                'sort_order'     => $template->sort_order,
                'version'        => $template->version,
                'metadata'       => $template->metadata,
                // User preferences (null if not configured yet)
                'enabled'        => $userRow?->enabled ?? false,
                'scheduled_time' => $userRow?->scheduled_time
                    ?? config("reminders.default_times.{$template->type}"),
                'frequency'      => $userRow?->frequency ?? UserReminder::FREQUENCY_DAILY,
                'custom_days'    => $userRow?->custom_days ?? [],
                'timezone'       => $userRow?->timezone ?? 'Asia/Baghdad',
                'last_sent_at'   => $userRow?->last_sent_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success'         => true,
            'reminders'       => $result,
            'snooze_options'  => config('reminders.snooze_options', [10, 30, 60]),
        ]);
    }

    /**
     * POST /api/v1/reminders/save
     * Bulk-save all reminder settings for the authenticated user.
     *
     * Body: { "reminders": [ { type, enabled, scheduled_time, frequency, custom_days, timezone }, ... ] }
     */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'reminders'                    => 'required|array',
            'reminders.*.type'             => 'required|string|max:50',
            'reminders.*.enabled'          => 'required|boolean',
            'reminders.*.scheduled_time'   => 'nullable|date_format:H:i',
            'reminders.*.frequency'        => 'nullable|string|max:50',
            'reminders.*.custom_days'      => 'nullable|array',
            'reminders.*.custom_days.*'    => 'integer|min:1|max:7',
            'reminders.*.timezone'         => 'nullable|string|max:64',
        ]);

        $user    = $request->user();
        $updated = [];

        foreach ($request->reminders as $row) {
            $attributes = [
                'user_id'        => $user->id,
                'reminder_type'  => $row['type'],
            ];
            $values = [
                'enabled'        => $row['enabled'],
                'scheduled_time' => $row['scheduled_time'] ?? null,
                'frequency'      => $row['frequency'] ?? UserReminder::FREQUENCY_DAILY,
                'custom_days'    => $row['custom_days'] ?? null,
                'timezone'       => $row['timezone'] ?? 'Asia/Baghdad',
            ];

            $isNew  = !UserReminder::where($attributes)->exists();
            $record = UserReminder::withTrashed()->updateOrCreate($attributes, $values);

            // Restore if soft-deleted
            if ($record->trashed()) {
                $record->restore();
                $record->fill($values)->save();
            }

            if ($isNew) {
                event(new ReminderCreated($record));
            } else {
                event(new ReminderUpdated($record));
            }

            $updated[] = $record->reminder_type;
        }

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'message' => __('reminders.messages.saved'),
        ]);
    }

    /**
     * POST /api/v1/reminders/enable
     * Enable a single reminder type.
     *
     * Body: { "type": "MORNING", "scheduled_time": "08:00", "timezone": "Asia/Baghdad" }
     */
    public function enable(Request $request): JsonResponse
    {
        $request->validate([
            'type'           => 'required|string|max:50',
            'scheduled_time' => 'nullable|date_format:H:i',
            'timezone'       => 'nullable|string|max:64',
        ]);

        $user   = $request->user();
        $record = UserReminder::withTrashed()->updateOrCreate(
            ['user_id' => $user->id, 'reminder_type' => $request->type],
            [
                'enabled'        => true,
                'scheduled_time' => $request->scheduled_time,
                'timezone'       => $request->timezone ?? 'Asia/Baghdad',
            ]
        );

        if ($record->trashed()) {
            $record->restore();
        }

        event(new ReminderUpdated($record));

        return response()->json(['success' => true, 'reminder' => $record]);
    }

    /**
     * POST /api/v1/reminders/disable
     * Disable a single reminder type.
     *
     * Body: { "type": "MORNING" }
     */
    public function disable(Request $request): JsonResponse
    {
        $request->validate(['type' => 'required|string|max:50']);

        $user   = $request->user();
        $record = UserReminder::where('user_id', $user->id)
            ->where('reminder_type', $request->type)
            ->first();

        if ($record) {
            $record->update(['enabled' => false]);
            event(new ReminderUpdated($record));
        }

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/v1/reminders/sync
     * Full schedule sync — returns all active reminder payloads with smart messages.
     * Flutter calls this on app start, reboot, or timezone change.
     *
     * Body (optional): { "today_progress": 50, "daily_goal": 100, "streak": 7, "near_achievement": true }
     */
    public function sync(Request $request): JsonResponse
    {
        $user   = $request->user();
        $locale = $user->preferred_locale ?? app()->getLocale();

        $context = [
            'today_progress'   => (int) $request->input('today_progress', 0),
            'daily_goal'       => (int) $request->input('daily_goal', 100),
            'streak'           => (int) $request->input('streak', 0),
            'near_achievement' => (bool) $request->input('near_achievement', false),
        ];

        $reminders = UserReminder::where('user_id', $user->id)
            ->where('enabled', true)
            ->get();

        $templates = ReminderTemplate::active()
            ->with('translations')
            ->get()
            ->keyBy('type');

        $schedule = $reminders->map(function (UserReminder $ur) use ($templates, $locale, $context) {
            $template = $templates->get($ur->reminder_type);
            if (!$template) {
                return null;
            }

            // Build smart body based on context
            $body = $this->buildSmartBody($template, $ur->reminder_type, $locale, $context);

            return [
                'notification_id' => $this->buildNotificationId($ur->user_id, $ur->reminder_type),
                'type'            => $ur->reminder_type,
                'icon'            => $template->icon,
                'title'           => $template->getTranslation('title', $locale) ?? $template->key,
                'body'            => $body,
                'scheduled_time'  => $ur->scheduled_time,
                'frequency'       => $ur->frequency,
                'custom_days'     => $ur->custom_days,
                'timezone'        => $ur->timezone,
                'template_version'=> $template->version,
                'metadata'        => $template->metadata,
            ];
        })->filter()->values();

        return response()->json([
            'success'  => true,
            'schedule' => $schedule,
            'synced_at'=> Carbon::now('UTC')->toIso8601String(),
        ]);
    }

    /**
     * POST /api/v1/reminders/opened
     * Mark a reminder as opened (for analytics).
     *
     * Body: { "notification_id": "...", "notification_type": "MORNING" }
     */
    public function opened(Request $request): JsonResponse
    {
        $request->validate([
            'notification_id'   => 'required|string|max:100',
            'notification_type' => 'required|string|max:50',
        ]);

        $user = $request->user();

        $log = ReminderLog::where('user_id', $user->id)
            ->where('notification_id', $request->notification_id)
            ->latest()
            ->first();

        if ($log && !$log->opened_at) {
            $log->update([
                'opened_at' => Carbon::now('UTC'),
                'status'    => ReminderLog::STATUS_OPENED,
            ]);
            event(new ReminderOpened($log));
        } elseif (!$log) {
            // Create log if not recorded server-side (local-only notification)
            $log = ReminderLog::create([
                'user_id'           => $user->id,
                'notification_type' => $request->notification_type,
                'notification_id'   => $request->notification_id,
                'sent_at'           => Carbon::now('UTC'),
                'opened_at'         => Carbon::now('UTC'),
                'status'            => ReminderLog::STATUS_OPENED,
                'device_platform'   => $request->header('X-Platform', ReminderLog::PLATFORM_ANDROID),
                'timezone'          => $request->input('timezone'),
            ]);
        }

        return response()->json(['success' => true]);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────────

    /**
     * Generate a deterministic notification ID: userId_type_HHMM
     */
    private function buildNotificationId(int $userId, string $type): string
    {
        return "u{$userId}_{$type}";
    }

    /**
     * Build a context-aware notification body.
     */
    private function buildSmartBody(
        ReminderTemplate $template,
        string $type,
        string $locale,
        array $context
    ): string {
        $base = $template->getTranslation('body', $locale) ?? '';

        if ($type === ReminderTemplate::TYPE_DAILY_GOAL) {
            $remaining = max(0, $context['daily_goal'] - $context['today_progress']);
            if ($remaining > 0) {
                return sprintf(
                    __('reminders.smart.goal_remaining', ['count' => $remaining]),
                );
            }
        }

        if ($type === ReminderTemplate::TYPE_STREAK && $context['streak'] > 0) {
            return __('reminders.smart.streak_warning', ['days' => $context['streak']]);
        }

        if ($type === ReminderTemplate::TYPE_ACHIEVEMENT && $context['near_achievement']) {
            return __('reminders.smart.achievement_close');
        }

        return $base;
    }
}
