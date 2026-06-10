<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.invalid_credentials')
            ], 401);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->status) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.account_deactivated')
            ], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => __('api.login_successful'),
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
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
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'status' => true,
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => __('api.registration_successful'),
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('api.logged_out')
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $request->user()
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => __('api.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        $request->user()->update($request->only(['name', 'email']));

        return response()->json([
            'status' => 'success',
            'message' => __('api.profile_updated'),
            'data' => [
                'user' => $request->user()->fresh()
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
}
