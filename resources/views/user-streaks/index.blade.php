@extends('layouts.app')

@section('title', __('user_streaks.title'))
@section('page-title', __('user_streaks.title'))

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ __('sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('user_streaks.title') }}</li>
        </ol>
    </nav>

    <!-- Page Title & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 mb-1 text-primary fw-bold">{{ __('user_streaks.title') }}</h1>
            <p class="text-muted mb-0">{{ __('user_streaks.subtitle') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('user-streaks.export') }}" class="btn btn-outline-success d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                {{ __('user_streaks.export_csv') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Analytics Dashboard Cards -->
    <div class="row g-4 mb-4">
        <!-- Average Streak -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #4f46e5, #6366f1);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">{{ __('user_streaks.average_streak') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-calculator fs-4"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1">{{ $averageStreak }}</h2>
                    <span class="small opacity-75">{{ __('user_streaks.current_streak') }}</span>
                </div>
            </div>
        </div>

        <!-- Active Today -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #059669, #10b981);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">{{ __('user_streaks.active_today') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1">{{ $activeToday }}</h2>
                    <span class="small opacity-75">{{ __('user_streaks.status') }}</span>
                </div>
            </div>
        </div>

        <!-- Drop Rate -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #dc2626, #ef4444);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">{{ __('user_streaks.drop_rate') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-graph-down-arrow fs-4"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1">{{ $dropRate }}%</h2>
                    <span class="small opacity-75">{{ __('user_streaks.broken') }}</span>
                </div>
            </div>
        </div>

        <!-- Top Streak -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #ca8a04, #eab308);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">Top Active Streak</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-fire fs-4"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1">
                        {{ $topUsers->first()?->current_streak ?? 0 }}
                    </h2>
                    <span class="small opacity-75">Held by: {{ $topUsers->first()?->user?->name ?? 'None' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Table Section -->
        <div class="col-12 col-xl-9">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <!-- Search Filter -->
                    <form method="GET" action="{{ route('user-streaks.index') }}" class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0" value="{{ request('search') }}" placeholder="{{ __('user_streaks.search_user') }}">
                            <button type="submit" class="btn btn-primary px-4">{{ __('sidebar.search_surah') ? 'Search' : 'Search' }}</button>
                        </div>
                    </form>

                    <!-- Responsive Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light border-0">
                                <tr>
                                    <th scope="col" class="border-0 rounded-start">{{ __('user_streaks.user_name') }}</th>
                                    <th scope="col" class="border-0">{{ __('user_streaks.current_streak') }}</th>
                                    <th scope="col" class="border-0">{{ __('user_streaks.longest_streak') }}</th>
                                    <th scope="col" class="border-0">{{ __('user_streaks.last_activity') }}</th>
                                    <th scope="col" class="border-0">{{ __('user_streaks.status') }}</th>
                                    <th scope="col" class="border-0 text-end rounded-end">{{ __('user_streaks.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    @php
                                        $streak = $user->tasbihStreak;
                                        $lastActivity = $streak && $streak->last_activity_date ? $streak->last_activity_date->toDateString() : null;
                                        $isActive = ($lastActivity === $today || $lastActivity === $yesterday);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-3 fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">{{ $user->name }}</h6>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-bold">
                                                🔥 {{ $streak ? $streak->current_streak : 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-bold">
                                                🏆 {{ $streak ? $streak->longest_streak : 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted small">
                                                {{ $lastActivity ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($isActive)
                                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded">{{ __('user_streaks.active') }}</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded">{{ __('user_streaks.broken') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editStreakModal" 
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-current="{{ $streak ? $streak->current_streak : 0 }}"
                                                    data-longest="{{ $streak ? $streak->longest_streak : 0 }}"
                                                    data-last-activity="{{ $lastActivity }}">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#resetStreakModal"
                                                    data-action="{{ route('user-streaks.reset', $user->id) }}"
                                                    data-user-name="{{ $user->name }}">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No users found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Leaderboard Section -->
        <div class="col-12 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-trophy-fill text-warning"></i>
                        {{ __('user_streaks.top_streaks') }}
                    </h5>
                    
                    <div class="d-flex flex-column gap-3">
                        @forelse($topUsers as $index => $topStreak)
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="fw-bold text-muted" style="width: 20px;">#{{ $index + 1 }}</span>
                                    <div>
                                        <h6 class="mb-0 fw-bold small text-truncate" style="max-width: 120px;">{{ $topStreak->user?->name ?? 'Unknown' }}</h6>
                                        <small class="text-muted" style="font-size: 10px;">Longest: {{ $topStreak->longest_streak }}</small>
                                    </div>
                                </div>
                                <span class="badge bg-danger text-white rounded-pill fw-bold">
                                    🔥 {{ $topStreak->current_streak }}
                                </span>
                            </div>
                        @empty
                            <div class="text-muted text-center py-4 small">
                                No records found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Streak Modal -->
<div class="modal fade" id="editStreakModal" tabindex="-1" aria-labelledby="editStreakModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="editStreakForm" method="POST" action="">
                @csrf
                <div class="modal-header border-0 bg-light rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold" id="editStreakModalLabel">{{ __('user_streaks.edit_streak') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">Editing streak parameters for <strong id="modalUserName" class="text-primary"></strong></p>
                    
                    <div class="mb-3">
                        <label for="inputCurrent" class="form-label fw-semibold">{{ __('user_streaks.current_streak') }}</label>
                        <input type="number" class="form-control" id="inputCurrent" name="current_streak" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label for="inputLongest" class="form-label fw-semibold">{{ __('user_streaks.longest_streak') }}</label>
                        <input type="number" class="form-control" id="inputLongest" name="longest_streak" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label for="inputLastActivity" class="form-label fw-semibold">{{ __('user_streaks.last_activity') }}</label>
                        <input type="date" class="form-control" id="inputLastActivity" name="last_activity_date">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('user_streaks.cancel') }}</button>
                    <button type="submit" class="btn btn-primary px-4">{{ __('user_streaks.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Streak Confirmation Modal -->
<div class="modal fade" id="resetStreakModal" tabindex="-1" aria-labelledby="resetStreakModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="resetStreakForm" method="POST" action="">
                @csrf
                <div class="modal-header border-0 bg-danger bg-opacity-10 text-danger rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold" id="resetStreakModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ __('user_streaks.reset_confirm_title') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-0" id="resetModalText"></p>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('user_streaks.cancel') }}</button>
                    <button type="submit" class="btn btn-danger px-4">{{ __('user_streaks.reset') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts for handling modals data injection -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit Modal Setup
        const editStreakModal = document.getElementById('editStreakModal');
        if (editStreakModal) {
            editStreakModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const userName = button.getAttribute('data-user-name');
                const current = button.getAttribute('data-current');
                const longest = button.getAttribute('data-longest');
                const lastActivity = button.getAttribute('data-last-activity');

                const form = editStreakModal.querySelector('#editStreakForm');
                form.action = `/user-streaks/${userId}/edit`;

                editStreakModal.querySelector('#modalUserName').textContent = userName;
                editStreakModal.querySelector('#inputCurrent').value = current;
                editStreakModal.querySelector('#inputLongest').value = longest;
                editStreakModal.querySelector('#inputLastActivity').value = lastActivity || '';
            });
        }

        // Reset Modal Setup
        const resetStreakModal = document.getElementById('resetStreakModal');
        if (resetStreakModal) {
            resetStreakModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const actionUrl = button.getAttribute('data-action');
                const userName = button.getAttribute('data-user-name');

                const form = resetStreakModal.querySelector('#resetStreakForm');
                form.action = actionUrl;

                const bodyText = resetStreakModal.querySelector('#resetModalText');
                bodyText.innerHTML = `Are you sure you want to reset the current streak for <strong class="text-danger">${userName}</strong> to 0? This action cannot be undone.`;
            });
        }
    });
</script>
@endsection
