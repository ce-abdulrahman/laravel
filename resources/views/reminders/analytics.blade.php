@extends('layouts.app')
@section('title', __('reminders.titles.analytics'))
@section('page-title', __('reminders.titles.analytics'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reminders.index') }}">{{ __('reminders.titles.index') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('reminders.titles.analytics') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">📊 {{ __('reminders.titles.analytics') }}</h1>
            <div class="text-muted small">{{ __('reminders.hints.analytics') }}</div>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('reminders.analytics') }}" id="periodForm">
                <select name="days" class="quran-form-control" onchange="document.getElementById('periodForm').submit();" style="width:auto; min-width: 150px;">
                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>{{ __('reminders.analytics.days_7') }}</option>
                    <option value="14" {{ $days == 14 ? 'selected' : '' }}>{{ __('reminders.analytics.days_14') }}</option>
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>{{ __('reminders.analytics.days_30') }}</option>
                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>{{ __('reminders.analytics.days_90') }}</option>
                </select>
            </form>
            <a href="{{ route('reminders.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-card-list"></i> {{ __('reminders.actions.back') }}
            </a>
        </div>
    </div>

    {{-- Stats Cards Grid --}}
    <div class="row g-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#3b82f6,#60a5fa);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('reminders.stats.total_sent') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-send fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($stats['totalSent']) }}</h2>
                    <div class="small mt-2 opacity-75">
                        <span class="fw-semibold">Failed:</span> {{ number_format($stats['totalFailed']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#10b981,#34d399);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('reminders.stats.total_opened') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-envelope-open fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($stats['totalOpened']) }}</h2>
                    <div class="small mt-2 opacity-75">
                        <span class="fw-semibold">Snoozed:</span> {{ number_format($stats['totalSnoozed']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#f59e0b,#fbbf24);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('reminders.stats.open_rate') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-percent fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ $stats['openRate'] }}%</h2>
                    <div class="small mt-2 opacity-75">
                        <span class="fw-semibold">Goal target:</span> > 30%
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg,#6366f1,#818cf8);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('reminders.stats.active_users') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-people fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($stats['activeUsers']) }}</h2>
                    <div class="small mt-2 opacity-75">
                        <span class="fw-semibold">Global subscriptions:</span> {{ number_format($activeReminders) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Line Chart and Extra Stats --}}
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="quran-card p-4">
                <h5 class="fw-semibold mb-4 text-primary d-flex align-items-center gap-2 border-bottom pb-3">
                    <i class="bi bi-graph-up-arrow"></i>
                    {{ __('reminders.analytics.daily_chart') }} ({{ __('reminders.analytics.days_14') }})
                </h5>
                <div style="height: 350px; position: relative;">
                    <canvas id="reminderActivityChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="quran-card p-4 h-100">
                <h5 class="fw-semibold mb-4 text-primary d-flex align-items-center gap-2 border-bottom pb-3">
                    <i class="bi bi-activity"></i>
                    {{ __('reminders.stats.most_effective') }}
                </h5>
                @if($stats['byType'])
                    <div class="text-center py-4">
                        <div class="display-1 mb-3">
                            @php
                                $typeKey = $stats['byType']->notification_type;
                                $icon = match($typeKey) {
                                    'MORNING' => '🌅',
                                    'AFTERNOON' => '☀️',
                                    'EVENING' => '🌆',
                                    'BEFORE_SLEEP' => '🌙',
                                    'DAILY_GOAL' => '🎯',
                                    'STREAK' => '🔥',
                                    'ACHIEVEMENT' => '🏆',
                                    'INACTIVITY' => '💤',
                                    default => '🔔'
                                };
                            @endphp
                            {{ $icon }}
                        </div>
                        <h4 class="fw-bold mb-1">{{ __('reminders.types.' . $typeKey) ?? $typeKey }}</h4>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fs-6 mt-2">
                            {{ round(($stats['byType']->opens / max(1, $stats['byType']->cnt)) * 100, 1) }}% Open Rate
                        </span>
                        <div class="text-muted mt-3 small">
                            Based on <strong>{{ number_format($stats['byType']->cnt) }}</strong> notifications sent with <strong>{{ number_format($stats['byType']->opens) }}</strong> recorded opens.
                        </div>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-bar-chart-fill fs-1 d-block mb-3"></i>
                        <p class="mb-0 fw-medium">No activity logged yet to determine the most effective type.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($stats['chartData']);
    
    const labels = chartData.map(d => d.date);
    const sentCounts = chartData.map(d => d.sent_count);
    const openedCounts = chartData.map(d => d.opened_count);

    new Chart(document.getElementById('reminderActivityChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: '{{ __("reminders.table.sent") }}',
                    data: sentCounts,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: '{{ __("reminders.table.opened") }}',
                    data: openedCounts,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
@endpush
