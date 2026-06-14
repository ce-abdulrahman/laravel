@extends('layouts.app')

@section('title', __('statistics.admin_dashboard'))

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                {{ __('statistics.admin_dashboard') }}
            </h1>
            <p class="text-muted mb-0 small">{{ __('statistics.admin_dashboard_subtitle') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.statistics.users') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-people me-1"></i>{{ __('statistics.user_analytics') }}
            </a>
            <a href="{{ route('admin.statistics.insights') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-lightbulb me-1"></i>{{ __('statistics.insights') }}
            </a>
            <a href="{{ route('admin.statistics.settings') }}" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-sliders me-1"></i>{{ __('statistics.settings') }}
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">{{ __('statistics.total_users') }}</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalUsers) }}</div>
                            <div class="text-success small">{{ number_format($activeUsers) }} {{ __('statistics.active_7d') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-success bg-opacity-10 p-3">
                            <i class="bi bi-heptagon-fill text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">{{ __('statistics.total_dhikr') }}</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalDhikr) }}</div>
                            <div class="text-muted small">{{ number_format($totalSessions) }} {{ __('statistics.sessions') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                            <i class="bi bi-trophy-fill text-warning fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">{{ __('statistics.total_achievements') }}</div>
                            <div class="fs-4 fw-bold">{{ number_format($totalAchieve) }}</div>
                            <div class="text-muted small">{{ __('statistics.earned_by_users') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-danger bg-opacity-10 p-3">
                            <i class="bi bi-fire text-danger fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">{{ __('statistics.avg_streak') }}</div>
                            <div class="fs-4 fw-bold">{{ round($avgStreak, 1) }}</div>
                            <div class="text-muted small">{{ __('statistics.avg_goal_completion') }} {{ round($avgGoal, 1) }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        {{-- Daily Activity Chart --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="fw-semibold mb-0">{{ __('statistics.daily_activity_chart') }}</h6>
                    <small class="text-muted">{{ __('statistics.last_30_days') }}</small>
                </div>
                <div class="card-body">
                    <canvas id="dailyActivityChart" height="120"></canvas>
                </div>
            </div>
        </div>

        {{-- Productivity Distribution --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="fw-semibold mb-0">{{ __('statistics.productivity_distribution') }}</h6>
                </div>
                <div class="card-body">
                    <canvas id="productivityChart" height="220"></canvas>
                    <div class="mt-3">
                        @foreach(['master'=>'#6f42c1','advanced'=>'#0d6efd','dedicated'=>'#198754','active'=>'#ffc107','beginner'=>'#6c757d'] as $label => $color)
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:{{ $color }}"></span>
                                <small class="text-capitalize">{{ __('statistics.label_' . $label) }}</small>
                            </div>
                            <small class="fw-semibold">{{ $scoreGroups[$label] ?? 0 }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Users + Top Dhikr --}}
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold mb-0">{{ __('statistics.top_active_users') }}</h6>
                    <a href="{{ route('admin.statistics.users') }}" class="btn btn-link btn-sm p-0">{{ __('statistics.view_all') }}</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ __('statistics.user') }}</th>
                                    <th>{{ __('statistics.total_dhikr') }}</th>
                                    <th>{{ __('statistics.streak') }}</th>
                                    <th>{{ __('statistics.score') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topUsers as $stat)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold small">{{ $stat->user?->name ?? '—' }}</div>
                                        <div class="text-muted" style="font-size:11px">{{ $stat->user?->email }}</div>
                                    </td>
                                    <td>{{ number_format($stat->total_dhikr) }}</td>
                                    <td>🔥 {{ $stat->current_streak }}</td>
                                    <td>
                                        <span class="badge rounded-pill"
                                            style="background:{{ ['master'=>'#6f42c1','advanced'=>'#0d6efd','dedicated'=>'#198754','active'=>'#ffc107','beginner'=>'#6c757d'][$stat->productivity_label] ?? '#6c757d' }}">
                                            {{ $stat->productivity_score }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="fw-semibold mb-0">{{ __('statistics.most_popular_dhikr') }}</h6>
                </div>
                <div class="card-body">
                    @php $maxDhikr = $topDhikr->first()?->total ?? 1; @endphp
                    @foreach($topDhikr as $d)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-semibold">{{ $d->name }}</span>
                            <span class="small text-muted">{{ number_format($d->total) }}</span>
                        </div>
                        <div class="progress" style="height:6px">
                            <div class="progress-bar bg-primary" style="width:{{ round(($d->total / $maxDhikr) * 100) }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
// Daily Activity Chart
const activityCtx = document.getElementById('dailyActivityChart');
const activityLabels = @json($dailyActivity->pluck('activity_date'));
const activityData   = @json($dailyActivity->pluck('total'));
new Chart(activityCtx, {
    type: 'bar',
    data: {
        labels: activityLabels,
        datasets: [{
            label: '{{ __("statistics.dhikr_count") }}',
            data: activityData,
            backgroundColor: 'rgba(13, 110, 253, 0.7)',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { maxTicksLimit: 10 }, grid: { display: false } },
            y: { beginAtZero: true }
        }
    }
});

// Productivity Doughnut
const prodCtx = document.getElementById('productivityChart');
const prodGroups = @json($scoreGroups);
const labels = ['master','advanced','dedicated','active','beginner'];
const colors = ['#6f42c1','#0d6efd','#198754','#ffc107','#6c757d'];
new Chart(prodCtx, {
    type: 'doughnut',
    data: {
        labels: labels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
        datasets: [{
            data: labels.map(l => prodGroups[l] ?? 0),
            backgroundColor: colors,
            borderWidth: 0,
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, cutout: '65%' }
});
</script>
@endpush
