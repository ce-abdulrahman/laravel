@extends('layouts.app')

@section('title', __('daily_goals.title'))
@section('page-title', __('daily_goals.title'))

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ __('sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('daily_goals.title') }}</li>
        </ol>
    </nav>

    <!-- Page Title & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 mb-1 text-primary fw-bold">{{ __('daily_goals.title') }}</h1>
            <p class="text-muted mb-0">{{ __('daily_goals.subtitle') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('user-goals.export', ['date' => $date]) }}" class="btn btn-outline-success d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                {{ __('daily_goals.export_csv') }}
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
        <!-- Active Trackers -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #4f46e5, #6366f1);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">{{ __('daily_goals.active_users') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-person-check-fill fs-4"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1">{{ $activeGoalsCount }}</h2>
                    <span class="small opacity-75">{{ __('daily_goals.date') }}: {{ $date }}</span>
                </div>
            </div>
        </div>

        <!-- Completion Rate -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #059669, #10b981);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">{{ __('daily_goals.completion_rate') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-pie-chart-fill fs-4"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1">{{ $completionRate }}%</h2>
                    <span class="small opacity-75">{{ __('daily_goals.completed') }}</span>
                </div>
            </div>
        </div>

        <!-- Average Daily Progress -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #ca8a04, #eab308);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">{{ __('daily_goals.average_progress') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-bar-chart-steps fs-4"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1">{{ $averageProgress }}</h2>
                    <span class="small opacity-75">Dhikr / User</span>
                </div>
            </div>
        </div>

        <!-- Popular Goal Value -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #7b1fa2, #9c27b0);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">{{ __('daily_goals.popular_goal') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-bookmark-star-fill fs-4"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1">{{ $popularGoal }}</h2>
                    <span class="small opacity-75">Dhikr Target</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Filter & Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('user-goals.index') }}" class="row g-3 mb-4">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0" value="{{ request('search') }}" placeholder="{{ __('daily_goals.search_user') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-calendar3 text-muted"></i></span>
                        <input type="date" name="date" class="form-control bg-light border-0" value="{{ $date }}">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary">{{ __('daily_goals.filter') }}</button>
                </div>
            </form>

            <!-- Responsive Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light border-0">
                        <tr>
                            <th scope="col" class="border-0 rounded-start">{{ __('daily_goals.user_name') }}</th>
                            <th scope="col" class="border-0">{{ __('daily_goals.goal_value') }}</th>
                            <th scope="col" class="border-0">{{ __('daily_goals.today_progress') }}</th>
                            <th scope="col" class="border-0" style="width: 200px;">Progress Bar</th>
                            <th scope="col" class="border-0">{{ __('daily_goals.status') }}</th>
                            <th scope="col" class="border-0 text-end rounded-end">{{ __('daily_goals.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $goal = $user->today_goal;
                                $goalValue = $goal ? $goal->goal_value : 100;
                                $progress = $goal ? $goal->today_progress : 0;
                                $percent = $goalValue > 0 ? min(100, round(($progress / $goalValue) * 100)) : 0;
                                $isCompleted = $goal ? $goal->is_completed : false;
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
                                    <span class="fw-semibold text-dark">{{ $goalValue }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary">{{ $progress }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar rounded {{ $isCompleted ? 'bg-success' : 'bg-primary' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $percent }}%;" 
                                                 aria-valuenow="{{ $percent }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span class="small fw-bold text-muted" style="min-width: 35px;">{{ $percent }}%</span>
                                    </div>
                                </td>
                                <td>
                                    @if($goal)
                                        @if($isCompleted)
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded">{{ __('daily_goals.completed') }}</span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded">{{ __('daily_goals.in_progress') }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded">{{ __('daily_goals.not_started') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editGoalModal" 
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            data-goal-value="{{ $goalValue }}"
                                            data-progress="{{ $progress }}">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#resetGoalModal"
                                            data-action="{{ route('user-goals.reset', $user->id) }}"
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
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Edit Goal Modal -->
<div class="modal fade" id="editGoalModal" tabindex="-1" aria-labelledby="editGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="editGoalForm" method="POST" action="">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="modal-header border-0 bg-light rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold" id="editGoalModalLabel">{{ __('daily_goals.edit_goal') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">Editing goal details for <strong id="modalUserName" class="text-primary"></strong> on <strong>{{ $date }}</strong></p>
                    
                    <div class="mb-3">
                        <label for="inputGoalValue" class="form-label fw-semibold">{{ __('daily_goals.goal_value') }}</label>
                        <input type="number" class="form-control" id="inputGoalValue" name="goal_value" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="inputProgress" class="form-label fw-semibold">{{ __('daily_goals.today_progress') }}</label>
                        <input type="number" class="form-control" id="inputProgress" name="today_progress" min="0" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('daily_goals.cancel') }}</button>
                    <button type="submit" class="btn btn-primary px-4">{{ __('daily_goals.save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Goal Confirmation Modal -->
<div class="modal fade" id="resetGoalModal" tabindex="-1" aria-labelledby="resetGoalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form id="resetGoalForm" method="POST" action="">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="modal-header border-0 bg-danger bg-opacity-10 text-danger rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold" id="resetGoalModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ __('daily_goals.reset_confirm_title') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-0" id="resetModalText"></p>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('daily_goals.cancel') }}</button>
                    <button type="submit" class="btn btn-danger px-4">{{ __('daily_goals.reset') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts for handling modals data injection -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit Modal Setup
        const editGoalModal = document.getElementById('editGoalModal');
        if (editGoalModal) {
            editGoalModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const userName = button.getAttribute('data-user-name');
                const goalValue = button.getAttribute('data-goal-value');
                const progress = button.getAttribute('data-progress');

                const form = editGoalModal.querySelector('#editGoalForm');
                form.action = `/user-goals/${userId}/edit`;

                editGoalModal.querySelector('#modalUserName').textContent = userName;
                editGoalModal.querySelector('#inputGoalValue').value = goalValue;
                editGoalModal.querySelector('#inputProgress').value = progress;
            });
        }

        // Reset Modal Setup
        const resetGoalModal = document.getElementById('resetGoalModal');
        if (resetGoalModal) {
            resetGoalModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const actionUrl = button.getAttribute('data-action');
                const userName = button.getAttribute('data-user-name');

                const form = resetGoalModal.querySelector('#resetGoalForm');
                form.action = actionUrl;

                const bodyText = resetGoalModal.querySelector('#resetModalText');
                bodyText.innerHTML = `Are you sure you want to reset the daily progress for <strong class="text-danger">${userName}</strong> to 0 for date <strong>{{ $date }}</strong>? This action cannot be undone.`;
            });
        }
    });
</script>
@endsection
