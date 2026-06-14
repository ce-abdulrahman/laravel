@extends('layouts.app')
@section('title', __('backup.tabs.list'))
@section('page-title', __('backup.tabs.list'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.backups.overview') }}">{{ __('backup.admin_title') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('backup.tabs.list') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">📋 {{ __('backup.tabs.list') }}</h1>
            <div class="text-muted small">{{ __('backup.list_subtitle') }}</div>
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
            <a class="nav-link active" href="{{ route('admin.backups.index') }}">{{ __('backup.tabs.list') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.backups.logs') }}">{{ __('backup.tabs.logs') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.backups.settings') }}">{{ __('backup.tabs.settings') }}</a>
        </li>
    </ul>

    {{-- Filter Card --}}
    <div class="quran-card p-4 mb-4">
        <form method="GET" action="{{ route('admin.backups.index') }}" class="row g-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="{{ __('backup.fields.search_placeholder') }}" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">{{ __('backup.fields.all_types') }}</option>
                    <option value="manual" {{ request('type') === 'manual' ? 'selected' : '' }}>{{ __('backup.fields.manual') }}</option>
                    <option value="auto" {{ request('type') === 'auto' ? 'selected' : '' }}>{{ __('backup.fields.auto') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">{{ __('backup.fields.all_statuses') }}</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>{{ __('backup.fields.success') }}</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('backup.fields.failed') }}</option>
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
                        <th>{{ __('backup.fields.created_at') }}</th>
                        <th>{{ __('backup.fields.size') }}</th>
                        <th>{{ __('backup.fields.type') }}</th>
                        <th>{{ __('backup.fields.encryption') }}</th>
                        <th>{{ __('backup.fields.device') }}</th>
                        <th>{{ __('backup.fields.expires_at') }}</th>
                        <th class="text-end pe-4">{{ __('backup.fields.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $b)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $b->user->name ?? 'Guest' }}</div>
                                <span class="text-muted small">{{ $b->user->email ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div>{{ $b->created_at->format('Y-m-d H:i') }}</div>
                                <small class="text-muted">{{ $b->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                @php
                                    $size = $b->file_size;
                                    if ($size >= 1048576) {
                                        echo number_format($size / 1048576, 2) . ' MB';
                                    } else {
                                        echo number_format($size / 1024, 2) . ' KB';
                                    }
                                @endphp
                            </td>
                            <td>
                                @if($b->backup_type === 'auto')
                                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">{{ __('backup.fields.auto') }}</span>
                                @else
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">{{ __('backup.fields.manual') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($b->is_encrypted)
                                    <span class="text-success"><i class="bi bi-shield-lock-fill me-1"></i> {{ __('backup.fields.encrypted') }}</span>
                                @else
                                    <span class="text-muted"><i class="bi bi-shield-slash me-1"></i> {{ __('backup.fields.plain') }}</span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $b->device_type ?? 'Unknown' }}</div>
                                <small class="text-muted">{{ $b->platform ?? '' }} (v{{ $b->app_version ?? '1.0' }})</small>
                            </td>
                            <td>
                                @if($b->expires_at)
                                    <span class="text-warning"><i class="bi bi-clock me-1"></i> {{ $b->expires_at->format('Y-m-d') }}</span>
                                @else
                                    <span class="text-muted">{{ __('backup.fields.never') }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('admin.backups.download', $b->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="{{ __('backup.actions.download') }}">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <form action="{{ route('admin.backups.destroy', $b->id) }}" method="POST" onsubmit="return confirm('{{ __('backup.actions.confirm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="{{ __('backup.actions.delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-archive fs-1 d-block mb-3"></i>
                                {{ __('backup.messages.no_backups_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($backups->hasPages())
            <div class="p-4 border-top">
                {{ $backups->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
