{{-- resources/views/adhkars/index.blade.php --}}
@extends('layouts.app')

@section('title', __('adhkars.titles.index'))
@section('page-title', __('adhkars.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('adhkars.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('adhkars.titles.index') }}</h1>
            <div class="text-muted">{{ __('adhkars.hints.index') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('adhkar-categories.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-tag me-1"></i>
                {{ __('adhkars.actions.categories') }}
            </a>
            <a href="{{ route('adhkars.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('adhkars.actions.create') }}
            </a>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" role="alert"
         style="background: linear-gradient(135deg, rgba(27,115,64,0.12) 0%, rgba(16,185,129,0.08) 100%); border-left: 4px solid #1B7340 !important;">
        <i class="bi bi-check-circle-fill me-2 text-success"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Filter Row --}}
    <div class="quran-card mb-4">
        <div class="quran-card-body py-3">
            <form method="GET" action="{{ route('adhkars.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="category_id" class="quran-form-select" onchange="this.form.submit()">
                        <option value="">{{ __('adhkars.placeholders.all_categories') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($selectedCategoryId == $cat->id)>
                                {{ $cat->name_ku }} ({{ $cat->name_ar }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        class="quran-form-control"
                        placeholder="{{ __('adhkars.placeholders.search') }}"
                    >
                </div>
                <div class="col-md-2">
                    <button class="quran-btn quran-btn-primary w-100" type="submit">
                        <i class="bi bi-search me-1"></i>
                        {{ __('adhkars.actions.search') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table Container --}}
    <div class="quran-card">
        {{-- Toolbar --}}
        <div class="quran-table-toolbar">
            <div class="quran-table-search">
                <i class="bi bi-search"></i>
                <input type="text"
                       placeholder="{{ __('adhkars.placeholders.search') }}"
                       id="tableSearch"
                       value="{{ $search }}">
            </div>
            <div class="quran-table-filters">
                <button class="quran-table-filter-btn"
                        onclick="window.location.href='{{ route('adhkars.index') }}'">
                    <i class="bi bi-arrow-clockwise"></i>
                    {{ __('adhkars.actions.refresh') }}
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped quran-surah-table">
                <thead>
                    <tr>
                        <th style="width: 150px;">{{ __('adhkars.table.category') }}</th>
                        <th style="width: 80px;" class="text-center">{{ __('adhkars.table.order') }}</th>
                        <th>{{ __('adhkars.table.arabic') }}</th>
                        <th>{{ __('adhkars.table.kurdish') }}</th>
                        <th class="text-center" style="width: 80px;">{{ __('adhkars.table.count') }}</th>
                        <th style="width: 140px;">{{ __('adhkars.table.source') }}</th>
                        <th class="text-end" style="width: 140px;">{{ __('adhkars.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adhkars as $item)
                        <tr>
                            <td>
                                @if($item->category)
                                    <span class="quran-table-badge info" style="font-size: 0.78rem;">
                                        {{ $item->category->name_ku }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="surah-number">{{ $item->order }}</span>
                            </td>
                            <td>
                                <div class="surah-name-arabic"
                                     style="font-size: 1rem; text-align: right; direction: rtl; line-height: 1.7; max-width: 260px;">
                                    {{ Str::limit($item->arabic_text, 120) }}
                                </div>
                            </td>
                            <td>
                                <div class="text-muted small" style="line-height: 1.5; max-width: 200px;">
                                    {{ $item->translation_ku ? Str::limit($item->translation_ku, 100) : '—' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge px-2 py-1"
                                      style="background: rgba(212,175,55,0.15); color: #a08000; border: 1px solid rgba(212,175,55,0.3); font-weight: 700; font-size: 0.85rem;">
                                    {{ $item->count }}×
                                </span>
                            </td>
                            <td>
                                @if($item->source)
                                    <span class="badge bg-light text-dark border" style="font-size: 0.75rem;">
                                        {{ $item->source }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="quran-table-actions justify-content-end">
                                    <a href="{{ route('adhkars.show', $item) }}"
                                       class="quran-table-action-btn view"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('adhkars.actions.view') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('adhkars.edit', $item) }}"
                                       class="quran-table-action-btn edit"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('adhkars.actions.edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="quran-table-action-btn delete"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('adhkars.actions.delete') }}"
                                            onclick="confirmDelete({{ $item->id }}, '{{ addslashes(Str::limit($item->arabic_text, 30)) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="quran-table-empty">
                                    <i class="bi bi-chat-square-quote" style="font-size: 3rem;"></i>
                                    <h6 class="mt-3">{{ __('adhkars.empty.title') }}</h6>
                                    <p>{{ __('adhkars.empty.message') }}</p>
                                    <a href="{{ route('adhkars.create') }}" class="quran-btn quran-btn-primary mt-3">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        {{ __('adhkars.actions.create_first') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if($adhkars->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('adhkars.pagination.showing') }}
                    <strong>{{ $adhkars->firstItem() }}</strong>
                    {{ __('adhkars.pagination.to') }}
                    <strong>{{ $adhkars->lastItem() }}</strong>
                    {{ __('adhkars.pagination.of') }}
                    <strong>{{ $adhkars->total() }}</strong>
                    {{ __('adhkars.pagination.entries') }}
                </div>
                <div class="quran-pagination">
                    {{ $adhkars->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @elseif($adhkars->count() > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('adhkars.pagination.total') }}
                    <strong>{{ $adhkars->count() }}</strong>
                    {{ __('adhkars.pagination.entries') }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                    {{ __('common.confirm_delete') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteModalBody">{{ __('adhkars.messages.confirm_delete') }}</p>
                <div id="deleteModalText" class="mt-2 p-2 rounded-2 text-muted small"
                     style="background: rgba(0,0,0,0.04); direction: rtl; font-family: inherit;"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">
                    {{ __('common.cancel') }}
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="quran-btn quran-btn-danger">
                        <i class="bi bi-trash me-1"></i>{{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(id, arabicText) {
    document.getElementById('deleteForm').action =
        "{{ route('adhkars.destroy', ':id') }}".replace(':id', id);
    document.getElementById('deleteModalText').textContent = arabicText;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = searchInput.value.trim();
                const categoryId = '{{ $selectedCategoryId }}';
                let url = '{{ route("adhkars.index") }}?';
                if (categoryId) url += 'category_id=' + categoryId + '&';
                if (query) url += 'q=' + encodeURIComponent(query);
                window.location.href = url;
            }
        });
    }

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, { placement: 'top' });
    });
});
</script>
@endpush
