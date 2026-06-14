@extends('layouts.app')
@section('title', __('backup.tabs.logs'))
@section('page-title', __('backup.tabs.logs'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.backups.overview') }}">{{ __('backup.admin_title') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('backup.tabs.logs') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">📋 {{ __('backup.tabs.logs') }}</h1>
            <div class="text-muted small">{{ __('backup.logs_subtitle') }}</div>
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
            <a class="nav-link active" href="{{ route('admin.backups.logs') }}">{{ __('backup.tabs.logs') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.backups.settings') }}">{{ __('backup.tabs.settings') }}</a>
        </li>
    </ul>

    {{-- Filter Card --}}
    <div class="quran-card p-4 mb-4">
        <form method="GET" action="{{ route('admin.backups.logs') }}" class="row g-3">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="{{ __('backup.fields.search_placeholder') }}" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">{{ __('backup.fields.all_statuses') }}</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>{{ __('backup.fields.success') }}</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('backup.fields.failed') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('backup.fields.pending') }}</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
    </div>

    {{-- Data Grid --}}
    <div class="quran-card p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('backup.fields.user') }}</th>
                        <th>{{ __('backup.fields.backup_ref') }}</th>
                        <th>{{ __('backup.fields.restore_type') }}</th>
                        <th>{{ __('backup.fields.status') }}</th>
                        <th>{{ __('backup.fields.started_at') }}</th>
                        <th>{{ __('backup.fields.completed_at') }}</th>
                        <th class="pe-4">{{ __('backup.fields.duration') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $l)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $l->user->name ?? 'Guest' }}</div>
                                <span class="text-muted small">{{ $l->user->email ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($l->backup)
                                    <div>#{{ $l->backup->id }}</div>
                                    <small class="text-muted">{{ basename($l->backup->file_name) }}</small>
                                @else
                                    <span class="text-muted">{{ __('backup.fields.local_file') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($l->restore_type === 'cloud')
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">{{ __('backup.fields.cloud_backup') }}</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">{{ __('backup.fields.local_file_restore') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($l->status === 'success')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> {{ __('backup.fields.success') }}</span>
                                @elseif($l->status === 'failed')
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill"><i class="bi bi-exclamation-circle me-1"></i> {{ __('backup.fields.failed') }}</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> {{ __('backup.fields.pending') }}</span>
                                @endif
                            </td>
                            <td>{{ $l->started_at ? $l->started_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                            <td>{{ $l->completed_at ? $l->completed_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                            <td class="pe-4">
                                @if($l->started_at && $l->completed_at)
                                    {{ number_format($l->completed_at->diffInSeconds($l->started_at), 2) }} s
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-clock-history fs-1 d-block mb-3"></i>
                                {{ __('backup.messages.no_logs_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-top">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
