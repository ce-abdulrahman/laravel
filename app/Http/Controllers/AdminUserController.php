<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Country;
use App\Models\Province;
use App\Services\ProfileStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    private readonly ProfileStatisticsService $statsService;

    public function __construct(ProfileStatisticsService $statsService)
    {
        $this->statsService = $statsService;
    }

    public function dashboard()
    {
        $totalUsers = User::withTrashed()->count();
        $activeUsers = User::where('status', true)->count();
        $suspendedUsers = User::where('status', false)->count();
        $deletedUsers = User::onlyTrashed()->count();
        
        $genderDistribution = User::selectRaw('gender, count(*) as count')
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->get();
            
        $recentUsers = User::latest()->limit(5)->get();
        
        $topCountries = User::join('countries', 'users.country_id', '=', 'countries.id')
            ->join('country_translations', function($join) {
                $join->on('countries.id', '=', 'country_translations.country_id')
                     ->where('country_translations.language_id', 1); // English standard fallback
            })
            ->selectRaw('country_translations.value as country_name, count(users.id) as user_count')
            ->groupBy('country_translations.value')
            ->orderByDesc('user_count')
            ->limit(5)
            ->get();

        return view('admin.users.dashboard', compact(
            'totalUsers', 'activeUsers', 'suspendedUsers', 'deletedUsers', 
            'genderDistribution', 'recentUsers', 'topCountries'
        ));
    }

    public function index(Request $request)
    {
        $search = $request->get('q');
        $status = $request->get('status');
        $gender = $request->get('gender');
        $countryId = $request->get('country_id');
        $role = $request->get('role');

        $query = User::withTrashed()->with(['country', 'province']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            if ($status === 'deleted') {
                $query->onlyTrashed();
            } elseif ($status === 'suspended') {
                $query->where('status', false)->whereNull('deleted_at');
            } elseif ($status === 'active') {
                $query->where('status', true)->whereNull('deleted_at');
            }
        }

        if ($gender) {
            $query->where('gender', $gender);
        }

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $countries = Country::all(); // load for filtering

        return view('admin.users.index', compact('users', 'countries', 'search', 'status', 'gender', 'countryId', 'role'));
    }

    public function show(int $id)
    {
        $user = User::withTrashed()->with(['profile.translations', 'country', 'province'])->findOrFail($id);
        
        $stats = $this->statsService->getStats($user);
        
        $devices = $user->devices()->orderBy('last_activity_at', 'desc')->get();
        
        $loginLogs = $user->loginLogs()->orderBy('login_at', 'desc')->paginate(15, ['*'], 'log_page');
        
        $sessions = $user->tasbihSessions()->orderBy('start_time', 'desc')->paginate(15, ['*'], 'session_page');
        
        $goals = $user->dailyGoals()->orderBy('goal_date', 'desc')->paginate(15, ['*'], 'goal_page');

        return view('admin.users.show', compact('user', 'stats', 'devices', 'loginLogs', 'sessions', 'goals'));
    }

    public function suspend(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => false]);
        
        // Revoke all tokens on suspend
        $user->tokens()->delete();
        $user->loginLogs()->whereNull('logout_at')->update(['logout_at' => now()]);

        return redirect()->route('admin.users.show', $id)
            ->with('success', 'User suspended and all sessions revoked successfully.');
    }

    public function unsuspend(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => true]);

        return redirect()->route('admin.users.show', $id)
            ->with('success', 'User unsuspended successfully.');
    }

    public function forceLogout(int $id)
    {
        $user = User::findOrFail($id);
        $user->tokens()->delete();
        $user->loginLogs()->whereNull('logout_at')->update(['logout_at' => now()]);

        return redirect()->route('admin.users.show', $id)
            ->with('success', 'User forced to logout from all devices.');
    }

    public function resetPassword(Request $request, int $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Revoke all tokens on password change
        $user->tokens()->delete();
        $user->loginLogs()->whereNull('logout_at')->update(['logout_at' => now()]);

        return redirect()->route('admin.users.show', $id)
            ->with('success', 'Password reset successfully and user logged out from all devices.');
    }
}
