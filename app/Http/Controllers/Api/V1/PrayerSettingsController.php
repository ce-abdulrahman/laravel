<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PrayerSetting;
use App\Models\City;
use Illuminate\Http\Request;

class PrayerSettingsController extends Controller
{
    public function index(Request $request)
    {
        $settings = PrayerSetting::firstOrCreate([]);
        $cities = City::all();
        
        $calculationMethod = $settings->calculation_method;
        $userLastUpdate = '';

        if (auth('sanctum')->check()) {
            $userSetting = \App\Models\UserPrayerSetting::where('user_id', auth('sanctum')->id())
                ->with('prayerMethod')
                ->first();
            if ($userSetting && $userSetting->prayerMethod) {
                $calculationMethod = $userSetting->prayerMethod->key;
                $userLastUpdate = '_' . $userSetting->updated_at->toIso8601String();
            }
        }

        $latestCity = City::latest('updated_at')->first();
        $latestCityTimestamp = $latestCity ? $latestCity->updated_at->toIso8601String() : 'no-cities';
        
        $versionHash = md5($settings->updated_at->toIso8601String() . '_' . $latestCityTimestamp . $userLastUpdate);
        
        $etag = '"' . $versionHash . '"';
        if ($request->header('If-None-Match') === $etag || $request->input('version_hash') === $versionHash) {
            return response()->json(null, 304)->header('ETag', $etag);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'calculation_method' => $calculationMethod,
                'global_notifications_enabled' => $settings->global_notifications_enabled,
                'adhan_settings' => $settings->adhan_settings ?? [],
                'cities' => $cities->map(function ($city) {
                    return [
                        'id' => $city->id,
                        'name' => $city->name,
                        'lat' => $city->lat,
                        'lng' => $city->lng,
                        'timezone' => $city->timezone,
                    ];
                }),
                'version_hash' => $versionHash,
            ]
        ])->header('ETag', $etag);
    }
}
