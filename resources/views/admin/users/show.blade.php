@extends('layouts.app')
@section('title', 'User Profile Audit — ' . $user->name)
@section('page-title', 'User Audit: ' . $user->name)
@section('page-subtitle', 'Detailed inspect of profile settings, statistics, activity, and device history')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.dashboard') }}">User Administration</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Registered Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Main Profile Header Card --}}
    <div class="quran-card p-4 mb-4 shadow-sm border-0 bg-white">
        <div class="row align-items-center">
            <div class="col-auto">
                @if($user->avatar)
                    <img src="{{ asset($user->avatar) }}" class="rounded-circle border border-2 border-primary" width="100" height="100" style="object-fit: cover;" alt="Avatar">
                @else
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-2 border border-2 border-primary" style="width: 100px; height: 100px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="col mt-3 mt-md-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <h2 class="h3 fw-bold text-dark mb-0">{{ $user->name }}</h2>
                    <code>{{ $user->username }}</code>
                    
                    @if($user->trashed())
                        <span class="badge bg-danger text-white rounded-pill px-3 py-1">Pending Deletion</span>
                    @elseif(!$user->status)
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Suspended</span>
                    @else
                        <span class="badge bg-success text-white rounded-pill px-3 py-1">Active</span>
                    @endif
                    
                    @if($user->role === 'admin')
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1">Admin Role</span>
                    @endif
                </div>
                
                <div class="row g-3 text-muted small">
                    <div class="col-auto"><i class="bi bi-envelope-fill me-1 text-primary"></i> {{ $user->email }}</div>
                    <div class="col-auto"><i class="bi bi-calendar-event me-1 text-success"></i> Registered: {{ $user->created_at->format('Y-m-d H:i') }} ({{ $user->created_at->diffForHumans() }})</div>
                    <div class="col-auto"><i class="bi bi-geo-alt-fill me-1 text-danger"></i> Location: 
                        @if($user->country)
                            @php
                                $cName = $user->country->translations->where('language_id', 1)->first()->value ?? 'Unknown';
                                $pName = $user->province ? ($user->province->translations->where('language_id', 1)->first()->value ?? '') : '';
                            @endphp
                            {{ $cName }}{{ $pName ? ', ' . $pName : '' }}
                        @else
                            Not Registered
                        @endif
                    </div>
                    <div class="col-auto"><i class="bi bi-person-fill-lock me-1 text-info"></i> Gender: {{ ucfirst($user->gender ?? 'unspecified') }}</div>
                    <div class="col-auto"><i class="bi bi-cake-fill me-1 text-warning"></i> Age: {{ $user->age ?? 'unspecified' }} (Born: {{ $user->birth_year ?? 'N/A' }})</div>
                </div>
            </div>
            <div class="col-12 col-lg-auto mt-4 mt-lg-0 text-lg-end">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    @if($user->trashed())
                        <span class="text-danger small fw-semibold d-block w-100 mb-1"><i class="bi bi-exclamation-octagon"></i> Recovery Active (Expires {{ $user->deleted_at->addDays(30)->diffForHumans() }})</span>
                    @elseif($user->status)
                        <form action="{{ route('admin.users.suspend', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to suspend this user? This will instantly terminate all their active session keys and log them out.');">
                            @csrf
                            <button type="submit" class="btn btn-warning d-flex align-items-center gap-2">
                                <i class="bi bi-slash-circle"></i> Suspend Profile
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.users.unsuspend', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle"></i> Unsuspend Profile
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('admin.users.force-logout', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to log this user out from all connected devices?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-right"></i> Terminate Sessions
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Layout Split --}}
    <div class="row g-4">
        {{-- Tabs & Sub-Logs --}}
        <div class="col-xl-9 col-lg-8">
            <div class="quran-card p-0 shadow-sm border-0 bg-white">
                {{-- Tabs Headers --}}
                <div class="border-bottom">
                    <ul class="nav nav-tabs border-0" id="profileTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-3 px-4" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                                <i class="bi bi-grid-fill me-2"></i> Overview & Metadata
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4" id="devices-tab" data-bs-toggle="tab" data-bs-target="#devices" type="button" role="tab" aria-controls="devices" aria-selected="false">
                                <i class="bi bi-phone-fill me-2"></i> Devices ({{ $devices->count() }})
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4" id="logins-tab" data-bs-toggle="tab" data-bs-target="#logins" type="button" role="tab" aria-controls="logins" aria-selected="false">
                                <i class="bi bi-clock-history me-2"></i> Login Timeline
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4" id="sessions-tab" data-bs-toggle="tab" data-bs-target="#sessions" type="button" role="tab" aria-controls="sessions" aria-selected="false">
                                <i class="bi bi-fingerprint me-2"></i> Tasbih Sessions
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 px-4" id="goals-tab" data-bs-toggle="tab" data-bs-target="#goals" type="button" role="tab" aria-controls="goals" aria-selected="false">
                                <i class="bi bi-target me-2"></i> Goals
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- Tabs Contents --}}
                <div class="tab-content p-4" id="profileTabsContent">
                    {{-- Tab 1: Overview --}}
                    <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                        {{-- Cached Statistics --}}
                        <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-bar-chart-fill text-primary me-2"></i> Cached Profile Statistics</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block small mb-1 text-uppercase fw-semibold">Total Dhikr Taps</small>
                                    <span class="fs-3 fw-bold text-dark">{{ number_format($stats['total_dhikrs']) }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block small mb-1 text-uppercase fw-semibold">Streak Count (Current / Longest)</small>
                                    <span class="fs-3 fw-bold text-dark">{{ $stats['current_streak'] }} <span class="fs-5 text-muted fw-normal">/ {{ $stats['longest_streak'] }} days</span></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block small mb-1 text-uppercase fw-semibold">Daily Goals Completed</small>
                                    <span class="fs-3 fw-bold text-success">{{ $stats['goals_completed_count'] }} <span class="fs-5 text-muted fw-normal">({{ $stats['goal_completion_rate'] }}%)</span></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block small mb-1 text-uppercase fw-semibold">Total Tasbih Sessions</small>
                                    <span class="fs-3 fw-bold text-info">{{ number_format($stats['total_sessions']) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block small mb-1 text-uppercase fw-semibold">Achievements Unlocked</small>
                                    <span class="fs-3 fw-bold text-warning">{{ $stats['achievements_count'] }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Bio Metadata Translations --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-translate text-success me-2"></i> Dynamic Profile Metadata</h5>
                            <span class="text-muted small"><i class="bi bi-info-circle"></i> Kurdish / Arabic translations mapped dynamically</span>
                        </div>

                        <div class="table-responsive border rounded mb-4">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Language</th>
                                        <th>Field</th>
                                        <th>Localized Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $translations = $user->profile?->translations ?? collect();
                                    @endphp
                                    @forelse($translations as $tr)
                                        @php
                                            $langCode = $tr->language?->code ?? 'N/A';
                                            $langName = $tr->language?->name ?? 'Unknown';
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-primary">{{ strtoupper($langCode) }}</span>
                                                <span class="text-muted small">({{ $langName }})</span>
                                            </td>
                                            <td><code class="text-capitalize">{{ $tr->field }}</code></td>
                                            <td class="arabic-text-container">{{ $tr->value }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">
                                                <i class="bi bi-chat-quote d-block fs-2 mb-2"></i>
                                                No localized bio/profile translations registered
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Extra Profile Data --}}
                        <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-file-earmark-person-fill text-info me-2"></i> Profile Raw Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>Bio:</strong>
                                <p class="text-muted p-3 border rounded bg-light mt-1">{{ $user->profile->bio ?? 'Not provided' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Profile Quote:</strong>
                                <p class="text-muted p-3 border rounded bg-light mt-1">{{ $user->profile->profile_quote ?? 'Not provided' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Nickname:</strong>
                                <span class="text-muted d-block border p-2 rounded bg-light mt-1">{{ $user->profile->nickname ?? 'Not provided' }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Public Title:</strong>
                                <span class="text-muted d-block border p-2 rounded bg-light mt-1">{{ $user->profile->public_title ?? 'Not provided' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 2: Devices --}}
                    <div class="tab-pane fade" id="devices" role="tabpanel" aria-labelledby="devices-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-phone-fill me-2 text-primary"></i> Registered Fingerprinted Devices</h5>
                            <span class="badge bg-secondary">{{ $devices->count() }} active devices</span>
                        </div>

                        <div class="row g-4">
                            @forelse($devices as $device)
                                <div class="col-md-6">
                                    <div class="border rounded p-4 position-relative bg-light h-100">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-laptop me-2"></i> {{ $device->device_name }}</h6>
                                                <span class="badge bg-primary bg-opacity-10 text-primary mb-3">{{ $device->platform }} {{ $device->last_platform_version }}</span>
                                            </div>
                                            <span class="text-muted small"><i class="bi bi-activity text-success"></i> Active: {{ $device->last_activity_at->diffForHumans() }}</span>
                                        </div>
                                        <ul class="list-unstyled mb-0 small text-muted">
                                            <li class="mb-2"><strong>Device UUID:</strong> <code>{{ $device->device_identifier }}</code></li>
                                            <li class="mb-2"><strong>IP Address:</strong> {{ $device->last_ip }}</li>
                                            <li>
                                                <strong>Push Token:</strong> 
                                                @if($device->push_token)
                                                    <span class="badge bg-success text-white">Registered</span>
                                                    <code class="d-block text-truncate mt-1" style="max-width: 280px;" title="{{ $device->push_token }}">{{ $device->push_token }}</code>
                                                @else
                                                    <span class="badge bg-secondary text-white">Not Registered</span>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 text-muted">
                                    <i class="bi bi-phone fs-1 d-block mb-3"></i>
                                    No registered devices for this user
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Tab 3: Timeline --}}
                    <div class="tab-pane fade" id="logins" role="tabpanel" aria-labelledby="logins-tab">
                        <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-calendar-check-fill text-success me-2"></i> Login Timeline Logs</h5>
                        <div class="table-responsive border rounded">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>IP Address</th>
                                        <th>Device Name</th>
                                        <th>Platform</th>
                                        <th>Login Time</th>
                                        <th>Logout Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loginLogs as $log)
                                        <tr>
                                            <td><code>{{ $log->ip_address }}</code></td>
                                            <td>{{ $log->device }}</td>
                                            <td><span class="badge bg-light text-dark">{{ $log->platform }}</span></td>
                                            <td>
                                                <div>{{ $log->login_at->format('Y-m-d H:i') }}</div>
                                                <small class="text-muted">{{ $log->login_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                @if($log->logout_at)
                                                    <div>{{ $log->logout_at->format('Y-m-d H:i') }}</div>
                                                    <small class="text-muted">{{ $log->logout_at->diffForHumans() }}</small>
                                                @else
                                                    <span class="badge bg-success text-white rounded-pill px-3 py-1">Active Session</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No login timeline logs recorded</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($loginLogs->hasPages())
                            <div class="mt-4">{{ $loginLogs->links() }}</div>
                        @endif
                    </div>

                    {{-- Tab 4: Tasbih Sessions --}}
                    <div class="tab-pane fade" id="sessions" role="tabpanel" aria-labelledby="sessions-tab">
                        <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-clock-history text-info me-2"></i> Recent Tasbih Sessions</h5>
                        <div class="table-responsive border rounded">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Dhikr Name</th>
                                        <th>Dhikr Count</th>
                                        <th>Duration</th>
                                        <th>Speed (taps/min)</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sessions as $session)
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-dark">{{ $session->dhikr?->name ?? $session->custom_dhikr_name ?? 'General Count' }}</span>
                                            </td>
                                            <td class="fw-bold text-primary">{{ number_format($session->total_count) }}</td>
                                            <td>
                                                @php
                                                    $sec = $session->duration_seconds;
                                                    $min = floor($sec / 60);
                                                    $secRem = $sec % 60;
                                                    echo $min > 0 ? "{$min}m {$secRem}s" : "{$secRem}s";
                                                @endphp
                                            </td>
                                            <td>{{ $session->avg_per_minute }}</td>
                                            <td>
                                                <div>{{ $session->start_time->format('Y-m-d H:i') }}</div>
                                            </td>
                                            <td>
                                                @if($session->status === 'completed')
                                                    <span class="badge bg-success rounded-pill px-3 py-1 text-white">Completed</span>
                                                @else
                                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1">{{ ucfirst($session->status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No Tasbih sessions recorded yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($sessions->hasPages())
                            <div class="mt-4">{{ $sessions->links() }}</div>
                        @endif
                    </div>

                    {{-- Tab 5: Goals --}}
                    <div class="tab-pane fade" id="goals" role="tabpanel" aria-labelledby="goals-tab">
                        <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-target text-danger me-2"></i> Daily Goal Milestones</h5>
                        <div class="table-responsive border rounded">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Goal Date</th>
                                        <th>Target Value</th>
                                        <th>Progress</th>
                                        <th>Completion Rate</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($goals as $goal)
                                        <tr>
                                            <td><span class="fw-bold text-dark">{{ $goal->goal_date->format('Y-m-d') }}</span></td>
                                            <td>{{ number_format($goal->goal_value) }}</td>
                                            <td>{{ number_format($goal->today_progress) }}</td>
                                            <td>
                                                @php
                                                    $rate = $goal->goal_value > 0 ? round(($goal->today_progress / $goal->goal_value) * 100) : 0;
                                                @endphp
                                                <div class="d-flex align-items-center gap-2" style="width: 150px;">
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar bg-success" style="width: {{ min(100, $rate) }}%"></div>
                                                    </div>
                                                    <small class="text-muted">{{ $rate }}%</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($goal->is_completed)
                                                    <span class="badge bg-success text-white rounded-pill px-3 py-1"><i class="bi bi-check2"></i> Completed</span>
                                                @else
                                                    <span class="badge bg-light text-dark rounded-pill px-3 py-1">In Progress</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No daily goals configured or attempted</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($goals->hasPages())
                            <div class="mt-4">{{ $goals->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Admin Tools --}}
        <div class="col-xl-3 col-lg-4">
            {{-- Security & Password Reset Panel --}}
            <div class="quran-card p-4 shadow-sm border-0 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock-fill text-danger me-2"></i> Security Panel</h5>
                <hr>
                
                <form action="{{ route('admin.users.reset-password', $user->id) }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Force Reset Password</label>
                        <input type="password" name="password" class="form-control" placeholder="New password" required>
                        <div class="invalid-feedback">Password is required</div>
                    </div>
                    
                    <div class="mb-3">
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                        <div class="invalid-feedback">Password confirmation is required</div>
                    </div>
                    
                    <button type="submit" class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-key-fill"></i> Reset Password
                    </button>
                </form>
            </div>

            {{-- Audit Details --}}
            <div class="quran-card p-4 shadow-sm border-0 bg-white">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-journal-text text-primary me-2"></i> Administrative Info</h5>
                <hr>
                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-2 d-flex justify-content-between">
                        <strong>User ID:</strong> 
                        <span>{{ $user->id }}</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <strong>Profile ID:</strong> 
                        <span>{{ $user->profile->id ?? 'None' }}</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <strong>Active Devices:</strong> 
                        <span>{{ $devices->count() }}</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <strong>Preferred Locale:</strong> 
                        <span>{{ strtoupper($user->preferred_locale ?? 'EN') }}</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <strong>Daily Streak:</strong> 
                        <span>{{ $user->streak_days }} days</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <strong>Total Points:</strong> 
                        <span class="fw-bold text-primary">{{ number_format($user->points_total) }} pts</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .arabic-text-container {
        font-family: 'Cairo', 'Inter', sans-serif;
    }
</style>
@endpush
