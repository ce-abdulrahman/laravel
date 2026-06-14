@extends('layouts.app')
@section('title', __('sessions.title'))
@section('page-title', __('sessions.title'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('sessions.title') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">🧘 {{ __('sessions.title') }}</h1>
            <div class="text-muted small">{{ __('sessions.subtitle') }}</div>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download"></i> {{ __('sessions.actions.export') }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-menu-item dropdown-item" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}"><i class="bi bi-file-earmark-spreadsheet me-2"></i> Export CSV</a></li>
                    <li><a class="dropdown-menu-item dropdown-item" href="{{ request()->fullUrlWithQuery(['export' => 'json']) }}"><i class="bi bi-filetype-json me-2"></i> Export JSON</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="sessionsTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.sessions.overview') }}">{{ __('sessions.tabs.overview') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.sessions.index') }}">{{ __('sessions.tabs.list') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.sessions.analytics') }}">{{ __('sessions.tabs.analytics') }}</a>
        </li>
    </ul>

    {{-- Filter Form --}}
    <div class="quran-card p-4 mb-4">
        <form method="GET" action="{{ route('admin.sessions.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="q" class="form-label small fw-semibold text-muted">{{ __('sessions.labels.search') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" id="q" class="form-control bg-light border-start-0" placeholder="{{ __('sessions.placeholders.search') }}" value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label small fw-semibold text-muted">{{ __('sessions.labels.status') }}</label>
                <select name="status" id="status" class="form-select bg-light">
                    <option value="">{{ __('sessions.labels.all_statuses') }}</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>{{ __('sessions.status.active') }}</option>
                    <option value="paused" {{ $status === 'paused' ? 'selected' : '' }}>{{ __('sessions.status.paused') }}</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>{{ __('sessions.status.completed') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date" class="form-label small fw-semibold text-muted">{{ __('sessions.labels.date') }}</label>
                <input type="date" name="date" id="date" class="form-control bg-light" value="{{ $date }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">{{ __('sessions.actions.filter') }}</button>
                @if($search || $status || $date)
                    <a href="{{ route('admin.sessions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                @endif
            </div>
        </form>
    </div>

    {{-- Session Standings Grid Table --}}
    <div class="quran-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>{{ __('sessions.fields.user') }}</th>
                        <th>{{ __('sessions.fields.dhikr') }}</th>
                        <th>{{ __('sessions.fields.start_time') }}</th>
                        <th>{{ __('sessions.fields.duration') }}</th>
                        <th class="text-end">{{ __('sessions.fields.count') }}</th>
                        <th class="text-end">{{ __('sessions.fields.rate') }}</th>
                        <th class="text-center">{{ __('sessions.fields.status') }}</th>
                        <th class="pe-4 text-end">{{ __('sessions.fields.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $s)
                        <tr>
                            <td class="ps-4 text-muted">#{{ $s->id }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $s->user?->name ?? 'Unknown' }}</div>
                                <span class="text-muted small">{{ $s->user?->email }}</span>
                            </td>
                            <td>
                                @if($s->dhikr)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $s->dhikr->name }}</span>
                                @elseif($s->custom_dhikr_name)
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $s->custom_dhikr_name }}</span>
                                @else
                                    <span class="text-muted italic">General Counting</span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-dark">{{ $s->start_time->setTimezone('Asia/Baghdad')->format('Y-m-d H:i') }}</div>
                                <span class="text-muted small">Asia/Baghdad</span>
                            </td>
                            <td>
                                @if($s->status === 'active' || $s->status === 'paused')
                                    <span class="text-info"><i class="bi bi-stopwatch"></i> Active...</span>
                                @else
                                    <span class="text-dark">{{ (int) floor($s->duration_seconds / 60) }} min {{ $s->duration_seconds % 60 }} sec</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-dark">{{ number_format($s->total_count) }}</td>
                            <td class="text-end text-muted">
                                @if($s->status === 'completed')
                                    {{ $s->avg_per_minute }} / min
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-center">
                                @if($s->status === 'completed')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Completed</span>
                                @elseif($s->status === 'active')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill animate-pulse">Active</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Paused</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.sessions.show', $s->id) }}" class="btn btn-sm btn-outline-primary" title="View Timeline Scrubber"><i class="bi bi-eye"></i></a>
                                    
                                    @if($s->status !== 'completed')
                                        <form method="POST" action="{{ route('admin.sessions.force-close', $s->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Force Close Session" onclick="return confirm('Force close this orphaned session?')"><i class="bi bi-slash-circle"></i></button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.sessions.destroy', $s->id) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Session" onclick="return confirm('Delete this session? This recalculates aggregates.')"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">No sessions match search parameters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($sessions->hasPages())
            <div class="card-footer p-4 bg-white border-top">
                {{ $sessions->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
