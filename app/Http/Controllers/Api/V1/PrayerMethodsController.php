<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PrayerMethod;
use App\Models\PrayerSetting;
use App\Models\UserPrayerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PrayerMethodsController extends Controller
{
    public function index(Request $request)
    {
        $methods = PrayerMethod::enabled()->orderBy('sort_order')->get();
        $settings = PrayerSetting::firstOrCreate([]);
        $defaultMethodKey = $settings->calculation_method ?? 'muslim_world_league';

        // Check if user has selected preference
        $userActiveMethodKey = null;
        $userLastUpdate = '_no-user';

        if (auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            $userSetting = UserPrayerSetting::where('user_id', $user->id)
                ->with('prayerMethod')
                ->first();

            if ($userSetting && $userSetting->prayerMethod) {
                $userActiveMethodKey = $userSetting->prayerMethod->key;
                $userLastUpdate = '_' . $userSetting->updated_at->toIso8601String();
            } else {
                $userLastUpdate = '_no-setting';
            }
        }

        // Generate SHA256 version hash
        $methodsLastUpdate = PrayerMethod::latest('updated_at')->first()?->updated_at?->toIso8601String() ?? 'no-methods';
        $settingsLastUpdate = $settings->updated_at ? $settings->updated_at->toIso8601String() : 'no-settings';
        $versionHash = hash('sha256', $methodsLastUpdate . '_' . $settingsLastUpdate . $userLastUpdate);

        $etag = '"' . $versionHash . '"';
        if ($request->header('If-None-Match') === $etag || $request->input('version_hash') === $versionHash) {
            return response()->json(null, 304)->header('ETag', $etag);
        }

        $methodsData = $methods->map(function ($method) use ($defaultMethodKey, $userActiveMethodKey) {
            return [
                'id' => $method->id,
                'key' => $method->key,
                'translation_key_name' => $method->translation_key_name,
                'translation_key_desc' => $method->translation_key_desc,
                'config' => $method->config,
                'is_default' => $method->key === $defaultMethodKey,
                'is_user_active' => $method->key === ($userActiveMethodKey ?? $defaultMethodKey),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'methods' => $methodsData,
                'default_method_key' => $defaultMethodKey,
                'user_active_method_key' => $userActiveMethodKey,
                'active_method_key' => $userActiveMethodKey ?? $defaultMethodKey,
                'version_hash' => $versionHash,
            ]
        ])->header('ETag', $etag);
    }

    public function updateUserMethod(Request $request)
    {
        $validated = $request->validate([
            'prayer_method_key' => 'required|string|exists:prayer_methods,key',
        ]);

        $user = auth()->user();
        $method = PrayerMethod::where('key', $validated['prayer_method_key'])->firstOrFail();

        if (!$method->is_enabled) {
            return response()->json([
                'status' => 'error',
                'message' => 'The selected calculation method is currently disabled.',
            ], 422);
        }

        $userSetting = UserPrayerSetting::updateOrCreate(
            ['user_id' => $user->id],
            ['prayer_method_id' => $method->id]
        );
        $userSetting->touch();

        return response()->json([
            'status' => 'success',
            'message' => 'User calculation method preference synchronized successfully.',
            'data' => [
                'active_method_key' => $method->key
            ]
        ]);
    }
}
