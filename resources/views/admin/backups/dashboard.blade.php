@extends('layouts.app')
@section('title', __('backup.admin_title'))
@section('page-title', __('backup.admin_title'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('backup.admin_title') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">💾 {{ __('backup.admin_title') }}</h1>
            <div class="text-muted small">{{ __('backup.admin_subtitle') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.backups.index') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-list-task"></i> {{ __('backup.tabs.list') }}
            </a>
            <a href="{{ route('admin.backups.settings') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-gear"></i> {{ __('backup.tabs.settings') }}
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="backupTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.backups.overview') }}">{{ __('backup.tabs.overview') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.backups.index') }}">{{ __('backup.tabs.list') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.backups.logs') }}">{{ __('backup.tabs.logs') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.backups.settings') }}">{{ __('backup.tabs.settings') }}</a>
        </li>
    </ul>

    {{-- Stats Cards Grid --}}
    <div class="row g-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #6366f1, #818cf8);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('backup.widgets.total_backups') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-archive fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($stats['total_count'] ?? 0) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #10b981, #34d399);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('backup.widgets.successful') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-check-circle fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($stats['success_count'] ?? 0) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #f43f5e, #fb7185);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('backup.widgets.failed') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-x-circle fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($stats['failed_count'] ?? 0) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #0ea5e9, #38bdf8);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold opacity-75">{{ __('backup.widgets.storage_usage') }}</span>
                        <div class="rounded-circle p-2 bg-white bg-opacity-20"><i class="bi bi-hdd-network fs-5"></i></div>
                    </div>
                    <h2 class="display-6 fw-bold mb-0" style="font-size: 1.8rem; margin-top: 5px;">
                        @php
                            $size = $stats['total_size'] ?? 0;
                            if ($size >= 1073741824) {
                                echo number_format($size / 1073741824, 2) . ' GB';
                            } elseif ($size >= 1048576) {
                                echo number_format($size / 1048576, 2) . ' MB';
                            } else {
                                echo number_format($size / 1024, 2) . ' KB';
                            }
                        @endphp
                    </h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="quran-card p-4 h-100">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">📈 {{ __('backup.widgets.growth_chart') }}</h5>
                <canvas id="growthChart" height="250"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="quran-card p-4 h-100">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">🔄 {{ __('backup.widgets.restore_frequency') }}</h5>
                <canvas id="restoresChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="quran-card p-4 h-100">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">💾 {{ __('backup.widgets.storage_usage_trend') }}</h5>
                <canvas id="storageChart" height="250"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="quran-card p-4 h-100">
                <h5 class="fw-semibold mb-3 text-primary border-bottom pb-2">🎯 {{ __('backup.widgets.success_rate') }}</h5>
                <div class="d-flex justify-content-center align-items-center" style="height: 220px;">
                    <canvas id="successRateChart" style="max-height: 200px; max-width: 200px;"></canvas>
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
        const stats = @json($stats);
        const growthData = stats.growth || [];
        const restoreData = stats.restores || [];

        const labels = growthData.map(d => d.date);
        const counts = growthData.map(d => d.count);
        const sizes = growthData.map(d => (d.size / 1048576).toFixed(2)); // in MB

        const restoreLabels = restoreData.map(d => d.date);
        const restoreCounts = restoreData.map(d => d.count);

        // Growth Chart
        new Chart(document.getElementById('growthChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '{{ __('backup.widgets.backups_created') }}',
                    data: counts,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Restores Chart
        new Chart(document.getElementById('restoresChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: restoreLabels,
                datasets: [{
                    label: '{{ __('backup.widgets.restores_triggered') }}',
                    data: restoreCounts,
                    backgroundColor: '#10b981',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Storage Chart
        new Chart(document.getElementById('storageChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '{{ __('backup.widgets.storage_mb') }}',
                    data: sizes,
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Success Rate Chart (Doughnut)
        const successCount = stats.success_count || 0;
        const failedCount = stats.failed_count || 0;
        new Chart(document.getElementById('successRateChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['{{ __('backup.widgets.successful') }}', '{{ __('backup.widgets.failed') }}'],
                datasets: [{
                    data: [successCount, failedCount],
                    backgroundColor: ['#10b981', '#f43f5e'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@endpush
