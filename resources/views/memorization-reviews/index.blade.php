{{-- resources/views/memorization-reviews/index.blade.php --}}
@extends('layouts.app')

@section('title', __('memorization_reviews.titles.index'))
@section('page-title', __('memorization_reviews.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('memorization_reviews.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('memorization_reviews.titles.index') }}</h1>
            <div class="text-muted">{{ __('memorization_reviews.hints.my_reviews') }}</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('memorization-reviews.stats-page') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-bar-chart me-1"></i>
                {{ __('memorization_reviews.actions.view_stats') }}
            </a>
            <a href="{{ route('memorization-reviews.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('memorization_reviews.actions.create') }}
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_reviews.total_reviews') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_reviews']) }}</div>
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
                        <div class="quran-stat-label">{{ __('memorization_reviews.today_reviews') }}</div>
                        <div class="quran-stat-value">{{ $stats['today_reviews'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_reviews.avg_retention') }}</div>
                        <div class="quran-stat-value">{{ $stats['avg_retention'] }}%</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-brain"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_reviews.perfect_reviews') }}</div>
                        <div class="quran-stat-value">{{ $stats['perfect_reviews'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Today's Reviews -->
        <div class="col-lg-4">
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-calendar-check me-2"></i>
                        {{ __('memorization_reviews.today_reviews') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    @if($todayReviews->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($todayReviews as $review)
                        <div class="list-group-item bg-transparent px-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <a href="{{ route('ayahs.show', $review->ayah) }}" class="text-decoration-none">
                                        {{ $review->ayah->surah->name_ar }} ({{ $review->ayah->ayah_number }})
                                    </a>
                                    <span class="quran-table-badge {{ $review->result }} ms-2">
                                        {{ $results[$review->result] ?? $review->result }}
                                    </span>
                                </div>
                                <a href="{{ route('memorization-reviews.show', $review) }}" 
                                   class="quran-table-action-btn view">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">{{ __('memorization_reviews.no_reviews_today') }}</p>
                    <a href="{{ route('memorization-reviews.create') }}" class="quran-btn quran-btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>
                        {{ __('memorization_reviews.actions.add_review') }}
                    </a>
                    @endif
                </div>
            </div>

            <!-- Suggested Reviews -->
            @if($suggestedReviews->count() > 0)
            <div class="quran-card mt-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-lightbulb me-2"></i>
                        {{ __('memorization_reviews.suggested_reviews') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="list-group list-group-flush">
                        @foreach($suggestedReviews->take(5) as $review)
                        <div class="list-group-item bg-transparent px-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    {{ $review->ayah->surah->name_ar }} ({{ $review->ayah->ayah_number }})
                                    <small class="text-muted d-block">
                                        {{ $review->review_date->diffForHumans() }}
                                    </small>
                                </div>
                                <a href="{{ route('memorization-reviews.create', ['ayah_id' => $review->ayah_id]) }}" 
                                   class="quran-btn quran-btn-outline-primary btn-sm">
                                    {{ __('memorization_reviews.actions.review_now') }}
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Reviews List -->
        <div class="col-lg-8">
            <!-- Filter -->
            <div class="quran-card mb-4">
                <div class="quran-card-body">
                    <form method="GET" action="{{ route('memorization-reviews.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="quran-form-label">{{ __('memorization_reviews.filter_by_surah') }}</label>
                                <select name="surah_id" class="quran-form-select">
                                    <option value="">{{ __('memorization_reviews.all_surahs') }}</option>
                                    @foreach($surahs as $surah)
                                    <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                        {{ $surah->number }}. {{ $surah->name_ar }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="quran-form-label">{{ __('memorization_reviews.filter_by_level') }}</label>
                                <select name="review_level" class="quran-form-select">
                                    <option value="">{{ __('memorization_reviews.all_levels') }}</option>
                                    @foreach($reviewLevels as $key => $label)
                                    <option value="{{ $key }}" {{ request('review_level') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="quran-form-label">{{ __('memorization_reviews.filter_by_result') }}</label>
                                <select name="result" class="quran-form-select">
                                    <option value="">{{ __('memorization_reviews.all_results') }}</option>
                                    @foreach($results as $key => $label)
                                    <option value="{{ $key }}" {{ request('result') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="quran-form-label">{{ __('memorization_reviews.date_range') }}</label>
                                <div class="d-flex gap-2">
                                    <input type="date" name="date_from" class="quran-form-control" 
                                           value="{{ request('date_from') }}" placeholder="{{ __('memorization_reviews.from') }}">
                                    <input type="date" name="date_to" class="quran-form-control" 
                                           value="{{ request('date_to') }}" placeholder="{{ __('memorization_reviews.to') }}">
                                </div>
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

            <!-- Reviews Table -->
            <div class="quran-card">
                <div class="quran-table-container">
                    <table class="quran-table quran-table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('memorization_reviews.fields.surah_ayah') }}</th>
                                <th>{{ __('memorization_reviews.fields.level') }}</th>
                                <th>{{ __('memorization_reviews.fields.result') }}</th>
                                <th>{{ __('memorization_reviews.fields.review_date') }}</th>
                                <th class="text-end">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                            <tr>
                                <td>
                                    <a href="{{ route('ayahs.show', $review->ayah) }}" class="text-decoration-none">
                                        {{ $review->ayah->surah->name_ar }} ({{ $review->ayah->ayah_number }})
                                    </a>
                                </td>
                                <td>
                                    @if($review->review_level)
                                    <span class="quran-table-badge info">
                                        {{ $reviewLevels[$review->review_level] ?? $review->review_level }}
                                    </span>
                                    @else
                                    —
                                    @endif
                                </td>
                                <td>
                                    @if($review->result)
                                    <span class="quran-table-badge {{ $review->result }}">
                                        {{ $results[$review->result] ?? $review->result }}
                                    </span>
                                    @else
                                    —
                                    @endif
                                </td>
                                <td>
                                    {{ $review->review_date->format('Y-m-d') }}
                                </td>
                                <td>
                                    <div class="quran-table-actions justify-content-end">
                                        <a href="{{ route('memorization-reviews.show', $review) }}" 
                                           class="quran-table-action-btn view">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('memorization-reviews.edit', $review) }}" 
                                           class="quran-table-action-btn edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="quran-table-action-btn delete" 
                                                onclick="confirmDelete({{ $review->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="quran-table-empty">
                                        <i class="bi bi-check2-all"></i>
                                        <h6>{{ __('memorization_reviews.no_reviews') }}</h6>
                                        <p>{{ __('memorization_reviews.no_reviews_message') }}</p>
                                        <a href="{{ route('memorization-reviews.create') }}" class="quran-btn quran-btn-primary mt-3">
                                            <i class="bi bi-plus-lg me-1"></i>
                                            {{ __('memorization_reviews.actions.create_first') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($reviews->hasPages())
                <div class="card-footer">
                    {{ $reviews->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">{{ __('common.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('memorization_reviews.messages.confirm_delete') }}</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">
                    {{ __('common.cancel') }}
                </button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="quran-btn quran-btn-danger">
                        {{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    const form = document.getElementById('deleteForm');
    form.action = "{{ route('memorization-reviews.destroy', ':id') }}".replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush