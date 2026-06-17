<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Language;
use App\Models\ProfileTranslation;
use App\Services\GuestMigrationService;
use App\Services\ProfileStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required',
            'device_identifier' => 'sometimes|string',
            'device_name' => 'sometimes|string',
            'platform' => 'sometimes|string',
            'last_platform_version' => 'sometimes|string',
            'push_token' => 'sometimes|string',
            'guest_data' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        $login = $request->input('login');

        // Include soft-deleted accounts in authentication to allow recovery within 30 days
        $user = User::withTrashed()
            ->where(function ($query) use ($login) {
                $query->where('email', $login)
                      ->orWhere('username', $login);
            })
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.invalid_credentials')
            ], 401);
        }

        // Account status check (active or deactivated)
        if (!$user->status) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.account_deactivated')
            ], 403);
        }

        // Restore account if it is soft deleted and within the 30-day recovery window
        if ($user->trashed()) {
            if ($user->deleted_at->addDays(30)->isFuture()) {
                $user->restore();
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => __('api.account_deleted_permanently')
                ], 403);
            }
        }

        // Record device registration and update last login log
        $this->recordDeviceAndLogin($user, $request);

        // Migrate guest progress data if provided
        if ($request->has('guest_data')) {
            app(GuestMigrationService::class)->migrate($user, $request->input('guest_data'));
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        // Warm memorization dashboard and statistics cache
        try {
            app(\App\Http\Controllers\Api\V1\UserAyahProgressController::class)->warmCache($user->id);
        } catch (\Exception $e) {
            \Log::error("Failed to warm cache for user {$user->id} on login: " . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => __('api.login_successful'),
            'data' => [
                'user' => $user->load(['profile.translations', 'country', 'province']),
                'token' => $token,
                'stats' => app(ProfileStatisticsService::class)->getStats($user)
            ]
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'gender' => 'sometimes|nullable|string|in:male,female,other',
            'birth_year' => 'sometimes|nullable|integer|min:1900|max:' . now()->year,
            'country_id' => 'sometimes|nullable|exists:countries,id',
            'province_id' => 'sometimes|nullable|exists:provinces,id',
            'avatar' => 'sometimes|nullable|file|image|max:5120',
            'device_identifier' => 'sometimes|string',
            'device_name' => 'sometimes|string',
            'platform' => 'sometimes|string',
            'last_platform_version' => 'sometimes|string',
            'push_token' => 'sometimes|string',
            'guest_data' => 'sometimes|array',
            'bio' => 'sometimes|nullable|string',
            'nickname' => 'sometimes|nullable|string|max:255',
            'public_title' => 'sometimes|nullable|string|max:255',
            'profile_quote' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'birth_year' => $request->birth_year,
            'country_id' => $request->country_id,
            'province_id' => $request->province_id,
            'role' => 'user',
            'status' => true,
        ]);

        // Create Profile
        $profile = $user->profile()->create([
            'bio' => $request->input('bio'),
            'nickname' => $request->input('nickname'),
            'public_title' => $request->input('public_title'),
            'profile_quote' => $request->input('profile_quote'),
            'preferences' => [],
            'settings' => [],
        ]);

        // Process profile translations if any are sent
        if ($request->has('translations')) {
            $this->updateTranslations($profile, $request->input('translations'));
        }

        // Handle Avatar WebP resize & compression
        if ($request->hasFile('avatar')) {
            $this->saveAvatarWebp($user, $request->file('avatar'));
        }

        // Record device fingerprinting and update login history
        $this->recordDeviceAndLogin($user, $request);

        // Migrate guest progress data if provided
        if ($request->has('guest_data')) {
            app(GuestMigrationService::class)->migrate($user, $request->input('guest_data'));
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => __('api.registration_successful'),
            'data' => [
                'user' => $user->load(['profile.translations', 'country', 'province']),
                'token' => $token,
                'stats' => app(ProfileStatisticsService::class)->getStats($user)
            ]
        ], 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $deviceIdentifier = $request->input('device_identifier');
        
        // Log logout time
        if ($deviceIdentifier) {
            $user->loginLogs()
                ->where('device_identifier', $deviceIdentifier)
                ->whereNull('logout_at')
                ->update(['logout_at' => now()]);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('api.logged_out')
        ]);
    }

    public function logoutAllDevices(Request $request)
    {
        $user = $request->user();
        
        // Update all open login logs
        $user->loginLogs()
            ->whereNull('logout_at')
            ->update(['logout_at' => now()]);

        // Revoke all tokens
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('api.logged_out_all_devices')
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user->load(['profile.translations', 'country', 'province']),
                'stats' => app(ProfileStatisticsService::class)->getStats($user)
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'gender' => 'sometimes|nullable|string|in:male,female,other',
            'birth_year' => 'sometimes|nullable|integer|min:1900|max:' . now()->year,
            'country_id' => 'sometimes|nullable|exists:countries,id',
            'province_id' => 'sometimes|nullable|exists:provinces,id',
            'avatar' => 'sometimes|nullable|file|image|max:5120',
            'bio' => 'sometimes|nullable|string',
            'nickname' => 'sometimes|nullable|string|max:255',
            'public_title' => 'sometimes|nullable|string|max:255',
            'profile_quote' => 'sometimes|nullable|string',
            'preferences' => 'sometimes|array',
            'settings' => 'sometimes|array',
            'translations' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        // Update core user data
        $user->update($request->only([
            'name', 'username', 'email', 'gender', 'birth_year', 'country_id', 'province_id'
        ]));

        // Retrieve or create UserProfile
        $profile = $user->profile ?: $user->profile()->create();
        $profile->update($request->only([
            'bio', 'nickname', 'public_title', 'profile_quote', 'preferences', 'settings'
        ]));

        // Process profile translations if any are sent
        if ($request->has('translations')) {
            $this->updateTranslations($profile, $request->input('translations'));
        }

        // Handle avatar image update
        if ($request->hasFile('avatar')) {
            $this->saveAvatarWebp($user, $request->file('avatar'));
        }

        // Invalidate cached statistics
        app(ProfileStatisticsService::class)->invalidateCache($user->id);

        return response()->json([
            'status' => 'success',
            'message' => __('api.profile_updated'),
            'data' => [
                'user' => $user->fresh(['profile.translations', 'country', 'province']),
                'stats' => app(ProfileStatisticsService::class)->getStats($user)
            ]
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.current_password_incorrect')
            ], 400);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Revoke all tokens except current
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('api.password_changed')
        ]);
    }

    public function guestConvert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        app(GuestMigrationService::class)->migrate($user, $request->input('guest_data'));

        return response()->json([
            'status' => 'success',
            'message' => __('api.guest_migration_successful'),
            'data' => [
                'user' => $user->load(['profile.translations', 'country', 'province']),
                'stats' => app(ProfileStatisticsService::class)->getStats($user)
            ]
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        // Update active logs
        $user->loginLogs()->whereNull('logout_at')->update(['logout_at' => now()]);

        // Revoke all tokens
        $user->tokens()->delete();

        // Soft delete user
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('api.account_deleted_successfully')
        ]);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────────

    private function recordDeviceAndLogin(User $user, Request $request)
    {
        $deviceIdentifier = $request->input('device_identifier');
        if ($deviceIdentifier) {
            \App\Models\UserDevice::updateOrCreate([
                'user_id' => $user->id,
                'device_identifier' => $deviceIdentifier,
            ], [
                'device_name' => $request->input('device_name', 'Unknown Device'),
                'platform' => $request->input('platform', 'Unknown'),
                'last_platform_version' => $request->input('last_platform_version'),
                'push_token' => $request->input('push_token'),
                'last_ip' => $request->ip(),
                'last_activity_at' => now(),
            ]);

            \App\Models\UserLoginLog::create([
                'user_id' => $user->id,
                'device_identifier' => $deviceIdentifier,
                'ip_address' => $request->ip(),
                'device' => $request->input('device_name', 'Unknown Device'),
                'platform' => $request->input('platform', 'Unknown'),
                'login_at' => now(),
            ]);
        }

        $user->update([
            'last_login_at' => now(),
        ]);
    }

    private function updateTranslations(UserProfile $profile, array $translations)
    {
        foreach ($translations as $langCode => $fields) {
            $language = Language::where('code', $langCode)->first();
            if ($language) {
                foreach ($fields as $field => $value) {
                    if (in_array($field, ['bio', 'nickname', 'public_title', 'profile_quote'])) {
                        ProfileTranslation::updateOrCreate([
                            'user_profile_id' => $profile->id,
                            'language_id' => $language->id,
                            'field' => $field,
                        ], [
                            'value' => $value,
                        ]);
                    }
                }
            }
        }
    }

    private function saveAvatarWebp(User $user, $file)
    {
        $path = "avatars/{$user->id}";
        $filename = "avatar.webp";
        $fullPath = public_path("{$path}/{$filename}");

        if (!file_exists(public_path($path))) {
            mkdir(public_path($path), 0755, true);
        }

        $success = false;
        if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $image = @imagecreatefromstring(file_get_contents($file->getPathname()));
            if ($image) {
                $width = imagesx($image);
                $height = imagesy($image);
                $maxSize = 500;
                if ($width > $maxSize || $height > $maxSize) {
                    if ($width > $height) {
                        $newWidth = $maxSize;
                        $newHeight = (int) ($height * ($maxSize / $width));
                    } else {
                        $newHeight = $maxSize;
                        $newWidth = (int) ($width * ($maxSize / $height));
                    }
                    $resized = imagecreatetruecolor($newWidth, $newHeight);
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagedestroy($image);
                    $image = $resized;
                }
                if (@imagewebp($image, $fullPath, 80)) {
                    $success = true;
                }
                imagedestroy($image);
            }
        }

        if ($success) {
            $user->update(['avatar' => "{$path}/{$filename}"]);
        } else {
            $originalName = $file->getClientOriginalName();
            $file->move(public_path($path), $originalName);
            $user->update(['avatar' => "{$path}/{$originalName}"]);
        }
    }

    public function countries()
    {
        $countries = \App\Models\Country::with('translations')->get();
        return response()->json([
            'status' => 'success',
            'data' => $countries
        ]);
    }

    public function provinces(int $countryId)
    {
        $provinces = \App\Models\Province::where('country_id', $countryId)->with('translations')->get();
        return response()->json([
            'status' => 'success',
            'data' => $provinces
        ]);
    }
}

