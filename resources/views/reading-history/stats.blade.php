{{-- resources/views/reading-history/stats.blade.php --}}
@extends('layouts.app')

@section('title', __('reading_history.titles.stats'))
@section('page-title', __('reading_history.titles.stats'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('reading-history.index') }}">{{ __('reading_history.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('reading_history.titles.stats') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('reading_history.titles.stats') }}</h1>
            <div class="text-muted">{{ __('reading_history.hints.stats_description') }}</div>
        </div>
        <a href="{{ route('reading-history.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('reading_history.actions.back') }}
        </a>
    </div>

    <!-- Summary Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('reading_history.current_streak') }}</div>
                        <div class="quran-stat-value">{{ $stats['summary']['current_streak'] }}</div>
                        <div class="quran-stat-sub">{{ __('reading_history.days') }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-fire"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('reading_history.longest_streak') }}</div>
                        <div class="quran-stat-value">{{ $stats['summary']['longest_streak'] }}</div>
                        <div class="quran-stat-sub">{{ __('reading_history.days') }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-trophy"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('reading_history.total_time') }}</div>
                        <div class="quran-stat-value">{{ $stats['summary']['total_time_formatted'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('reading_history.total_reads') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['summary']['total_reads']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-eye"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4">
        <!-- Daily Activity -->
        <div class="col-lg-6">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-graph-up me-2"></i>
                        {{ __('reading_history.daily_activity') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <canvas id="dailyChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- By Surah -->
        <div class="col-lg-6">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-pie-chart me-2"></i>
                        {{ __('reading_history.by_surah') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <canvas id="surahChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Surahs Table -->
    <div class="quran-card mt-4">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-list-ol me-2"></i>
                {{ __('reading_history.top_surahs') }}
            </h5>
        </div>
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('reading_history.fields.surah') }}</th>
                        <th>{{ __('reading_history.fields.read_count') }}</th>
                        <th>{{ __('reading_history.fields.time_spent') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['by_surah'] as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['name_ar'] }}</td>
                        <td>{{ $item['count'] }}</td>
                        <td>{{ \App\Helpers\TimeHelper::formatSeconds($item['time']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Chart
    const dailyData = @json($stats['daily']);
    const dailyLabels = dailyData.map(d => d.date);
    const dailyCounts = dailyData.map(d => d.count);
    
    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: '{{ __("reading_history.ayahs_read") }}',
                data: dailyCounts,
                backgroundColor: '#1B7340',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Surah Chart
    const surahData = @json($stats['by_surah']);
    const surahLabels = surahData.slice(0, 10).map(s => s.name_ar);
    const surahCounts = surahData.slice(0, 10).map(s => s.count);
    
    new Chart(document.getElementById('surahChart'), {
        type: 'doughnut',
        data: {
            labels: surahLabels,
            datasets: [{
                data: surahCounts,
                backgroundColor: [
                    '#1B7340', '#2A9D5C', '#10B981', '#34D399',
                    '#D4AF37', '#F4D03F', '#F59E0B', '#FBBF24',
                    '#3B82F6', '#60A5FA'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
});
</script>
@endpush