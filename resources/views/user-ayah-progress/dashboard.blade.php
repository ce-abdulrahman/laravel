{{-- resources/views/user-ayah-progress/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', __('user_ayah_progress.titles.dashboard'))
@section('page-title', __('user_ayah_progress.titles.dashboard'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('user-ayah-progress.index') }}">{{ __('user_ayah_progress.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('user_ayah_progress.titles.dashboard') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('user_ayah_progress.titles.dashboard') }}</h1>
            <div class="text-muted">{{ __('user_ayah_progress.hints.dashboard_description') }}</div>
        </div>
        <a href="{{ route('user-ayah-progress.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('user_ayah_progress.actions.back') }}
        </a>
    </div>

    <!-- Overall Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('user_ayah_progress.total_progress') }}</div>
                        <div class="quran-stat-value">{{ $stats['completion_percentage'] }}%</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-trophy"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('user_ayah_progress.mastered_ayahs') }}</div>
                        <div class="quran-stat-value">{{ $stats['mastered_count'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-star"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('user_ayah_progress.total_mistakes') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_mistakes'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('user_ayah_progress.memorizing') }}</div>
                        <div class="quran-stat-value">{{ $stats['memorizing_count'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Needs Review -->
        <div class="col-lg-4">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        {{ __('user_ayah_progress.needs_review_title') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    @if($needsReview->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($needsReview as $item)
                        <div class="list-group-item bg-transparent px-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <a href="{{ route('ayahs.show', $item->ayah) }}" class="text-decoration-none">
                                        {{ $item->ayah->surah->name_ar }} ({{ $item->ayah->ayah_number }})
                                    </a>
                                    <div class="d-flex gap-2 mt-1">
                                        <span class="quran-table-badge status-{{ $item->memorize_status }}">
                                            {{ $statuses[$item->memorize_status] }}
                                        </span>
                                        <small class="text-muted">
                                            {{ __('user_ayah_progress.strength') }}: {{ $item->strength_score }}%
                                        </small>
                                    </div>
                                </div>
                                <a href="{{ route('user-ayah-progress.show', $item) }}" 
                                   class="quran-table-action-btn view">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">{{ __('user_ayah_progress.no_needs_review') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Strongest Ayahs -->
        <div class="col-lg-4">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-lightning text-success me-2"></i>
                        {{ __('user_ayah_progress.strongest_ayahs') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    @if($strongest->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($strongest as $item)
                        <div class="list-group-item bg-transparent px-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <a href="{{ route('ayahs.show', $item->ayah) }}" class="text-decoration-none">
                                        {{ $item->ayah->surah->name_ar }} ({{ $item->ayah->ayah_number }})
                                    </a>
                                    <div class="mt-1">
                                        <div class="progress" style="height: 4px; width: 100px;">
                                            <div class="progress-bar bg-success" style="width: {{ $item->strength_score }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="fw-bold text-success">{{ $item->strength_score }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">{{ __('user_ayah_progress.no_strong_ayahs') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Weakest Ayahs -->
        <div class="col-lg-4">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-arrow-down text-danger me-2"></i>
                        {{ __('user_ayah_progress.weakest_ayahs') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    @if($weakest->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($weakest as $item)
                        <div class="list-group-item bg-transparent px-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <a href="{{ route('ayahs.show', $item->ayah) }}" class="text-decoration-none">
                                        {{ $item->ayah->surah->name_ar }} ({{ $item->ayah->ayah_number }})
                                    </a>
                                    <div class="mt-1">
                                        <div class="progress" style="height: 4px; width: 100px;">
                                            <div class="progress-bar bg-danger" style="width: {{ $item->strength_score }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="fw-bold text-danger">{{ $item->strength_score }}%</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">{{ __('user_ayah_progress.no_weak_ayahs') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Progress by Juz -->
    <div class="quran-card mt-4">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-grid-3x3 me-2"></i>
                {{ __('user_ayah_progress.progress_by_juz') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="row g-2">
                @foreach($byJuz as $juz => $data)
                <div class="col-md-2 col-4">
                    <div class="text-center p-2 rounded-3 bg-light">
                        <small class="text-muted">{{ __('user_ayah_progress.juz') }} {{ $juz }}</small>
                        <div class="fw-bold">{{ $data['percentage'] }}%</div>
                        <small>{{ $data['memorized'] }}/{{ $data['total'] }}</small>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar" style="width: {{ $data['percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Progress by Surah -->
    <div class="quran-card mt-4">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-book me-2"></i>
                {{ __('user_ayah_progress.progress_by_surah') }}
            </h5>
        </div>
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('user_ayah_progress.fields.surah') }}</th>
                        <th>{{ __('user_ayah_progress.fields.total') }}</th>
                        <th>{{ __('user_ayah_progress.fields.mastered') }}</th>
                        <th>{{ __('user_ayah_progress.fields.memorized') }}</th>
                        <th>{{ __('user_ayah_progress.fields.memorizing') }}</th>
                        <th>{{ __('user_ayah_progress.fields.needs_review') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bySurah as $surah)
                    <tr>
                        <td>{{ $surah->number }}</td>
                        <td>{{ $surah->name_ar }}</td>
                        <td>{{ $surah->total }}</td>
                        <td>{{ $surah->mastered }}</td>
                        <td>{{ $surah->memorized }}</td>
                        <td>{{ $surah->memorizing }}</td>
                        <td>{{ $surah->needs_review }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection