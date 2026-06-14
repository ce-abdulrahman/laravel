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
            <a href="{{ route('admin.sessions.index') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-list-task"></i> {{ __('sessions.tabs.list') }}
            </a>
            <a href="{{ route('admin.sessions.analytics') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-graph-up"></i> {{ __('sessions.tabs.analytics') }}
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="sessionsTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.sessions.overview') }}">{{ __('sessions.tabs.overview') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.sessions.index') }}">{{ __('sessions.tabs.list') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.sessions.analytics') }}">{{ __('sessions.tabs.analytics') }}</a>
        </li>
    </ul>

    {{-- Stats Cards Grid --}}
    <div class="row g-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#6366f1,#818cf8);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('sessions.widgets.total_sessions') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-clock-history fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($totalSessions) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#0ea5e9,#38bdf8);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('sessions.widgets.active_sessions') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-play-circle fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($activeSessions) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#10b981,#34d399);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('sessions.widgets.avg_duration') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-stopwatch fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ (int) round($avgDuration / 60) }} min</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#f43f5e,#fb7185);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('sessions.widgets.total_dhikr') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-heptagon fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($totalDhikr) }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail tables --}}
    <div class="row g-4">
        {{-- Left: Top Dhikrs --}}
        <div class="col-lg-6">
            <div class="quran-card p-4">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">📈 {{ __('sessions.widgets.top_dhikr_types') }}</h5>
                @if($topDhikrs->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('sessions.fields.dhikr') }}</th>
                                    <th class="text-end">{{ __('sessions.fields.sessions') }}</th>
                                    <th class="text-end">{{ __('sessions.fields.count') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topDhikrs as $d)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $d->name }}</td>
                                        <td class="text-end text-muted">{{ number_format($d->sessions_count) }}</td>
                                        <td class="text-end text-primary fw-bold">{{ number_format($d->total_dhikr) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted py-3 text-center">No session activity recorded.</div>
                @endif
            </div>
        </div>

        {{-- Right: Active users --}}
        <div class="col-lg-6">
            <div class="quran-card p-4">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">🔥 {{ __('sessions.widgets.most_active_users') }}</h5>
                @if($mostActiveUsers->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('sessions.fields.user') }}</th>
                                    <th class="text-end">{{ __('sessions.fields.sessions') }}</th>
                                    <th class="text-end">{{ __('sessions.fields.count') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mostActiveUsers as $u)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $u->name }}</div>
                                            <span class="text-muted small">{{ $u->email }}</span>
                                        </td>
                                        <td class="text-end text-muted">{{ number_format($u->sessions_count) }}</td>
                                        <td class="text-end text-primary fw-bold">{{ number_format($u->total_dhikr) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted py-3 text-center">No active users recorded.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
