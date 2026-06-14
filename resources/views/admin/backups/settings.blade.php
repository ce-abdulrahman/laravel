@extends('layouts.app')
@section('title', __('backup.tabs.settings'))
@section('page-title', __('backup.tabs.settings'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.backups.overview') }}">{{ __('backup.admin_title') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('backup.tabs.settings') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">⚙️ {{ __('backup.tabs.settings') }}</h1>
            <div class="text-muted small">{{ __('backup.settings_subtitle') }}</div>
        </div>
        <div>
            <a href="{{ route('admin.backups.overview') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> {{ __('backup.tabs.overview') }}
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="backupTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.backups.overview') }}">{{ __('backup.tabs.overview') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.backups.index') }}">{{ __('backup.tabs.list') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.backups.logs') }}">{{ __('backup.tabs.logs') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.backups.settings') }}">{{ __('backup.tabs.settings') }}</a>
        </li>
    </ul>

    {{-- Configuration Card --}}
    <div class="quran-card p-5" style="max-width: 800px; margin: 0 auto;">
        <h5 class="fw-semibold mb-4 text-primary border-bottom pb-2">💾 {{ __('backup.widgets.system_backup_settings') }}</h5>
        
        <form method="POST" action="{{ route('admin.backups.settings.save') }}">
            @csrf
            
            <div class="row g-4">
                {{-- Max auto backups --}}
                <div class="col-md-6">
                    <label for="max_count" class="form-label fw-semibold text-dark">{{ __('backup.fields.max_count') }}</label>
                    <input type="number" id="max_count" name="max_count" class="form-control" min="1" max="100" value="{{ old('max_count', $settings['max_count']) }}" required>
                    <div class="form-text text-muted">{{ __('backup.hints.max_count') }}</div>
                </div>

                {{-- Retention Period --}}
                <div class="col-md-6">
                    <label for="retention_days" class="form-label fw-semibold text-dark">{{ __('backup.fields.retention_days') }}</label>
                    <input type="number" id="retention_days" name="retention_days" class="form-control" min="1" max="365" value="{{ old('retention_days', $settings['retention_days']) }}" required>
                    <div class="form-text text-muted">{{ __('backup.hints.retention_days') }}</div>
                </div>

                {{-- Storage Provider --}}
                <div class="col-md-6">
                    <label for="storage_provider" class="form-label fw-semibold text-dark">{{ __('backup.fields.storage_provider') }}</label>
                    <select id="storage_provider" name="storage_provider" class="form-select">
                        <option value="local" {{ old('storage_provider', $settings['storage_provider']) === 'local' ? 'selected' : '' }}>Local (Storage Disk)</option>
                        <option value="s3" {{ old('storage_provider', $settings['storage_provider']) === 's3' ? 'selected' : '' }}>Amazon S3 / DigitalOcean Spaces</option>
                    </select>
                    <div class="form-text text-muted">{{ __('backup.hints.storage_provider') }}</div>
                </div>

                {{-- Encryption Mandate --}}
                <div class="col-md-6">
                    <label for="encryption_required" class="form-label fw-semibold text-dark d-block">{{ __('backup.fields.encryption_policy') }}</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="encryption_required" name="encryption_required" value="1" {{ old('encryption_required', $settings['encryption_required']) ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="encryption_required">{{ __('backup.fields.require_encryption') }}</label>
                    </div>
                    <div class="form-text text-muted">{{ __('backup.hints.encryption_policy') }}</div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 rounded-3 shadow-sm">
                        <i class="bi bi-save"></i> {{ __('backup.actions.save_settings') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
