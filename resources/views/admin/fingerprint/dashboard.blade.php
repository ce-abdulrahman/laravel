@extends('layouts.app')
@section('title', __('fingerprint.admin.title'))
@section('page-title', __('fingerprint.admin.title'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('fingerprint.admin.title') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">👆 {{ __('fingerprint.admin.title') }}</h1>
            <div class="text-muted small">{{ __('fingerprint.admin.subtitle') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.fingerprint.users') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-people"></i> {{ __('fingerprint.admin.tabs.users') }}
            </a>
            <a href="{{ route('admin.fingerprint.settings') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-gear"></i> {{ __('fingerprint.admin.tabs.settings') }}
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="fingerprintTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.fingerprint.dashboard') }}">{{ __('fingerprint.admin.tabs.dashboard') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.fingerprint.users') }}">{{ __('fingerprint.admin.tabs.users') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.fingerprint.settings') }}">{{ __('fingerprint.admin.tabs.settings') }}</a>
        </li>
    </ul>

    {{-- Stats Cards Grid --}}
    <div class="row g-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('fingerprint.admin.widgets.active_users') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-people fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($totalUsers) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('fingerprint.admin.widgets.total_sessions') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-clock-history fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($totalSessions) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #10b981, #34d399);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('fingerprint.admin.widgets.avg_duration') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-stopwatch fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ (int) round($avgDuration) }}s</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #ec4899, #f472b6);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('fingerprint.admin.widgets.preferred_haptic') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-phone-vibrate fs-5"></i></div>
                    </div>
                    <h2 class="h3 fw-bold mb-0 text-capitalize">{{ $mostUsedHaptic }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts and Mode Distributions --}}
    <div class="row g-4">
        {{-- Mode Distribution --}}
        <div class="col-lg-4">
            <div class="quran-card p-4 h-100">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">📊 {{ __('fingerprint.admin.widgets.mode_distribution') }}</h5>
                @if($modeDistribution->isNotEmpty())
                    <div class="d-flex flex-column gap-3">
                        @php $totalModes = $modeDistribution->sum('count'); @endphp
                        @foreach($modeDistribution as $mode)
                            @php $percent = $totalModes > 0 ? ($mode->count / $totalModes) * 100 : 0; @endphp
                            <div>
                                <div class="d-flex justify-content-between mb-1 small">
                                    <span class="fw-bold text-dark text-capitalize">{{ str_replace('_', ' ', $mode->count_mode) }}</span>
                                    <span class="text-muted">{{ number_format($mode->count) }} ({{ round($percent, 1) }}%)</span>
                                </div>
                                <div class="progress rounded-pill" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted py-5 text-center">No configurations saved.</div>
                @endif
            </div>
        </div>

        {{-- Session Trends --}}
        <div class="col-lg-8">
            <div class="quran-card p-4 h-100">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">📈 {{ __('fingerprint.admin.widgets.recent_trends') }}</h5>
                @if($usageGrowth->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('fingerprint.admin.fields.date') }}</th>
                                    <th class="text-end">{{ __('fingerprint.admin.fields.sessions') }}</th>
                                    <th class="text-end">{{ __('fingerprint.admin.fields.taps') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usageGrowth as $day)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($day->session_date)->format('M d, Y') }}</td>
                                        <td class="text-end text-muted">{{ number_format($day->count) }}</td>
                                        <td class="text-end text-primary fw-bold">{{ number_format($day->total_count) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted py-5 text-center">No recent session data available.</div>
                @endif
            </div>
        </div>

        {{-- Hourly distribution --}}
        <div class="col-12">
            <div class="quran-card p-4">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">⏰ {{ __('fingerprint.admin.widgets.hourly_distribution') }}</h5>
                @if($activeHours->isNotEmpty())
                    <div class="d-flex align-items-end justify-content-between pt-4 px-2 overflow-x-auto" style="height: 180px; min-width: 600px;">
                        @php $maxHourCount = $activeHours->max('count') ?: 1; @endphp
                        @for($h = 0; $h < 24; $h++)
                            @php
                                $hourStr = sprintf('%02d', $h);
                                $item = $activeHours->firstWhere('hour', $hourStr);
                                $count = $item ? $item->count : 0;
                                $pct = ($count / $maxHourCount) * 100;
                            @endphp
                            <div class="d-flex flex-column align-items-center flex-grow-1" style="height: 100%;">
                                <div class="text-primary small fw-bold mb-1" style="font-size: 10px;">{{ $count > 0 ? number_format($count) : '' }}</div>
                                <div class="bg-primary bg-opacity-75 rounded-top-2 w-50 transition-all" style="height: calc({{ $pct }}% - 25px); min-height: 4px;" title="{{ $hourStr }}:00 - {{ $count }} sessions"></div>
                                <div class="text-muted mt-2" style="font-size: 11px;">{{ $hourStr }}</div>
                            </div>
                        @endfor
                    </div>
                @else
                    <div class="text-muted py-5 text-center">No hourly data available.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
