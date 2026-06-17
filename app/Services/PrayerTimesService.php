<?php

namespace App\Services;

use App\Models\City;
use App\Models\PrayerMethod;
use App\Models\WidgetSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PrayerTimesService
{
    protected PrayerCalculationService $calculationService;

    public function __construct(PrayerCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Resolve coordinate strategy and calculate local prayer times.
     */
    public function getTimesForRequest(Request $request): array
    {
        $resolved = $this->resolveCoordinatesAndSettings($request);
        $city = $resolved['city'];
        $latitude = $resolved['latitude'];
        $longitude = $resolved['longitude'];
        $timezoneStr = $resolved['timezone'];
        $methodKey = $resolved['method_key'];

        // Determine dynamic UTC offset (supporting DST)
        $tz = new \DateTimeZone($timezoneStr);
        $dateTime = new \DateTime('now', $tz);
        $utcOffset = $tz->getOffset($dateTime) / 3600.0;

        // Fetch calculation method model configurations
        $method = PrayerMethod::where('key', $methodKey)->first();
        $fajrAngle = 18.0;
        $ishaAngle = 17.0;
        $ishaDelay = null;

        if ($method) {
            $config = $method->config;
            $fajrAngle = $config['fajr_angle'] ?? 18.0;
            $ishaAngle = $config['isha_angle'] ?? 17.0;
            
            $rules = $config['rules'] ?? [];
            if (isset($rules['isha_delay_minutes'])) {
                // If it is Ramadan, apply Ramadan delay
                $isRamadan = $this->isCurrentMonthRamadan($request);
                $ishaDelay = $isRamadan 
                    ? ($rules['isha_delay_ramadan_minutes'] ?? 120) 
                    : ($rules['isha_delay_minutes'] ?? 90);
            }
        }

        $now = Carbon::now($timezoneStr);
        $year = $now->year;
        $month = $now->month;
        $day = $now->day;

        // Run calculation engine
        $rawTimes = $this->calculationService->calculateTimes(
            $latitude,
            $longitude,
            $utcOffset,
            $year,
            $month,
            $day,
            $fajrAngle,
            $ishaAngle,
            $ishaDelay
        );

        // Format decimal times to HH:MM format strings
        $formattedTimes = [];
        foreach ($rawTimes as $key => $decimalHours) {
            $formattedTimes[$key] = $this->formatDecimalHours($decimalHours);
        }

        return [
            'city_name' => $city ? $city->name : 'Custom Location',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timezone' => $timezoneStr,
            'utc_offset' => $utcOffset,
            'calculation_method' => $methodKey,
            'times' => $formattedTimes,
            'raw_times' => $rawTimes, // decimal hours
        ];
    }

    /**
     * Resolve latitude, longitude, and timezone based on Priority Strategy:
     * 1. User Selected City (authenticated user preference) or request query param (city_id / lat, lng)
     * 2. GPS Coordinates (if request contains latitude and longitude)
     * 3. Widget Default City (from settings)
     * 4. System Default City (Erbil)
     */
    public function resolveCoordinatesAndSettings(Request $request): array
    {
        $city = null;
        $latitude = null;
        $longitude = null;
        $timezone = 'Asia/Baghdad';
        
        $user = auth('sanctum')->user();
        
        // 1. Check for request query city_id
        if ($request->filled('city_id')) {
            $city = City::find($request->input('city_id'));
        }
        
        // 2. Check authenticated user's province locked preferences
        if (!$city && $user && $user->province) {
            // Find English name of the user province to match with City model
            $enLang = \App\Models\Language::where('code', 'en')->first();
            $translation = $user->province->translations()
                ->where('language_id', $enLang->id ?? 0)
                ->where('field', 'name')
                ->first();
            if ($translation) {
                $city = City::where('name', 'like', $translation->value)->first();
            }
        }

        // 3. Fallback to GPS coordinates passed in query
        if (!$city && $request->filled('latitude') && $request->filled('longitude')) {
            $latitude = (float) $request->input('latitude');
            $longitude = (float) $request->input('longitude');
            $timezone = $request->input('timezone', 'Asia/Baghdad');
        }

        // 4. Fallback to Widget Default City
        if (!$city && !$latitude) {
            $widgetSettings = WidgetSetting::first();
            if ($widgetSettings && $widgetSettings->widget_default_city_id) {
                $city = City::find($widgetSettings->widget_default_city_id);
            }
        }

        // 5. Fallback to System Default (Erbil)
        if (!$city && !$latitude) {
            $city = City::firstOrCreate(
                ['name' => 'Erbil'],
                [
                    'lat' => 36.1912,
                    'lng' => 44.0091,
                    'timezone' => 'Asia/Baghdad',
                ]
            );
        }

        if ($city) {
            $latitude = $city->lat;
            $longitude = $city->lng;
            $timezone = $city->timezone;
        }

        // Resolve active calculation method
        $methodKey = 'kurdistan'; // fallback default
        
        // First check request param
        if ($request->filled('calculation_method')) {
            $methodKey = $request->input('calculation_method');
        }
        // Then user settings
        elseif ($user) {
            $userSetting = \App\Models\UserPrayerSetting::where('user_id', $user->id)
                ->with('prayerMethod')
                ->first();
            if ($userSetting && $userSetting->prayerMethod) {
                $methodKey = $userSetting->prayerMethod->key;
            }
        }
        // Then fallback settings table
        else {
            $settings = \App\Models\PrayerSetting::first();
            if ($settings && $settings->calculation_method) {
                $methodKey = $settings->calculation_method;
            }
        }

        return [
            'city' => $city,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timezone' => $timezone,
            'method_key' => $methodKey,
        ];
    }

    /**
     * Check if the current Hijri month is Ramadan.
     */
    protected function isCurrentMonthRamadan(Request $request): bool
    {
        $widgetService = app(PrayerWidgetService::class);
        $jd = $this->calculationService->getJulianDate(date('Y'), date('m'), date('d'), 12.0);
        
        // Grab widget settings to resolve Hijri source
        $widgetSettings = WidgetSetting::first();
        $source = $widgetSettings->hijri_source ?? 'tabular';
        
        $hijri = $widgetService->calculateHijriDate($jd, $source);
        return $hijri['month'] === 9;
    }

    /**
     * Convert decimal hours to HH:MM format.
     */
    protected function formatDecimalHours(float $decimalHours): string
    {
        $decimalHours = fmod($decimalHours, 24.0);
        if ($decimalHours < 0) {
            $decimalHours += 24.0;
        }
        $h = floor($decimalHours);
        $m = round(($decimalHours - $h) * 60);
        if ($m >= 60) {
            $m -= 60;
            $h += 1;
        }
        $h = fmod($h, 24.0);
        return sprintf('%02d:%02d', $h, $m);
    }
}
