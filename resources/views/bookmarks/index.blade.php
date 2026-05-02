{{-- resources/views/bookmarks/index.blade.php --}}
@extends('layouts.app')

@section('title', __('bookmarks.titles.index'))
@section('page-title', __('bookmarks.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('bookmarks.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('bookmarks.titles.index') }}</h1>
            <div class="text-muted">{{ __('bookmarks.hints.my_bookmarks') }}</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('bookmarks.export') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-download me-1"></i>
                {{ __('bookmarks.actions.export') }}
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('bookmarks.total_bookmarks') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_bookmarks'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-bookmark"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('bookmarks.with_notes') }}</div>
                        <div class="quran-stat-value">{{ $stats['bookmarks_with_notes'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-pencil"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('bookmarks.recent') }}</div>
                        <div class="quran-stat-value">{{ $stats['recent_bookmarks'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('bookmarks.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="quran-form-label">{{ __('bookmarks.filter_by_surah') }}</label>
                        <select name="surah_id" class="quran-form-select">
                            <option value="">{{ __('bookmarks.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                {{ $surah->number }}. {{ $surah->name_ar }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="quran-form-label">{{ __('bookmarks.search') }}</label>
                        <input type="text" name="search" class="quran-form-control" 
                               placeholder="{{ __('bookmarks.search_placeholder') }}" 
                               value="{{ request('search') }}">
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

    <!-- Bookmarks List -->
    <div class="quran-card">
        @if($bookmarks->count() > 0)
        <div class="list-group list-group-flush">
            @foreach($bookmarks as $bookmark)
            <div class="list-group-item bg-transparent p-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="quran-surah-number" style="min-width: 48px;">
                        {{ $bookmark->ayah->surah->number }}:{{ $bookmark->ayah->ayah_number }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <h6 class="mb-0">{{ $bookmark->ayah->surah->name_ar }}</h6>
                            <span class="quran-table-badge info">{{ $bookmark->ayah->surah->revelation_type }}</span>
                        </div>
                        <div class="arabic-text mb-3" style="font-size: 18px; line-height: 1.8;">
                            {{ Str::limit($bookmark->ayah->text_uthmani, 200) }}
                        </div>
                        @if($bookmark->note)
                        <div class="bg-light p-3 rounded-3 mb-3">
                            <i class="bi bi-pencil me-1 text-muted"></i>
                            <span>{{ $bookmark->note }}</span>
                        </div>
                        @endif
                        <div class="d-flex align-items-center justify-content-between">
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                {{ $bookmark->created_at->diffForHumans() }}
                            </small>
                            <div class="quran-table-actions">
                                <a href="{{ route('bookmarks.show', $bookmark) }}" 
                                   class="quran-table-action-btn view">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('bookmarks.edit', $bookmark) }}" 
                                   class="quran-table-action-btn edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="quran-table-action-btn delete" 
                                        onclick="confirmDelete({{ $bookmark->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="quran-table-empty p-5">
            <i class="bi bi-bookmark"></i>
            <h6>{{ __('bookmarks.no_bookmarks') }}</h6>
            <p>{{ __('bookmarks.no_bookmarks_message') }}</p>
            <a href="{{ route('ayahs.index') }}" class="quran-btn quran-btn-primary mt-3">
                <i class="bi bi-book me-1"></i>
                {{ __('bookmarks.actions.browse_ayahs') }}
            </a>
        </div>
        @endif

        @if($bookmarks->hasPages())
        <div class="card-footer">
            {{ $bookmarks->links() }}
        </div>
        @endif
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
                <p>{{ __('bookmarks.messages.confirm_delete') }}</p>
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
    form.action = "{{ route('bookmarks.destroy', ':id') }}".replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush