{{-- resources/views/memorization-reviews/stats.blade.php --}}
@extends('layouts.app')

@section('title', __('memorization_reviews.titles.stats'))
@section('page-title', __('memorization_reviews.titles.stats'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('memorization-reviews.index') }}">{{ __('memorization_reviews.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('memorization_reviews.titles.stats') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('memorization_reviews.titles.stats') }}</h1>
            <div class="text-muted">{{ __('memorization_reviews.hints.my_reviews') }}</div>
        </div>
        <a href="{{ route('memorization-reviews.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('memorization_reviews.actions.back') }}
        </a>
    </div>

    <!-- Summary Stats -->
    @php
        $totalReviews = array_sum($stats['by_result'] ?? []);
        $perfectReviews = $stats['by_result']['perfect'] ?? 0;
        $retentionRate = $totalReviews > 0 ? round((($perfectReviews + ($stats['by_result']['good'] ?? 0)) / $totalReviews) * 100) : 0;
    @endphp
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_reviews.total_reviews') }}</div>
                        <div class="quran-stat-value">{{ number_format($totalReviews) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check2-all"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_reviews.mastered_ayahs') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['mastered_count'] ?? 0) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_reviews.perfect_reviews') }}</div>
                        <div class="quran-stat-value">{{ number_format($perfectReviews) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_reviews.avg_retention') }}</div>
                        <div class="quran-stat-value">{{ $retentionRate }}%</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-brain"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        <!-- Monthly Review Trend -->
        <div class="col-lg-6">
            <div class="quran-card h-100">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-graph-up-arrow me-2"></i>
                        {{ __('memorization_reviews.monthly_trend') }}
                    </h5>
                </div>
                <div class="quran-card-body d-flex align-items-center">
                    <div class="w-100" style="height: 250px; position: relative;">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews by Level -->
        <div class="col-lg-6">
            <div class="quran-card h-100">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-pie-chart-fill me-2"></i>
                        {{ __('memorization_reviews.reviews_by_level') }}
                    </h5>
                </div>
                <div class="quran-card-body d-flex align-items-center">
                    <div class="w-100" style="height: 250px; position: relative;">
                        <canvas id="levelChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Reviews by Result -->
        <div class="col-lg-6">
            <div class="quran-card h-100">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-bar-chart-steps me-2"></i>
                        {{ __('memorization_reviews.reviews_by_result') }}
                    </h5>
                </div>
                <div class="quran-card-body d-flex align-items-center">
                    <div class="w-100" style="height: 250px; position: relative;">
                        <canvas id="resultChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Retention Rate by Level -->
        <div class="col-lg-6">
            <div class="quran-card h-100">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-shield-check me-2"></i>
                        {{ __('memorization_reviews.retention_rate_by_level') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="d-flex flex-column gap-4 py-2">
                        @php
                            $levelLabels = [
                                'new' => __('memorization_reviews.levels.new'),
                                'learning' => __('memorization_reviews.levels.learning'),
                                'reviewing' => __('memorization_reviews.levels.reviewing'),
                                'mastered' => __('memorization_reviews.levels.mastered')
                            ];
                            $colors = [
                                'new' => 'bg-info',
                                'learning' => 'bg-warning',
                                'reviewing' => 'bg-primary',
                                'mastered' => 'bg-success'
                            ];
                        @endphp
                        @foreach(['new', 'learning', 'reviewing', 'mastered'] as $level)
                            @php
                                $rate = $stats['retention_rate'][$level] ?? 0;
                            @endphp
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-capitalize">{{ $levelLabels[$level] ?? $level }}</span>
                                    <span class="badge {{ $colors[$level] }}">{{ $rate }}%</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px; background-color: rgba(0,0,0,0.05);">
                                    <div class="progress-bar {{ $colors[$level] }}" role="progressbar" 
                                         style="width: {{ $rate }}%; border-radius: 4px;" aria-valuenow="{{ $rate }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Surahs Table -->
    <div class="quran-card mt-4">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-trophy-fill me-2 text-warning"></i>
                {{ __('memorization_reviews.top_surahs') }}
            </h5>
        </div>
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th>{{ __('memorization_reviews.fields.surah_ayah') }}</th>
                        <th class="text-end">{{ __('memorization_reviews.reviews_count') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['by_surah'] as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span class="fw-bold">{{ $item['name_ar'] }}</span>
                        </td>
                        <td class="text-end">
                            <span class="badge bg-primary rounded-pill px-3">{{ $item['count'] }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            {{ __('memorization_reviews.no_reviews') }}
                        </td>
                    </tr>
                    @endforelse
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
    // 1. Monthly Trend Chart
    const monthlyTrendData = @json($stats['monthly_trend'] ?? []);
    const monthlyLabels = Object.keys(monthlyTrendData);
    const monthlyCounts = Object.values(monthlyTrendData);

    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: '{{ __("memorization_reviews.titles.index") }}',
                data: monthlyCounts,
                borderColor: '#1B7340',
                backgroundColor: 'rgba(27, 115, 64, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#D4AF37',
                pointBorderColor: '#1B7340',
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // 2. Levels Chart
    const byLevelData = @json($stats['by_level'] ?? []);
    const levelLabels = {
        'new': '{{ __("memorization_reviews.levels.new") }}',
        'learning': '{{ __("memorization_reviews.levels.learning") }}',
        'reviewing': '{{ __("memorization_reviews.levels.reviewing") }}',
        'mastered': '{{ __("memorization_reviews.levels.mastered") }}'
    };

    const levelChartLabels = Object.keys(byLevelData).map(k => levelLabels[k] || k);
    const levelChartValues = Object.values(byLevelData);

    new Chart(document.getElementById('levelChart'), {
        type: 'doughnut',
        data: {
            labels: levelChartLabels,
            datasets: [{
                data: levelChartValues,
                backgroundColor: ['#0dcaf0', '#ffc107', '#0d6efd', '#198754'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // 3. Results Chart
    const byResultData = @json($stats['by_result'] ?? []);
    const resultLabels = {
        'perfect': '{{ __("memorization_reviews.results.perfect") }}',
        'good': '{{ __("memorization_reviews.results.good") }}',
        'fair': '{{ __("memorization_reviews.results.fair") }}',
        'needs_work': '{{ __("memorization_reviews.results.needs_work") }}',
        'forgot': '{{ __("memorization_reviews.results.forgot") }}'
    };

    const resultChartLabels = Object.keys(byResultData).map(k => resultLabels[k] || k);
    const resultChartValues = Object.values(byResultData);

    new Chart(document.getElementById('resultChart'), {
        type: 'bar',
        data: {
            labels: resultChartLabels,
            datasets: [{
                label: '{{ __("memorization_reviews.fields.result") }}',
                data: resultChartValues,
                backgroundColor: ['#198754', '#20c997', '#ffc107', '#fd7e14', '#dc3545'],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endpush
