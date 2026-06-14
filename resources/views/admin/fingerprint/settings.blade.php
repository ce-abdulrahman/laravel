@extends('layouts.app')
@section('title', __('fingerprint.admin.settings.title'))
@section('page-title', __('fingerprint.admin.settings.title'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.fingerprint.dashboard') }}">{{ __('fingerprint.admin.title') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('fingerprint.admin.tabs.settings') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">⚙️ {{ __('fingerprint.admin.settings.title') }}</h1>
            <div class="text-muted small">{{ __('fingerprint.admin.settings.subtitle') }}</div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="fingerprintTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.fingerprint.dashboard') }}">{{ __('fingerprint.admin.tabs.dashboard') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.fingerprint.users') }}">{{ __('fingerprint.admin.tabs.users') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.fingerprint.settings') }}">{{ __('fingerprint.admin.tabs.settings') }}</a>
        </li>
    </ul>

    {{-- Alert Success --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- Settings Form --}}
    <div class="row">
        <div class="col-lg-6">
            <form action="{{ route('admin.fingerprint.settings.save') }}" method="POST" class="quran-card p-4">
                @csrf
                
                <h5 class="fw-semibold mb-4 text-primary border-bottom pb-2">🛡️ {{ __('fingerprint.admin.settings.security_limits') }}</h5>
                
                {{-- Maximum Hold Duration --}}
                <div class="mb-4">
                    <label for="fingerprint_max_hold_duration" class="form-label fw-bold text-dark mb-1">
                        {{ __('fingerprint.admin.settings.fields.max_hold_duration') }}
                    </label>
                    <div class="text-muted small mb-2">{{ __('fingerprint.admin.settings.fields.max_hold_duration_help') }}</div>
                    <div class="input-group">
                        <input type="number" name="fingerprint_max_hold_duration" id="fingerprint_max_hold_duration" value="{{ old('fingerprint_max_hold_duration', $maxHoldDuration) }}" class="form-control border-0 bg-light @error('fingerprint_max_hold_duration') is-invalid @enderror" min="1" required>
                        <span class="input-group-text bg-light border-0 text-muted">{{ __('fingerprint.admin.settings.units.seconds') }}</span>
                    </div>
                    @error('fingerprint_max_hold_duration')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Maximum Continuous Count Rate --}}
                <div class="mb-4">
                    <label for="fingerprint_max_continuous_rate" class="form-label fw-bold text-dark mb-1">
                        {{ __('fingerprint.admin.settings.fields.max_continuous_rate') }}
                    </label>
                    <div class="text-muted small mb-2">{{ __('fingerprint.admin.settings.fields.max_continuous_rate_help') }}</div>
                    <div class="input-group">
                        <input type="number" name="fingerprint_max_continuous_rate" id="fingerprint_max_continuous_rate" value="{{ old('fingerprint_max_continuous_rate', $maxContinuousRate) }}" class="form-control border-0 bg-light @error('fingerprint_max_continuous_rate') is-invalid @enderror" min="1" required>
                        <span class="input-group-text bg-light border-0 text-muted">{{ __('fingerprint.admin.settings.units.taps_per_sec') }}</span>
                    </div>
                    @error('fingerprint_max_continuous_rate')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <h5 class="fw-semibold mb-4 mt-5 text-primary border-bottom pb-2">🔧 {{ __('fingerprint.admin.settings.feature_toggles') }}</h5>

                {{-- Enable Fingerprint Mode --}}
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" name="fingerprint_mode_enabled" id="fingerprint_mode_enabled" {{ old('fingerprint_mode_enabled', $modeEnabled) == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="fingerprint_mode_enabled">
                        {{ __('fingerprint.admin.settings.toggles.enable_mode') }}
                    </label>
                    <div class="text-muted small">{{ __('fingerprint.admin.settings.toggles.enable_mode_help') }}</div>
                </div>

                {{-- Enable Blind Mode --}}
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" name="fingerprint_blind_mode_enabled" id="fingerprint_blind_mode_enabled" {{ old('fingerprint_blind_mode_enabled', $blindEnabled) == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="fingerprint_blind_mode_enabled">
                        {{ __('fingerprint.admin.settings.toggles.enable_blind') }}
                    </label>
                    <div class="text-muted small">{{ __('fingerprint.admin.settings.toggles.enable_blind_help') }}</div>
                </div>

                {{-- Enable Focus Mode --}}
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" name="fingerprint_focus_mode_enabled" id="fingerprint_focus_mode_enabled" {{ old('fingerprint_focus_mode_enabled', $focusEnabled) == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="fingerprint_focus_mode_enabled">
                        {{ __('fingerprint.admin.settings.toggles.enable_focus') }}
                    </label>
                    <div class="text-muted small">{{ __('fingerprint.admin.settings.toggles.enable_focus_help') }}</div>
                </div>

                {{-- Submit Button --}}
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                        <i class="bi bi-save me-2"></i> {{ __('fingerprint.admin.settings.buttons.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
