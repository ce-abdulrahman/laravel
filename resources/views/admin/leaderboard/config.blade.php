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
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="leaderboardTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.overview') }}">{{ __('leaderboard.tabs.overview') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.index') }}">{{ __('leaderboard.tabs.standings') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.leaderboard.config') }}">{{ __('leaderboard.tabs.config') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.analytics') }}">{{ __('leaderboard.tabs.analytics') }}</a>
        </li>
    </ul>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Scoring Weight Panel --}}
        <div class="col-lg-6">
            <div class="quran-card p-4">
                <h5 class="fw-semibold mb-4 text-primary border-bottom pb-3">
                    <i class="bi bi-calculator me-2"></i>{{ __('leaderboard.config.weights') }}
                </h5>
                <form method="POST" action="{{ route('admin.leaderboard.config.save') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('leaderboard.config.dhikr') }}</label>
                        <input type="number" name="dhikr" value="{{ $weights['dhikr'] }}" class="quran-form-control w-100" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('leaderboard.config.daily_goal') }}</label>
                        <input type="number" name="daily_goal" value="{{ $weights['daily_goal'] }}" class="quran-form-control w-100" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('leaderboard.config.achievement') }}</label>
                        <input type="number" name="achievement" value="{{ $weights['achievement'] }}" class="quran-form-control w-100" min="0">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">{{ __('leaderboard.config.streak') }}</label>
                        <input type="number" name="streak" value="{{ $weights['streak'] }}" class="quran-form-control w-100" min="0">
                    </div>

                    <h5 class="fw-semibold mb-4 text-primary border-bottom pb-3">
                        <i class="bi bi-toggles me-2"></i>{{ __('leaderboard.config.types') }}
                    </h5>
                    @foreach($types as $key => $isEnabled)
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="{{ $key }}" id="switch_{{ $key }}" {{ $isEnabled ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium text-capitalize" for="switch_{{ $key }}">
                                {{ $key }} Rankings
                            </label>
                        </div>
                    @endforeach

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                        <button type="submit" class="quran-btn quran-btn-primary px-4">
                            <i class="bi bi-save me-1"></i>{{ __('leaderboard.config.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Explanatory Panel --}}
        <div class="col-lg-6">
            <div class="quran-card p-4 bg-light">
                <h5 class="fw-semibold mb-3"><i class="bi bi-info-circle me-1"></i>How Leaderboard Points Work</h5>
                <p class="text-muted small">
                    The system aggregates multiple sources of data inside the Quran App (Tasbih, daily goals, streaks, and achievements) and applies the weights defined on the left to calculate the user's composite <strong>CUSTOM_SCORING</strong> ranking.
                </p>
                <ul class="small text-muted ps-3">
                    <li class="mb-2"><strong>Dhikr point weight</strong> is applied directly to the total sum of completed tasbih counts.</li>
                    <li class="mb-2"><strong>Daily goal weight</strong> rewards users for checking off all targets on their daily checklist.</li>
                    <li class="mb-2"><strong>Achievement points</strong> are awarded when rare badges are unlocked.</li>
                    <li class="mb-2"><strong>Streak points</strong> incentivize daily consistency.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
