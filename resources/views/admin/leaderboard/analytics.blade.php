@extends('layouts.app')
@section('title', __('leaderboard.title'))
@section('page-title', __('leaderboard.title'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('leaderboard.title') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">📊 {{ __('leaderboard.title') }} - Analytics</h1>
            <div class="text-muted small">Leaderboard usage analytics, growth trends and competitive metrics.</div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="leaderboardTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.overview') }}">{{ __('leaderboard.tabs.overview') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.index') }}">{{ __('leaderboard.tabs.standings') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.leaderboard.config') }}">{{ __('leaderboard.tabs.config') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.leaderboard.analytics') }}">{{ __('leaderboard.tabs.analytics') }}</a>
        </li>
    </ul>

    {{-- Metrics Grid --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="quran-card p-4">
                <h6 class="text-secondary small fw-bold text-uppercase mb-2">{{ __('leaderboard.analytics.density') }}</h6>
                <h3 class="fw-bold text-primary">{{ number_format($densityCount) }} Users</h3>
                <span class="text-muted small">Active users within the top 10% score margin of the leader.</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="quran-card p-4">
                <h6 class="text-secondary small fw-bold text-uppercase mb-2">{{ __('leaderboard.analytics.participation') }}</h6>
                <h3 class="fw-bold text-success">{{ $participationRate }}%</h3>
                <span class="text-muted small">Percentage of active registered users participating in leaderboards.</span>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="quran-card p-4">
                <h5 class="fw-semibold mb-4 text-primary d-flex align-items-center gap-2 border-bottom pb-3">
                    <i class="bi bi-graph-up-arrow"></i>
                    {{ __('leaderboard.analytics.growth') }}
                </h5>
                <div style="height: 350px; position: relative;">
                    <canvas id="dhikrGrowthChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="quran-card p-4 h-100">
                <h5 class="fw-semibold mb-4 text-primary d-flex align-items-center gap-2 border-bottom pb-3">
                    <i class="bi bi-pie-chart"></i>
                    {{ __('leaderboard.analytics.movements') }}
                </h5>
                <div style="height: 300px; position: relative;">
                    <canvas id="movementDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Dhikr growth trend line chart
    const dailyTrends = @json($dailyTrends);
    const growthLabels = dailyTrends.map(d => d.goal_date);
    const growthData = dailyTrends.map(d => d.total_dhikr);

    new Chart(document.getElementById('dhikrGrowthChart'), {
        type: 'line',
        data: {
            labels: growthLabels,
            datasets: [{
                label: 'Dhikr/Tasbih Recited',
                data: growthData,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
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

    // 2. Rank movement distribution pie chart
    const movementStats = @json($movementStats);
    new Chart(document.getElementById('movementDistributionChart'), {
        type: 'doughnut',
        data: {
            labels: [
                '{{ __("leaderboard.analytics.up") }}',
                '{{ __("leaderboard.analytics.down") }}',
                '{{ __("leaderboard.analytics.none") }}',
                '{{ __("leaderboard.analytics.new") }}'
            ],
            datasets: [{
                data: [
                    movementStats.up,
                    movementStats.down,
                    movementStats.none,
                    movementStats.new
                ],
                backgroundColor: ['#10b981', '#ef4444', '#6b7280', '#3b82f6'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>
@endpush
