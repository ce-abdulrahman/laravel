<?php

namespace App\Services;

use App\Models\WidgetSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PrayerWidgetService
{
    protected PrayerTimesService $timesService;
    protected PrayerCalculationService $calculationService;

    public function __construct(
        PrayerTimesService $timesService,
        PrayerCalculationService $calculationService
    ) {
        $this->timesService = $timesService;
        $this->calculationService = $calculationService;
    }

    /**
     * Build the API response payload for the prayer widget.
     */
    public function getWidgetPayload(Request $request): array
    {
        // Calculate daily times
        $timesData = $this->timesService->getTimesForRequest($request);
        $timezone = $timesData['timezone'];
        $times = $timesData['times']; // array of string HH:MM times
        
        $now = Carbon::now($timezone);
        $todayDateStr = $now->toDateString();
        
        // Determine current, next prayer and live countdown
        $nextPrayerInfo = $this->calculateNextPrayer($times, $timezone);
        
        // Retrieve widget configuration to resolve Hijri source
        $widgetSettings = WidgetSetting::firstOrCreate([]);
        $source = $widgetSettings->hijri_source ?? 'tabular';
        
        // Calculate Hijri date
        $jd = $this->calculationService->getJulianDate($now->year, $now->month, $now->day, 12.0);
        $hijri = $this->calculateHijriDate($jd, $source);
        $hijriMonthName = __("hijri.month.{$hijri['month']}");
        $hijriDateStr = "{$hijri['day']} {$hijriMonthName} {$hijri['year']}";

        // Localized Gregorian Date (e.g. 14 June 2026)
        $gregorianDateStr = $now->translatedFormat('j F Y');

        // Compile payload
        return [
            'next_prayer' => $nextPrayerInfo['next_prayer'],
            'next_prayer_time' => $nextPrayerInfo['next_prayer_time'],
            'next_prayer_remaining' => $nextPrayerInfo['remaining'], // HH:MM:SS format
            'current_city' => $timesData['city_name'],
            'hijri_date' => $hijriDateStr,
            'gregorian_date' => $gregorianDateStr,
            'active_prayer_method' => $timesData['calculation_method'],
            'timezone' => $timezone,
            'utc_offset' => $timesData['utc_offset'],
            'dst_active' => (bool) $now->format('I'),
            'prayer_times' => $times, // list of today's prayer times
            'widget_settings' => [
                'enabled' => $widgetSettings->widget_enabled,
                'visibility' => $widgetSettings->widget_visibility,
                'refresh_interval' => $widgetSettings->widget_refresh_interval,
                'display_order' => $widgetSettings->widget_display_order,
            ]
        ];
    }

    /**
     * Compute the SHA256 version hash for cache validation.
     */
    public function getVersionHash(Request $request, array $payload): string
    {
        $user = auth('sanctum')->user();
        $userLastUpdate = '';
        if ($user) {
            $userSetting = \App\Models\UserPrayerSetting::where('user_id', $user->id)->first();
            if ($userSetting) {
                $userLastUpdate = '_' . $userSetting->updated_at->toIso8601String();
            }
        }

        $widgetSettings = WidgetSetting::firstOrCreate([]);
        $widgetUpdatedAt = $widgetSettings->updated_at ? $widgetSettings->updated_at->toIso8601String() : 'no-widget-settings';
        $locale = app()->getLocale();

        // SHA256 input elements
        $inputs = [
            date('Y-m-d'),
            $payload['current_city'],
            $payload['active_prayer_method'],
            $locale,
            $widgetUpdatedAt,
            $userLastUpdate
        ];

        return hash('sha256', implode('_', $inputs));
    }

    /**
     * Determine next prayer name and time, and the remaining duration as a string.
     */
    public function calculateNextPrayer(array $times, string $timezone): array
    {
        $now = Carbon::now($timezone);
        $prayers = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];
        
        $nextPrayerName = null;
        $nextPrayerTimeStr = null;
        $nextPrayerDateTime = null;

        // Loop to find next prayer today
        foreach ($prayers as $pName) {
            $timeParts = explode(':', $times[$pName]);
            $pDateTime = Carbon::create($now->year, $now->month, $now->day, $timeParts[0], $timeParts[1], 0, $timezone);
            
            if ($pDateTime->isAfter($now)) {
                $nextPrayerName = $pName;
                $nextPrayerTimeStr = $times[$pName];
                $nextPrayerDateTime = $pDateTime;
                break;
            }
        }

        // If all prayers today passed, next prayer is Fajr tomorrow
        if (!$nextPrayerDateTime) {
            $tomorrow = Carbon::now($timezone)->addDay();
            
            // Re-calculate Fajr for tomorrow
            $timesDataTomorrow = $this->timesService->getTimesForRequest(new Request([
                'latitude' => $this->timesService->resolveCoordinatesAndSettings(request())['latitude'],
                'longitude' => $this->timesService->resolveCoordinatesAndSettings(request())['longitude'],
                'calculation_method' => $this->timesService->resolveCoordinatesAndSettings(request())['method_key'],
            ]));
            
            $fajrTimeParts = explode(':', $timesDataTomorrow['times']['fajr']);
            $nextPrayerName = 'fajr';
            $nextPrayerTimeStr = $timesDataTomorrow['times']['fajr'];
            $nextPrayerDateTime = Carbon::create($tomorrow->year, $tomorrow->month, $tomorrow->day, $fajrTimeParts[0], $fajrTimeParts[1], 0, $timezone);
        }

        // Calculate countdown duration HH:MM:SS
        $diffSeconds = $now->diffInSeconds($nextPrayerDateTime, false);
        $h = floor($diffSeconds / 3600);
        $m = floor(($diffSeconds % 3600) / 60);
        $s = $diffSeconds % 60;
        $remainingStr = sprintf('%02d:%02d:%02d', $h, $m, $s);

        return [
            'next_prayer' => $nextPrayerName,
            'next_prayer_time' => $nextPrayerTimeStr,
            'remaining' => $remainingStr,
        ];
    }

    /**
     * Compute Hijri date from Julian Date.
     */
    public function calculateHijriDate(float $jd, string $source = 'tabular'): array
    {
        $epoch = 1948440; // JD at 1 Muharram 1 AH
        $days = floor($jd) - $epoch;

        // Adjust offset based on configured Hijri source
        if ($source === 'umm_al_qura') {
            $days -= 1; // typical offset shift for Makkah crescent visibility rules
        }

        $hYear = floor(($days * 30 + 10631) / 10631);
        $elapsedDays = floor(($hYear - 1) * 354.36667) + floor((($hYear * 11) + 3) / 30);
        $daysInYear = $days - $elapsedDays;

        $hMonth = floor(($daysInYear * 30 + 59) / 885) + 1;
        if ($hMonth > 12) {
            $hMonth = 12;
        }

        $elapsedDaysInMonth = floor(($hMonth - 1) * 29.5) + floor($hMonth / 2);
        $hDay = $daysInYear - $elapsedDaysInMonth + 1;

        if ($hDay < 1) {
            $hMonth--;
            if ($hMonth < 1) {
                $hMonth = 12;
                $hYear--;
            }
            $elapsedDaysInMonth = floor(($hMonth - 1) * 29.5) + floor($hMonth / 2);
            $hDay = $daysInYear - $elapsedDaysInMonth + 1;
        }

        return [
            'day' => (int) $hDay,
            'month' => (int) $hMonth,
            'year' => (int) $hYear,
        ];
    }
}
