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
            <a href="{{ route('admin.leaderboard.index') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-list-ol"></i> {{ __('leaderboard.tabs.standings') }}
            </a>
            <a href="{{ route('admin.leaderboard.config') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-gear"></i> {{ __('leaderboard.tabs.config') }}
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="leaderboardTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.leaderboard.overview') }}">{{ __('leaderboard.tabs.overview') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.index') }}">{{ __('leaderboard.tabs.standings') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.config') }}">{{ __('leaderboard.tabs.config') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.analytics') }}">{{ __('leaderboard.tabs.analytics') }}</a>
        </li>
    </ul>

    {{-- Stats Cards Grid --}}
    <div class="row g-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#4f46e5,#6366f1);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('leaderboard.widgets.total_users') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-people fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($totalRankedUsers) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#059669,#10b981);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('leaderboard.widgets.active_participants') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-patch-check fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($activeParticipants) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#ca8a04,#eab308);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('leaderboard.widgets.average_score') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-calculator fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($averageScore, 0) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#e11d48,#f43f5e);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('leaderboard.widgets.top_today') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-award fs-5"></i></div>
                    </div>
                    <h5 class="fw-bold mb-0">{{ $topUserToday?->name ?? 'No entries' }}</h5>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Top Showcase --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="quran-card p-4">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">🥇 {{ __('leaderboard.widgets.top_week') }}</h5>
                @if($topUserWeekly)
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 50px; height: 50px;">
                            {{ strtoupper(substr($topUserWeekly->name, 0, 2)) }}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $topUserWeekly->name }}</h6>
                            <span class="text-muted small">{{ $topUserWeekly->email }}</span>
                        </div>
                    </div>
                @else
                    <div class="text-muted py-3">No active users recorded this week.</div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="quran-card p-4">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">🏆 {{ __('leaderboard.widgets.top_month') }}</h5>
                @if($topUserMonthly)
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 50px; height: 50px;">
                            {{ strtoupper(substr($topUserMonthly->name, 0, 2)) }}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $topUserMonthly->name }}</h6>
                            <span class="text-muted small">{{ $topUserMonthly->email }}</span>
                        </div>
                    </div>
                @else
                    <div class="text-muted py-3">No active users recorded this month.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
