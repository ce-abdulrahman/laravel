{{-- resources/views/favorites/index.blade.php --}}
@extends('layouts.app')

@section('title', __('favorites.titles.index'))
@section('page-title', __('favorites.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('favorites.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('favorites.titles.index') }}</h1>
            <div class="text-muted">{{ __('favorites.hints.my_favorites') }}</div>
        </div>

        <div class="d-flex gap-2">
            @if($favorites->count() > 0)
            <button type="button" class="quran-btn quran-btn-outline-danger" id="bulkDeleteBtn" disabled>
                <i class="bi bi-trash me-1"></i>
                {{ __('favorites.actions.bulk_delete') }}
            </button>
            @endif
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('favorites.total_favorites') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_favorites'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-heart"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('favorites.unique_surahs') }}</div>
                        <div class="quran-stat-value">{{ $stats['unique_surahs'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('favorites.recent') }}</div>
                        <div class="quran-stat-value">{{ $stats['recent_favorites'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-calendar-heart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('favorites.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="quran-form-label">{{ __('favorites.filter_by_surah') }}</label>
                        <select name="surah_id" class="quran-form-select">
                            <option value="">{{ __('favorites.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                {{ $surah->number }}. {{ $surah->name_ar }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="quran-form-label">{{ __('favorites.per_page') }}</label>
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

    <!-- Favorites Grid -->
    <div class="quran-card">
        @if($favorites->count() > 0)
        <div class="row g-0">
            <div class="col-12 p-3 border-bottom">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="selectAll">
                    <label class="form-check-label" for="selectAll">
                        {{ __('favorites.select_all') }}
                    </label>
                </div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-2 g-0" id="favoritesGrid">
            @foreach($favorites as $favorite)
            <div class="col">
                <div class="favorite-item p-4 h-100 border-end border-bottom">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input favorite-checkbox" 
                               value="{{ $favorite->id }}">
                    </div>
                    <a href="{{ route('ayahs.show', $favorite->ayah) }}" class="text-decoration-none">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="quran-surah-number" style="width: 36px; height: 36px; font-size: 14px;">
                                {{ $favorite->ayah->surah->number }}
                            </span>
                            <h6 class="mb-0">{{ $favorite->ayah->surah->name_ar }}</h6>
                            <span class="quran-table-badge info ms-auto">
                                {{ __('favorites.ayah') }} {{ $favorite->ayah->ayah_number }}
                            </span>
                        </div>
                        <div class="arabic-text" style="font-size: 16px; line-height: 1.8;">
                            {{ Str::limit($favorite->ayah->text_uthmani, 100) }}
                        </div>
                    </a>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="quran-table-action-btn delete" 
                                onclick="removeFavorite({{ $favorite->id }}, this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="quran-table-empty p-5">
            <i class="bi bi-heart"></i>
            <h6>{{ __('favorites.no_favorites') }}</h6>
            <p>{{ __('favorites.no_favorites_message') }}</p>
            <a href="{{ route('ayahs.index') }}" class="quran-btn quran-btn-primary mt-3">
                <i class="bi bi-book me-1"></i>
                {{ __('favorites.actions.browse_ayahs') }}
            </a>
        </div>
        @endif

        @if($favorites->hasPages())
        <div class="card-footer">
            {{ $favorites->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedIds = new Set();

document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.favorite-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = this.checked;
        if (this.checked) {
            selectedIds.add(cb.value);
        } else {
            selectedIds.delete(cb.value);
        }
    });
    updateBulkDeleteButton();
});

document.querySelectorAll('.favorite-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        if (this.checked) {
            selectedIds.add(this.value);
        } else {
            selectedIds.delete(this.value);
        }
        updateBulkDeleteButton();
        
        // Update select all
        const allCheckboxes = document.querySelectorAll('.favorite-checkbox');
        const selectAll = document.getElementById('selectAll');
        selectAll.checked = allCheckboxes.length === selectedIds.size;
    });
});

function updateBulkDeleteButton() {
    const btn = document.getElementById('bulkDeleteBtn');
    btn.disabled = selectedIds.size === 0;
}

document.getElementById('bulkDeleteBtn')?.addEventListener('click', function() {
    if (selectedIds.size === 0) return;
    
    if (confirm('{{ __("favorites.messages.confirm_bulk_delete") }}')) {
        fetch('{{ route("favorites.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: Array.from(selectedIds) })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }
});

function removeFavorite(id, btn) {
    if (confirm('{{ __("favorites.messages.confirm_delete") }}')) {
        fetch(`/favorites/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                btn.closest('.col').remove();
            }
        });
    }
}
</script>
@endpush