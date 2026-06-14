@extends('layouts.app')

@section('title', __('goal_progress.title'))
@section('page-title', __('goal_progress.title'))

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">{{ __('sidebar.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('goal_progress.title') }}</li>
        </ol>
    </nav>

    <!-- Page Title & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 mb-1 text-primary fw-bold">{{ __('goal_progress.title') }}</h1>
            <p class="text-muted mb-0">{{ __('goal_progress.subtitle') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('user-goal-progress.export', ['date' => $date]) }}" class="btn btn-outline-success d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                {{ __('goal_progress.export_csv') }}
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
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, hsl(240, 56%, 45%), hsl(240, 60%, 55%));">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">{{ __('goal_progress.active_users') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1">{{ $totalUsers }}</h2>
                    <span class="small opacity-75">{{ __('daily_goals.date') }}: {{ $date }}</span>
                </div>
            </div>
        </div>

        <!-- Completed Goals Today -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, hsl(160, 93%, 30%), hsl(160, 84%, 40%));">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">{{ __('goal_progress.completed_goals') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-trophy-fill fs-4"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1">{{ $completedGoalsCount }}</h2>
                    <span class="small opacity-75">Today</span>
                </div>
            </div>
        </div>

        <!-- Completion Rate -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, hsl(35, 91%, 45%), hsl(35, 95%, 55%));">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">{{ __('goal_progress.completion_rate') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-pie-chart-fill fs-4"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1">{{ $completionRate }}%</h2>
                    <span class="small opacity-75">Avg Progress: {{ $averagePercentage }}%</span>
                </div>
            </div>
        </div>

        <!-- Popular Goal Value -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, hsl(280, 68%, 40%), hsl(280, 60%, 50%));">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-6 fw-semibold opacity-75">{{ __('goal_progress.most_active_type') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20">
                            <i class="bi bi-activity fs-4"></i>
                        </div>
                    </div>
                    <h3 class="h4 fw-bold mb-1 text-truncate" title="{{ $mostActiveGoalType }}">{{ $mostActiveGoalType }}</h3>
                    <span class="small opacity-75">Top template choice</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Filter & Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('user-goal-progress.index') }}" class="row g-3 mb-4">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0" value="{{ request('search') }}" placeholder="{{ __('goal_progress.search_user') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-calendar3 text-muted"></i></span>
                        <input type="date" name="date" class="form-control bg-light border-0" value="{{ $date }}">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary">{{ __('goal_progress.filter') }}</button>
                </div>
            </form>

            <!-- Responsive Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light border-0">
                        <tr>
                            <th scope="col" class="border-0 rounded-start">{{ __('goal_progress.user_name') }}</th>
                            <th scope="col" class="border-0">{{ __('goal_progress.goal_name') }}</th>
                            <th scope="col" class="border-0">{{ __('goal_progress.progress') }}</th>
                            <th scope="col" class="border-0" style="width: 200px;">Progress Bar</th>
                            <th scope="col" class="border-0">{{ __('goal_progress.status') }}</th>
                            <th scope="col" class="border-0">{{ __('goal_progress.badges_awarded') }}</th>
                            <th scope="col" class="border-0 text-end rounded-end">{{ __('goal_progress.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $progress = $user->current_progress_record;
                                
                                $goalId = $progress ? $progress->goal_id : null;
                                $currentProgress = $progress ? $progress->current_progress : 0;
                                
                                $goalValue = 100;
                                $goalName = 'No Goal Set';
                                if ($progress) {
                                    $template = $templates->firstWhere('id', $progress->goal_id);
                                    if ($template) {
                                        $goalValue = $template->value;
                                        $translation = $template->translations->firstWhere('locale', app()->getLocale()) ?? $template->translations->first();
                                        $goalName = $translation ? $translation->title : "Template #{$template->id}";
                                    }
                                }

                                $percent = $goalValue > 0 ? min(100, round(($currentProgress / $goalValue) * 100)) : 0;
                                $isCompleted = $progress ? $progress->is_completed : false;

                                // Badge classes mapping
                                $hasBronze = $user->total_completed_goals >= 1;
                                $hasSilver = $user->total_completed_goals >= 10;
                                $hasGold = $user->total_completed_goals >= 50;
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
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded">{{ $goalName }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $currentProgress }}</span> <span class="text-muted">/ {{ $goalValue }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            @php
                                                // Color transitions: 0–49% → blue, 50–79% → orange, 80–100% → green
                                                $barColorClass = 'bg-primary'; // default blue
                                                if ($percent >= 80) {
                                                    $barColorClass = 'bg-success'; // green
                                                } elseif ($percent >= 50) {
                                                    $barColorClass = 'bg-warning'; // orange
                                                }
                                            @endphp
                                            <div class="progress-bar rounded {{ $barColorClass }}" 
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
                                    @if($progress)
                                        @if($isCompleted)
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded">🟢 {{ __('daily_goals.completed') }}</span>
                                        @elseif($percent >= 80)
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded">🟠 Near Completion</span>
                                        @else
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded">🔵 {{ __('daily_goals.in_progress') }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded">{{ __('daily_goals.not_started') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <span class="badge {{ $hasBronze ? 'bg-bronze text-white' : 'bg-light text-muted border' }}" style="{{ $hasBronze ? 'background-color: #cd7f32;' : '' }}" title="Bronze Badge (1+ completions)">🥉</span>
                                        <span class="badge {{ $hasSilver ? 'bg-silver text-white' : 'bg-light text-muted border' }}" style="{{ $hasSilver ? 'background-color: #c0c0c0;' : '' }}" title="Silver Badge (10+ completions)">🥈</span>
                                        <span class="badge {{ $hasGold ? 'bg-gold text-dark' : 'bg-light text-muted border' }}" style="{{ $hasGold ? 'background-color: #ffd700;' : '' }}" title="Gold Badge (50+ completions)">🥇</span>
                                        <span class="small text-muted align-self-center">({{ $user->total_completed_goals }} completed)</span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    @if($progress)
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editGoalModal" 
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-goal-id="{{ $goalId }}"
                                                data-goal-value="{{ $goalValue }}"
                                                data-progress="{{ $currentProgress }}">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        
                                        @if(!$isCompleted)
                                            <form method="POST" action="{{ route('user-goal-progress.force-complete', $user->id) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="goal_id" value="{{ $goalId }}">
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <button type="submit" class="btn btn-sm btn-outline-success me-1" title="{{ __('goal_progress.force_complete') }}">
                                                    <i class="bi bi-check-all"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#resetGoalModal"
                                                data-action="{{ route('user-goal-progress.reset', $user->id) }}"
                                                data-goal-id="{{ $goalId }}"
                                                data-user-name="{{ $user->name }}">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    @else
                                        <!-- Add first goal progress manually -->
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editGoalModal"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-goal-id="{{ $templates->first() ? $templates->first()->id : '' }}"
                                                data-goal-value="{{ $templates->first() ? $templates->first()->value : 100 }}"
                                                data-progress="0">
                                            <i class="bi bi-plus-lg"></i> Set Goal
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
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
                    <h5 class="modal-title fw-bold" id="editGoalModalLabel">{{ __('goal_progress.edit_progress') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">Editing goal progress details for <strong id="modalUserName" class="text-primary"></strong> on <strong>{{ $date }}</strong></p>
                    
                    <div class="mb-3">
                        <label for="inputGoalId" class="form-label fw-semibold">{{ __('goal_progress.goal_name') }}</label>
                        <select name="goal_id" id="inputGoalId" class="form-select" required>
                            @foreach($templates as $tpl)
                                @php
                                    $trans = $tpl->translations->firstWhere('locale', app()->getLocale()) ?? $tpl->translations->first();
                                    $title = $trans ? $trans->title : "Template #{$tpl->id}";
                                @endphp
                                <option value="{{ $tpl->id }}">{{ $title }} ({{ $tpl->value }} target)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="inputProgress" class="form-label fw-semibold">{{ __('goal_progress.progress') }}</label>
                        <input type="number" class="form-control" id="inputProgress" name="current_progress" min="0" required>
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
                <input type="hidden" id="resetGoalId" name="goal_id" value="">
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
                const goalId = button.getAttribute('data-goal-id');
                const progress = button.getAttribute('data-progress');

                const form = editGoalModal.querySelector('#editGoalForm');
                form.action = `/user-goal-progress/${userId}/edit`;

                editGoalModal.querySelector('#modalUserName').textContent = userName;
                editGoalModal.querySelector('#inputGoalId').value = goalId;
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
                const goalId = button.getAttribute('data-goal-id');

                const form = resetGoalModal.querySelector('#resetGoalForm');
                form.action = actionUrl;

                resetGoalModal.querySelector('#resetGoalId').value = goalId;

                const bodyText = resetGoalModal.querySelector('#resetModalText');
                bodyText.innerHTML = `Are you sure you want to reset the daily progress for <strong class="text-danger">${userName}</strong> to 0 for date <strong>{{ $date }}</strong>? This action cannot be undone.`;
            });
        }
    });
</script>
@endsection
