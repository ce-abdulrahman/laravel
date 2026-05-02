{{-- resources/views/user-ayah-progress/index.blade.php --}}
@extends('layouts.app')

@section('title', __('user_ayah_progress.titles.index'))
@section('page-title', __('user_ayah_progress.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('user_ayah_progress.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('user_ayah_progress.titles.index') }}</h1>
            <div class="text-muted">{{ __('user_ayah_progress.hints.my_progress') }}</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('user-ayah-progress.dashboard') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-bar-chart me-1"></i>
                {{ __('user_ayah_progress.actions.view_dashboard') }}
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('user_ayah_progress.completion') }}</div>
                        <div class="quran-stat-value">{{ $stats['completion_percentage'] }}%</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-pie-chart"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('user_ayah_progress.memorized') }}</div>
                        <div class="quran-stat-value">{{ $stats['memorized_count'] }}</div>
                        <div class="quran-stat-sub">/ {{ $stats['total_ayahs'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('user_ayah_progress.needs_review') }}</div>
                        <div class="quran-stat-value">{{ $stats['needs_review_count'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('user_ayah_progress.avg_strength') }}</div>
                        <div class="quran-stat-value">{{ round($stats['avg_strength']) }}%</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-lightning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('user-ayah-progress.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('user_ayah_progress.filter_by_surah') }}</label>
                        <select name="surah_id" class="quran-form-select">
                            <option value="">{{ __('user_ayah_progress.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                {{ $surah->number }}. {{ $surah->name_ar }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('user_ayah_progress.filter_by_status') }}</label>
                        <select name="memorize_status" class="quran-form-select">
                            <option value="">{{ __('user_ayah_progress.all_statuses') }}</option>
                            @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('memorize_status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('user_ayah_progress.sort_by') }}</label>
                        <select name="sort" class="quran-form-select">
                            <option value="">{{ __('user_ayah_progress.default') }}</option>
                            <option value="strength_asc" {{ request('sort') == 'strength_asc' ? 'selected' : '' }}>
                                {{ __('user_ayah_progress.strength_low_to_high') }}
                            </option>
                            <option value="strength_desc" {{ request('sort') == 'strength_desc' ? 'selected' : '' }}>
                                {{ __('user_ayah_progress.strength_high_to_low') }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="quran-btn quran-btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Progress Table -->
    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th>{{ __('user_ayah_progress.fields.surah_ayah') }}</th>
                        <th>{{ __('user_ayah_progress.fields.status') }}</th>
                        <th>{{ __('user_ayah_progress.fields.strength') }}</th>
                        <th>{{ __('user_ayah_progress.fields.mistakes') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($progresses as $progress)
                    <tr>
                        <td>
                            <a href="{{ route('ayahs.show', $progress->ayah) }}" class="text-decoration-none">
                                {{ $progress->ayah->surah->name_ar }} ({{ $progress->ayah->ayah_number }})
                            </a>
                        </td>
                        <td>
                            <span class="quran-table-badge status-{{ $progress->memorize_status }}">
                                {{ $statuses[$progress->memorize_status] ?? $progress->memorize_status }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-{{ $progress->strength_score >= 70 ? 'success' : ($progress->strength_score >= 40 ? 'warning' : 'danger') }}" 
                                         style="width: {{ $progress->strength_score }}%"></div>
                                </div>
                                <span>{{ $progress->strength_score }}%</span>
                            </div>
                        </td>
                        <td>
                            {{ $progress->mistakes_count }}
                        </td>
                        <td>
                            <div class="quran-table-actions justify-content-end">
                                <a href="{{ route('user-ayah-progress.show', $progress) }}" 
                                   class="quran-table-action-btn view">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('user-ayah-progress.edit', $progress) }}" 
                                   class="quran-table-action-btn edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="quran-table-empty">
                                <i class="bi bi-journal-check"></i>
                                <h6>{{ __('user_ayah_progress.no_progress') }}</h6>
                                <p>{{ __('user_ayah_progress.no_progress_message') }}</p>
                                <a href="{{ route('ayahs.index') }}" class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-book me-1"></i>
                                    {{ __('user_ayah_progress.actions.start_learning') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($progresses->hasPages())
        <div class="card-footer">
            {{ $progresses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.status-mastered { background: #10B981; color: white; }
.status-memorized { background: #3B82F6; color: white; }
.status-memorizing { background: #F59E0B; color: white; }
.status-needs_review { background: #EF4444; color: white; }
.status-not_started { background: #6B7280; color: white; }
</style>
@endpush