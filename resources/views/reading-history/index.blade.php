{{-- resources/views/reading-history/index.blade.php --}}
@extends('layouts.app')

@section('title', __('reading_history.titles.index'))
@section('page-title', __('reading_history.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('reading_history.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('reading_history.titles.index') }}</h1>
            <div class="text-muted">{{ __('reading_history.hints.my_history') }}</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('reading-history.stats') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-bar-chart me-1"></i>
                {{ __('reading_history.actions.view_stats') }}
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('reading_history.total_reads') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_reads']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-eye"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('reading_history.unique_ayahs') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['unique_ayahs_read']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-journal-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('reading_history.total_time') }}</div>
                        <div class="quran-stat-value">
                            {{ \App\Helpers\TimeHelper::formatSeconds($stats['total_time_spent']) }}
                        </div>
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
                        <div class="quran-stat-label">{{ __('reading_history.today') }}</div>
                        <div class="quran-stat-value">{{ $stats['today_reads'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Read Ayahs -->
    @if($mostRead->count() > 0)
    <div class="quran-card mb-4">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-trophy me-2"></i>
                {{ __('reading_history.most_read') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="row g-3">
                @foreach($mostRead as $index => $item)
                <div class="col-md-4">
                    <a href="{{ route('ayahs.show', $item->ayah) }}" class="text-decoration-none">
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                            <span class="quran-surah-number" style="width: 40px; height: 40px;">
                                {{ $item->ayah->surah->number }}:{{ $item->ayah->ayah_number }}
                            </span>
                            <div>
                                <div class="fw-semibold">{{ $item->ayah->surah->name_ar }}</div>
                                <small class="text-muted">
                                    {{ $item->read_count }} {{ __('reading_history.times_read') }}
                                </small>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Filter -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('reading-history.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('reading_history.filter_by_surah') }}</label>
                        <select name="surah_id" class="quran-form-select">
                            <option value="">{{ __('reading_history.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                {{ $surah->number }}. {{ $surah->name_ar }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="quran-form-label">{{ __('reading_history.date_from') }}</label>
                        <input type="date" name="date_from" class="quran-form-control" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="quran-form-label">{{ __('reading_history.date_to') }}</label>
                        <input type="date" name="date_to" class="quran-form-control" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('reading_history.per_page') }}</label>
                        <select name="per_page" class="quran-form-select">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
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

    <!-- History Table -->
    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th>{{ __('reading_history.fields.surah_ayah') }}</th>
                        <th>{{ __('reading_history.fields.last_read') }}</th>
                        <th>{{ __('reading_history.fields.time_spent') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                    <tr>
                        <td>
                            <a href="{{ route('ayahs.show', $history->ayah) }}" class="text-decoration-none">
                                <span class="fw-semibold">{{ $history->ayah->surah->name_ar }}</span>
                                <span class="text-muted ms-1">({{ $history->ayah->ayah_number }})</span>
                            </a>
                        </td>
                        <td>
                            {{ $history->last_read_at->format('Y-m-d H:i') }}
                            <small class="text-muted d-block">{{ $history->last_read_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            @if($history->seconds_spent > 0)
                            {{ \App\Helpers\TimeHelper::formatSeconds($history->seconds_spent) }}
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="quran-table-actions justify-content-end">
                                <a href="{{ route('ayahs.show', $history->ayah) }}" 
                                   class="quran-table-action-btn view">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="quran-table-empty">
                                <i class="bi bi-clock-history"></i>
                                <h6>{{ __('reading_history.no_history') }}</h6>
                                <p>{{ __('reading_history.no_history_message') }}</p>
                                <a href="{{ route('ayahs.index') }}" class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-book me-1"></i>
                                    {{ __('reading_history.actions.start_reading') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
        <div class="card-footer">
            {{ $histories->links() }}
        </div>
        @endif
    </div>

    <!-- Clear History -->
    @if($histories->count() > 0)
    <div class="quran-card border-danger mt-4">
        <div class="quran-card-header bg-danger bg-opacity-10">
            <h5 class="quran-card-title text-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ __('reading_history.titles.clear_history') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1">{{ __('reading_history.messages.clear_warning_title') }}</h6>
                    <p class="text-muted mb-0">{{ __('reading_history.messages.clear_warning') }}</p>
                </div>
                <button type="button" class="quran-btn quran-btn-danger" onclick="showClearModal()">
                    <i class="bi bi-trash me-1"></i>
                    {{ __('reading_history.actions.clear_all') }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Clear History Modal -->
<div class="modal fade" id="clearModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">{{ __('reading_history.titles.clear_history') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ __('reading_history.messages.clear_confirm') }}
                </div>
                <form id="clearForm" method="POST" action="{{ route('reading-history.clear') }}">
                    @csrf
                    <div class="form-check">
                        <input type="checkbox" name="confirm" id="confirm" class="form-check-input" required>
                        <label class="form-check-label" for="confirm">
                            {{ __('reading_history.messages.i_understand') }}
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">
                    {{ __('common.cancel') }}
                </button>
                <button type="button" class="quran-btn quran-btn-danger" onclick="submitClearForm()">
                    {{ __('reading_history.actions.clear_all') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showClearModal() {
    new bootstrap.Modal(document.getElementById('clearModal')).show();
}

function submitClearForm() {
    document.getElementById('clearForm').submit();
}
</script>
@endpush