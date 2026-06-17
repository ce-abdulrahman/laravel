@extends('layouts.app')

@section('title', __('prayer_widget.settings.title'))
@section('page-title', __('prayer_widget.settings.title'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.prayer-settings.index') }}">Prayer Settings</a></li>
    <li class="breadcrumb-item active">{{ __('prayer_widget.settings.title') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('prayer_widget.settings.title') }}</h1>
            <div class="text-muted">Configure how the dynamic prayer times widget operates on the mobile application homepage.</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Widget Settings Form -->
        <div class="col-lg-8">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-card-text me-2"></i>
                        {{ __('prayer_widget.settings.title') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <form method="POST" action="{{ route('admin.prayer-widget-settings.update') }}">
                        @csrf
                        
                        <!-- Enabled / Disabled switch -->
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="widget_enabled" id="widget_enabled" value="1" {{ $settings->widget_enabled ? 'checked' : '' }}>
                            <label class="form-check-label quran-form-label mb-0 ms-2" for="widget_enabled">
                                {{ __('prayer_widget.settings.enabled') }}
                            </label>
                        </div>

                        <div class="row g-3">
                            <!-- Visibility -->
                            <div class="col-md-6">
                                <label class="quran-form-label" for="widget_visibility">{{ __('prayer_widget.settings.visibility') }}</label>
                                <select name="widget_visibility" id="widget_visibility" class="quran-form-select">
                                    <option value="always_visible" {{ $settings->widget_visibility == 'always_visible' ? 'selected' : '' }}>
                                        {{ __('prayer_widget.settings.visibility.always') }}
                                    </option>
                                    <option value="only_authenticated" {{ $settings->widget_visibility == 'only_authenticated' ? 'selected' : '' }}>
                                        {{ __('prayer_widget.settings.visibility.auth') }}
                                    </option>
                                </select>
                            </div>

                            <!-- Refresh Interval -->
                            <div class="col-md-6">
                                <label class="quran-form-label" for="widget_refresh_interval">{{ __('prayer_widget.settings.refresh_interval') }}</label>
                                <input type="number" name="widget_refresh_interval" id="widget_refresh_interval" class="quran-form-control" value="{{ $settings->widget_refresh_interval }}" min="60" required>
                                <small class="text-muted">Minimum 60 seconds.</small>
                            </div>

                            <!-- Default City Fallback -->
                            <div class="col-md-6">
                                <label class="quran-form-label" for="widget_default_city_id">{{ __('prayer_widget.settings.default_city') }}</label>
                                <select name="widget_default_city_id" id="widget_default_city_id" class="quran-form-select">
                                    <option value="">-- No City Fallback --</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ $settings->widget_default_city_id == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }} ({{ $city->timezone }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Display Order -->
                            <div class="col-md-6">
                                <label class="quran-form-label" for="widget_display_order">{{ __('prayer_widget.settings.display_order') }}</label>
                                <input type="number" name="widget_display_order" id="widget_display_order" class="quran-form-control" value="{{ $settings->widget_display_order }}" min="0" required>
                            </div>

                            <!-- Hijri Source -->
                            <div class="col-md-6">
                                <label class="quran-form-label" for="hijri_source">{{ __('prayer_widget.settings.hijri_source') }}</label>
                                <select name="hijri_source" id="hijri_source" class="quran-form-select">
                                    <option value="tabular" {{ $settings->hijri_source == 'tabular' ? 'selected' : '' }}>Tabular Islamic Calendar</option>
                                    <option value="umm_al_qura" {{ $settings->hijri_source == 'umm_al_qura' ? 'selected' : '' }}>Umm al-Qura (Makkah astronomical)</option>
                                    <option value="custom" {{ $settings->hijri_source == 'custom' ? 'selected' : '' }}>Custom Offsets</option>
                                </select>
                            </div>
                        </div>

                        <div class="quran-form-actions mt-4">
                            <button type="submit" class="quran-btn quran-btn-primary">
                                <i class="bi bi-save me-1"></i>
                                {{ __('prayer_widget.settings.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="col-lg-4">
            <div class="quran-card mb-4" style="border-left: 4px solid #0d6efd;">
                <div class="quran-card-body">
                    <h5 class="card-title text-primary"><i class="bi bi-info-circle-fill me-2"></i>Dynamic Translations</h5>
                    <p class="card-text text-muted mt-2" style="font-size: 0.9rem;">
                        This settings page leverages our <strong>Dynamic Translation Architecture</strong>. All label translation keys (e.g. <code>prayer_widget.settings.enabled</code>) are discovered automatically by build-time scans.
                    </p>
                    <p class="card-text text-muted" style="font-size: 0.9rem;">
                        You can manage localizations for all active languages in the <a href="{{ route('translations-manager.index') }}">Translations Manager</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
