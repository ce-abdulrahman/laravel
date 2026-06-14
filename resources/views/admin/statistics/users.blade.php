@extends('layouts.app')
@section('title', __('statistics.user_analytics'))
@section('content')
<div class="container-fluid px-4 py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i>{{ __('statistics.user_analytics') }}
            </h1>
            <p class="text-muted mb-0 small">{{ __('statistics.user_analytics_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.statistics.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>{{ __('statistics.back') }}
        </a>
    </div>

    {{-- Search & Sort --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto flex-grow-1">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('statistics.search_users') }}" value="{{ $search }}">
                </div>
                <div class="col-auto">
                    <select name="sort" class="form-select">
                        <option value="total_dhikr" {{ $sort==='total_dhikr' ? 'selected' : '' }}>{{ __('statistics.sort_by_dhikr') }}</option>
                        <option value="total_sessions" {{ $sort==='total_sessions' ? 'selected' : '' }}>{{ __('statistics.sort_by_sessions') }}</option>
                        <option value="productivity_score" {{ $sort==='productivity_score' ? 'selected' : '' }}>{{ __('statistics.sort_by_score') }}</option>
                        <option value="current_streak" {{ $sort==='current_streak' ? 'selected' : '' }}>{{ __('statistics.sort_by_streak') }}</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="order" class="form-select">
                        <option value="desc" {{ $order==='desc' ? 'selected' : '' }}>{{ __('statistics.descending') }}</option>
                        <option value="asc" {{ $order==='asc' ? 'selected' : '' }}>{{ __('statistics.ascending') }}</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">{{ __('statistics.filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">{{ __('statistics.user') }}</th>
                            <th>{{ __('statistics.total_dhikr') }}</th>
                            <th>{{ __('statistics.sessions') }}</th>
                            <th>{{ __('statistics.streak') }}</th>
                            <th>{{ __('statistics.goals') }}</th>
                            <th>{{ __('statistics.achievements') }}</th>
                            <th>{{ __('statistics.fingerprint') }}</th>
                            <th>{{ __('statistics.productivity_score') }}</th>
                            <th>{{ __('statistics.last_calc') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $stat)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $stat->user?->name ?? '—' }}</div>
                                <div class="text-muted" style="font-size:11px">{{ $stat->user?->email }}</div>
                            </td>
                            <td class="fw-semibold">{{ number_format($stat->total_dhikr) }}</td>
                            <td>{{ number_format($stat->total_sessions) }}</td>
                            <td>🔥 {{ $stat->current_streak }} / 🏆 {{ $stat->longest_streak }}</td>
                            <td>
                                {{ $stat->total_goals_completed }} ✅
                                <span class="text-muted small">({{ round($stat->goal_completion_rate, 1) }}%)</span>
                            </td>
                            <td>{{ $stat->total_achievements }}</td>
                            <td>{{ number_format($stat->fingerprint_total_counts) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;width:60px">
                                        <div class="progress-bar" style="width:{{ $stat->productivity_score }}%;background:{{ ['master'=>'#6f42c1','advanced'=>'#0d6efd','dedicated'=>'#198754','active'=>'#ffc107','beginner'=>'#6c757d'][$stat->productivity_label] ?? '#6c757d' }}"></div>
                                    </div>
                                    <span class="small fw-semibold">{{ $stat->productivity_score }}</span>
                                </div>
                                <span class="badge text-capitalize mt-1"
                                    style="background:{{ ['master'=>'#6f42c1','advanced'=>'#0d6efd','dedicated'=>'#198754','active'=>'#ffc107','beginner'=>'#6c757d'][$stat->productivity_label] ?? '#6c757d' }};font-size:10px">
                                    {{ __('statistics.label_' . $stat->productivity_label) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $stat->last_calculated_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
