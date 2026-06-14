@extends('layouts.app')
@section('title', __('statistics.settings'))
@section('content')
<div class="container-fluid px-4 py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                <i class="bi bi-sliders text-primary me-2"></i>{{ __('statistics.settings') }}
            </h1>
            <p class="text-muted mb-0 small">{{ __('statistics.settings_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.statistics.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>{{ __('statistics.back') }}
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.statistics.settings.save') }}">
        @csrf

        {{-- Productivity Score Weights --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-speedometer2 me-2 text-primary"></i>{{ __('statistics.productivity_score_weights') }}
                </h6>
                <small class="text-muted">{{ __('statistics.weights_sum_hint') }}</small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach([
                        'streak_weight'      => ['label' => 'statistics.streak_weight',      'icon' => '🔥'],
                        'goal_weight'        => ['label' => 'statistics.goal_weight',        'icon' => '🎯'],
                        'session_weight'     => ['label' => 'statistics.session_weight',     'icon' => '📈'],
                        'achievement_weight' => ['label' => 'statistics.achievement_weight', 'icon' => '🏆'],
                    ] as $key => $meta)
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">{{ $meta['icon'] }} {{ __($meta['label']) }}</label>
                        <input type="number" name="{{ $key }}" class="form-control @error($key) is-invalid @enderror"
                            step="0.01" min="0" max="1"
                            value="{{ old($key, $settings[$key]?->value ?? 0.25) }}">
                        @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @endforeach
                </div>
                <div class="alert alert-info mt-3 py-2 mb-0">
                    <small><i class="bi bi-info-circle me-1"></i>{{ __('statistics.weights_info') }}</small>
                </div>
            </div>
        </div>

        {{-- Snapshot Retention --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-archive me-2 text-warning"></i>{{ __('statistics.snapshot_retention') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('statistics.daily_retention_days') }}</label>
                        <input type="number" name="snapshot_daily_retention_days" class="form-control" min="7" max="365"
                            value="{{ old('snapshot_daily_retention_days', $settings['snapshot_daily_retention_days']?->value ?? 90) }}">
                        <small class="text-muted">{{ __('statistics.daily_retention_hint') }}</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('statistics.weekly_retention_days') }}</label>
                        <input type="number" name="snapshot_weekly_retention_days" class="form-control" min="30" max="3650"
                            value="{{ old('snapshot_weekly_retention_days', $settings['snapshot_weekly_retention_days']?->value ?? 730) }}">
                        <small class="text-muted">{{ __('statistics.weekly_retention_hint') }}</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ __('statistics.monthly_retention') }}</label>
                        <input type="text" class="form-control" value="{{ __('statistics.forever') }}" disabled>
                        <small class="text-muted">{{ __('statistics.monthly_retention_hint') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Insights TTL --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-lightbulb me-2 text-success"></i>{{ __('statistics.insight_settings') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('statistics.insights_expire_hours') }}</label>
                    <input type="number" name="insights_expire_hours" class="form-control" min="1" max="168"
                        value="{{ old('insights_expire_hours', $settings['insights_expire_hours']?->value ?? 24) }}">
                    <small class="text-muted">{{ __('statistics.insights_expire_hint') }}</small>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>{{ __('statistics.save_settings') }}
            </button>
        </div>
    </form>
</div>
@endsection
