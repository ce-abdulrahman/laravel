@extends('layouts.app')
@section('title', __('leaderboard.title'))
@section('page-title', __('leaderboard.title'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('leaderboard.title') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">🏆 {{ __('leaderboard.title') }}</h1>
            <div class="text-muted small">{{ __('leaderboard.subtitle') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.leaderboard.index', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-outline-success d-flex align-items-center gap-2">
                <i class="bi bi-download"></i> {{ __('leaderboard.actions.export') }}
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="leaderboardTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.overview') }}">{{ __('leaderboard.tabs.overview') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.leaderboard.index') }}">{{ __('leaderboard.tabs.standings') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.config') }}">{{ __('leaderboard.tabs.config') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.analytics') }}">{{ __('leaderboard.tabs.analytics') }}</a>
        </li>
    </ul>

    {{-- Filter Toolbar --}}
    <div class="quran-card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.leaderboard.index') }}" class="d-flex gap-2 flex-wrap">
                <div class="quran-table-search flex-grow-1" style="min-width: 250px;">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control border-0 bg-transparent py-2 shadow-none"
                           placeholder="{{ __('leaderboard.placeholders.search') }}">
                </div>
                <select name="period" class="quran-form-control" style="width:auto; min-width: 150px;">
                    <option value="daily" {{ $periodType === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $periodType === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $periodType === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="alltime" {{ $periodType === 'alltime' ? 'selected' : '' }}>All Time</option>
                    <option value="achievement" {{ $periodType === 'achievement' ? 'selected' : '' }}>Achievement</option>
                    <option value="streak" {{ $periodType === 'streak' ? 'selected' : '' }}>Streak</option>
                </select>
                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 px-4">
                    <i class="bi bi-funnel"></i> {{ __('leaderboard.actions.filter') }}
                </button>
                <a href="{{ route('admin.leaderboard.index') }}" class="btn btn-secondary d-flex align-items-center px-3" title="Reset">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </form>
        </div>
    </div>

    {{-- Standings Grid --}}
    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">{{ __('leaderboard.fields.rank') }}</th>
                        <th>{{ __('leaderboard.fields.user') }}</th>
                        <th class="text-center">{{ __('leaderboard.fields.score') }}</th>
                        <th class="text-center">{{ __('leaderboard.fields.movement') }}</th>
                        <th class="text-center">{{ __('leaderboard.fields.privacy') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td class="ps-4">
                            @if($u->rank_position === 1)
                                <span class="badge bg-warning text-dark px-2.5 py-1.5"><i class="bi bi-trophy-fill me-1"></i>1st</span>
                            @elseif($u->rank_position === 2)
                                <span class="badge bg-secondary text-white px-2.5 py-1.5"><i class="bi bi-trophy-fill me-1"></i>2nd</span>
                            @elseif($u->rank_position === 3)
                                <span class="badge bg-danger text-white px-2.5 py-1.5"><i class="bi bi-trophy-fill me-1"></i>3rd</span>
                            @else
                                <span class="fw-bold text-muted ps-2">#{{ $u->rank_position }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $u->name }}</div>
                                    <div class="text-muted small">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fw-bold">
                                {{ number_format($u->score) }}
                            </span>
                        </td>
                        <td class="text-center font-monospace">
                            @if($u->movement === 'up')
                                <span class="text-success"><i class="bi bi-caret-up-fill me-1"></i>Up</span>
                            @elseif($u->movement === 'down')
                                <span class="text-danger"><i class="bi bi-caret-down-fill me-1"></i>Down</span>
                            @elseif($u->movement === 'none')
                                <span class="text-secondary"><i class="bi bi-dash me-1"></i>Steady</span>
                            @else
                                <span class="text-info"><i class="bi bi-star-fill me-1"></i>New</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($u->is_hidden)
                                <span class="badge bg-danger-subtle text-danger px-2"><i class="bi bi-eye-slash-fill me-1"></i>{{ __('leaderboard.status.hidden') }}</span>
                            @elseif($u->is_anonymous)
                                <span class="badge bg-warning-subtle text-warning px-2"><i class="bi bi-person-fill-slash me-1"></i>{{ __('leaderboard.status.anonymous') }}</span>
                            @else
                                <span class="badge bg-success-subtle text-success px-2"><i class="bi bi-globe me-1"></i>{{ __('leaderboard.status.public') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="quran-table-empty py-5 text-center">
                                <i class="bi bi-people d-block fs-1 text-muted mb-3"></i>
                                <h6 class="text-muted fw-semibold">No participants found on this leaderboard period.</h6>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong> entries
                </div>
                <div class="quran-pagination">
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
