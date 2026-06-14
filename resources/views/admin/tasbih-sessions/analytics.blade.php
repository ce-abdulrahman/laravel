@extends('layouts.app')
@section('title', __('sessions.title'))
@section('page-title', __('sessions.title'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sessions.index') }}">{{ __('sessions.title') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('sessions.tabs.analytics') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">📊 {{ __('sessions.title') }} - Analytics</h1>
            <div class="text-muted small">Overview of community dhikr intensity, fatigue patterns, and daily trends.</div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="sessionsTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.sessions.overview') }}">{{ __('sessions.tabs.overview') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.sessions.index') }}">{{ __('sessions.tabs.list') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.sessions.analytics') }}">{{ __('sessions.tabs.analytics') }}</a>
        </li>
    </ul>

    {{-- KPI Cards Grid --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="quran-card p-4 h-100">
                <div class="text-muted small fw-semibold">User Participation Rate</div>
                <h2 class="mt-2 text-primary fw-bold">{{ $participationRate }}%</h2>
                <div class="text-muted small mt-2">Percentage of active users who have completed at least one structured session.</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="quran-card p-4 h-100">
                <div class="text-muted small fw-semibold">Dhikr Fatigue Analysis</div>
                <div class="table-responsive mt-2">
                    <table class="table table-borderless table-hover align-middle mb-0 small">
                        <thead>
                            <tr class="text-muted border-bottom">
                                <th>Session Length</th>
                                <th class="text-end">Average Tap Speed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fatigueStats as $f)
                                <tr>
                                    <td class="fw-bold">{{ $f->duration_bucket }}</td>
                                    <td class="text-end text-success fw-bold">{{ round($f->avg_rate, 1) }} taps / min</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Session Duration Trend Chart --}}
    <div class="quran-card p-4 mb-4">
        <h5 class="fw-semibold mb-3 text-dark">📈 Daily Session Volume & Progress (Last 30 Days)</h5>
        <div style="height: 300px; position: relative;">
            <canvas id="durationTrendChart"></canvas>
        </div>
    </div>
</div>

{{-- Load Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dailyData = @json($dailyDurations);

    const labels = dailyData.map(d => d.session_date);
    const dhikrCounts = dailyData.map(d => d.total_dhikr);
    const avgDurations = dailyData.map(d => Math.round(d.avg_duration / 60)); // convert to minutes

    const ctx = document.getElementById('durationTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Dhikr Counts',
                    data: dhikrCounts,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    yAxisID: 'y',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Avg Session Length (min)',
                    data: avgDurations,
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    yAxisID: 'y1',
                    tension: 0.3,
                    borderDash: [5, 5]
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        drawOnChartArea: true
                    },
                    title: {
                        display: true,
                        text: 'Total Dhikr Count'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Minutes'
                    }
                }
            }
        }
    });
});
</script>
@endsection
