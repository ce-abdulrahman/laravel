@extends('layouts.app')

@section('title', __('sidebar.leaderboard'))
@section('page-title', __('sidebar.leaderboard'))
@section('page-subtitle', 'Community reading engagement and streaks')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('sidebar.leaderboard') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <!-- Total Users Card -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <h6 class="quran-stat-label">Total Users</h6>
                        <h3 class="quran-stat-value">{{ $totalUsers }}</h3>
                        <span class="quran-stat-sub">Registered accounts</span>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Today Card -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <h6 class="quran-stat-label">Active Today</h6>
                        <h3 class="quran-stat-value">{{ $activeToday }}</h3>
                        <span class="quran-stat-sub">Users read today</span>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active This Week Card -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <h6 class="quran-stat-label">Active This Week</h6>
                        <h3 class="quran-stat-value">{{ $activeThisWeek }}</h3>
                        <span class="quran-stat-sub">Read this week</span>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-calendar-week-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Streaker Card -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <h6 class="quran-stat-label">Top Streak</h6>
                        <h3 class="quran-stat-value">{{ $topStreaker?->streak_days ?? 0 }} Days</h3>
                        <span class="quran-stat-sub">Held by: {{ $topStreaker?->name ?? 'None' }}</span>
                    </div>
                    <div class="quran-stat-icon text-danger">
                        <i class="bi bi-fire"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard Table Section -->
    <div class="quran-card">
        <div class="quran-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="quran-card-title">
                <i class="bi bi-trophy-fill me-2 text-warning"></i>
                Leaderboard Standings
            </h5>
            <!-- Period Filter -->
            <div class="btn-group" role="group" aria-label="Leaderboard Period">
                <a href="{{ route('leaderboard.index', ['period' => 'daily']) }}" 
                   class="btn btn-sm {{ $period === 'daily' ? 'btn-primary' : 'btn-outline-primary' }}">Daily</a>
                <a href="{{ route('leaderboard.index', ['period' => 'weekly']) }}" 
                   class="btn btn-sm {{ $period === 'weekly' ? 'btn-primary' : 'btn-outline-primary' }}">Weekly</a>
                <a href="{{ route('leaderboard.index', ['period' => 'monthly']) }}" 
                   class="btn btn-sm {{ $period === 'monthly' ? 'btn-primary' : 'btn-outline-primary' }}">Monthly</a>
                <a href="{{ route('leaderboard.index', ['period' => 'alltime']) }}" 
                   class="btn btn-sm {{ $period === 'alltime' ? 'btn-primary' : 'btn-outline-primary' }}">All Time</a>
            </div>
        </div>
        
        <div class="quran-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 quran-leaderboard-table">
                    <thead class="table-light text-uppercase fs-7 text-secondary">
                        <tr>
                            <th class="ps-4" style="width: 80px;">Rank</th>
                            <th>User Details</th>
                            <th class="text-center">Current Streak</th>
                            <th class="text-center">Longest Streak</th>
                            <th class="text-center">Points (Ayahs Read)</th>
                            @if(auth()->user()?->role === 'admin')
                                <th class="text-end pe-4" style="width: 150px;">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            @php
                                // Calculate actual absolute rank on current page
                                $userRank = ($users->currentPage() - 1) * $users->perPage() + $index + 1;
                            @endphp
                            <tr class="{{ auth()->id() === $user->id ? 'table-active border-primary border-start border-3' : '' }}">
                                <td class="ps-4">
                                    @if($userRank === 1)
                                        <div class="rank-badge rank-1"><i class="bi bi-trophy-fill gold-icon"></i></div>
                                    @elseif($userRank === 2)
                                        <div class="rank-badge rank-2"><i class="bi bi-trophy-fill silver-icon"></i></div>
                                    @elseif($userRank === 3)
                                        <div class="rank-badge rank-3"><i class="bi bi-trophy-fill bronze-icon"></i></div>
                                    @else
                                        <span class="fw-bold text-muted ps-2">#{{ $userRank }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar-placeholder me-3">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 fw-semibold">{{ $user->name }}</h6>
                                                @if(auth()->user()?->role === 'admin')
                                                    <button class="btn btn-link btn-sm p-0 ms-2 text-decoration-none" 
                                                            type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#user-details-{{ $user->id }}" 
                                                            aria-expanded="false" 
                                                            aria-controls="user-details-{{ $user->id }}"
                                                            title="View detailed stats">
                                                        <i class="bi bi-info-circle-fill text-primary"></i>
                                                    </button>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($user->streak_days > 0)
                                        <span class="badge bg-danger-subtle text-danger py-2 px-3 rounded-pill fw-semibold">
                                            <i class="bi bi-fire me-1"></i> {{ $user->streak_days }} days
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($user->longest_streak > 0)
                                        <span class="badge bg-info-subtle text-info py-2 px-3 rounded-pill fw-semibold">
                                            <i class="bi bi-lightning-fill me-1"></i> {{ $user->longest_streak }} days
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $points = $period === 'alltime' ? ($user->alltime_points ?? $user->points_total) : $user->period_points;
                                    @endphp
                                    <span class="badge bg-success py-2 px-3 rounded-pill fw-bold fs-6">
                                        +{{ $points }} pts
                                    </span>
                                </td>
                                @if(auth()->user()?->role === 'admin')
                                    <td class="text-end pe-4">
                                        @if(auth()->id() !== $user->id)
                                            <form action="{{ route('leaderboard.reset', $user->id) }}" method="POST" class="d-inline reset-points-form">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="confirmReset(event, '{{ $user->name }}')">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                            @if(auth()->user()?->role === 'admin')
                                <tr>
                                    <td colspan="6" class="p-0 border-0">
                                        <div class="collapse bg-light-subtle ps-5 py-3" id="user-details-{{ $user->id }}">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-clock-history me-2 text-primary fs-5"></i>
                                                        <div>
                                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Total Reading Time</small>
                                                            <span class="fw-semibold text-dark">
                                                                @php
                                                                    $totalSeconds = $user->total_seconds_spent ?? 0;
                                                                    if ($totalSeconds < 60) {
                                                                        echo $totalSeconds . ' sec';
                                                                    } elseif ($totalSeconds < 3600) {
                                                                        echo floor($totalSeconds / 60) . ' min';
                                                                    } else {
                                                                        echo number_format($totalSeconds / 3600, 1) . ' hrs';
                                                                    }
                                                                @endphp
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-bookmark-fill me-2 text-warning fs-5"></i>
                                                        <div>
                                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Bookmarks</small>
                                                            <span class="fw-semibold text-dark">{{ $user->bookmarks_count ?? 0 }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-heart-fill me-2 text-danger fs-5"></i>
                                                        <div>
                                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Favorites</small>
                                                            <span class="fw-semibold text-dark">{{ $user->favorites_count ?? 0 }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-journal-check me-2 text-success fs-5"></i>
                                                        <div>
                                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Active Plans</small>
                                                            <span class="fw-semibold text-dark">{{ $user->memorization_plans_count ?? 0 }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <i class="bi bi-calendar-event me-2 text-muted fs-6"></i>
                                                        <div>
                                                            <small class="text-muted d-block" style="font-size: 0.75rem; line-height: 1;">Registered Date</small>
                                                            <span class="fw-semibold text-dark" style="font-size: 0.8rem;">{{ $user->created_at ? $user->created_at->format('Y-m-d') : 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-calendar-check me-2 text-muted fs-6"></i>
                                                        <div>
                                                            <small class="text-muted d-block" style="font-size: 0.75rem; line-height: 1;">Last Active Date</small>
                                                            <span class="fw-semibold text-dark" style="font-size: 0.8rem;">{{ $user->last_read_date ? $user->last_read_date->format('Y-m-d') : 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()?->role === 'admin' ? 6 : 5 }}" class="text-center py-5 text-muted">
                                    <i class="bi bi-people quran-empty-icon d-block mb-3 fs-1 text-secondary"></i>
                                    <h6>No users found on the leaderboard for this period.</h6>
                                    <p class="mb-0">Keep reading the Quran to gain points and start a streak!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($users->hasPages())
                <div class="d-flex justify-content-between align-items-center p-4 border-top">
                    <span class="text-muted small">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                    </span>
                    <div>
                        {{ $users->appends(['period' => $period])->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Premium Styling overrides for Leaderboard */
    .quran-leaderboard-table th {
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .user-avatar-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--quran-primary-light, #1b7340);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    .rank-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
    .gold-icon { color: #FFD700; filter: drop-shadow(0 2px 4px rgba(255,215,0,0.3)); }
    .silver-icon { color: #C0C0C0; filter: drop-shadow(0 2px 4px rgba(192,192,192,0.3)); }
    .bronze-icon { color: #CD7F32; filter: drop-shadow(0 2px 4px rgba(205,127,50,0.3)); }

    /* Custom variables check */
    :root {
        --quran-primary: #1B7340;
        --quran-primary-light: rgba(27, 115, 64, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    function confirmReset(event, userName) {
        event.preventDefault();
        const form = event.target.closest('form');
        window.confirmAction(`Are you sure you want to reset all points and streak stats for ${userName}? This action cannot be undone.`, function() {
            window.showLoading();
            form.submit();
        });
    }
</script>
@endpush
