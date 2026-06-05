{{-- resources/views/hadith-categories/index.blade.php --}}
@extends('layouts.app')

@section('title', __('hadith_categories.titles.index'))
@section('page-title', __('hadith_categories.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('hadith_categories.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('hadith_categories.titles.index') }}</h1>
            <div class="text-muted">{{ __('hadith_categories.hints.index') }}</div>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('hadith-categories.index') }}" class="d-flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="quran-form-control"
                    placeholder="{{ __('hadith_categories.placeholders.search') }}"
                >
                <button class="quran-btn quran-btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>
            <a href="{{ route('hadith-categories.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('hadith_categories.actions.create') }}
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

    {{-- Table Container --}}
    <div class="quran-card">
        {{-- Toolbar --}}
        <div class="quran-table-toolbar">
            <div class="quran-table-search">
                <i class="bi bi-search"></i>
                <input type="text"
                       placeholder="{{ __('hadith_categories.placeholders.search') }}"
                       id="tableSearch"
                       value="{{ $search }}">
            </div>
            <div class="quran-table-filters">
                <button class="quran-table-filter-btn"
                        onclick="window.location.href='{{ route('hadith-categories.index') }}'">
                    <i class="bi bi-arrow-clockwise"></i>
                    {{ __('hadith_categories.actions.refresh') }}
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped quran-surah-table">
                <thead>
                    <tr>
                        <th class="number-column" style="width: 80px;">{{ __('hadith_categories.table.order') }}</th>
                        <th>{{ __('hadith_categories.table.name_ku') }}</th>
                        <th>{{ __('hadith_categories.table.name_ar') }}</th>
                        <th>{{ __('hadith_categories.table.name_en') }}</th>
                        <th style="width: 160px;">{{ __('hadith_categories.table.icon') }}</th>
                        <th class="text-center" style="width: 120px;">{{ __('hadith_categories.table.status') }}</th>
                        <th class="text-end" style="width: 170px;">{{ __('hadith_categories.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td class="number-column">
                                <span class="surah-number">{{ $cat->order }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $cat->name_ku }}</div>
                            </td>
                            <td>
                                <div class="surah-name-arabic">{{ $cat->name_ar }}</div>
                            </td>
                            <td>
                                <span class="text-muted">{{ $cat->name_en ?? '—' }}</span>
                            </td>
                            <td>
                                @if($cat->icon)
                                    <span class="badge bg-light text-dark border" style="font-family: monospace; font-size: 0.75rem;">
                                        <i class="bi bi-grid me-1 text-muted"></i>{{ $cat->icon }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($cat->is_active)
                                    <span class="quran-table-badge success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ __('hadith_categories.status.active') }}
                                    </span>
                                @else
                                    <span class="quran-table-badge danger">
                                        <i class="bi bi-x-circle me-1"></i>
                                        {{ __('hadith_categories.status.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="quran-table-actions justify-content-end">
                                    <a href="{{ route('hadith-categories.show', $cat) }}"
                                       class="quran-table-action-btn view"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('hadith_categories.actions.view') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('hadiths.index') }}?category_id={{ $cat->id }}"
                                       class="quran-table-action-btn"
                                       style="color: #1B7340;"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('hadith_categories.actions.view_hadiths') }}">
                                        <i class="bi bi-list-task"></i>
                                    </a>
                                    <a href="{{ route('hadith-categories.edit', $cat) }}"
                                       class="quran-table-action-btn edit"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('hadith_categories.actions.edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="quran-table-action-btn delete"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('hadith_categories.actions.delete') }}"
                                            onclick="confirmDelete({{ $cat->id }}, '{{ addslashes($cat->name_ku) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="quran-table-empty">
                                    <i class="bi bi-tag" style="font-size: 3rem;"></i>
                                    <h6 class="mt-3">{{ __('hadith_categories.empty.title') }}</h6>
                                    <p>{{ __('hadith_categories.empty.message') }}</p>
                                    <a href="{{ route('hadith-categories.create') }}" class="quran-btn quran-btn-primary mt-3">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        {{ __('hadith_categories.actions.create_first') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if($categories->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('hadith_categories.pagination.showing') }}
                    <strong>{{ $categories->firstItem() }}</strong>
                    {{ __('hadith_categories.pagination.to') }}
                    <strong>{{ $categories->lastItem() }}</strong>
                    {{ __('hadith_categories.pagination.of') }}
                    <strong>{{ $categories->total() }}</strong>
                    {{ __('hadith_categories.pagination.entries') }}
                </div>
                <div class="quran-pagination">
                    {{ $categories->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @elseif($categories->count() > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('hadith_categories.pagination.total') }}
                    <strong>{{ $categories->count() }}</strong>
                    {{ __('hadith_categories.pagination.entries') }}
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
                <p>{{ __('hadith_categories.messages.confirm_delete') }}</p>
                <div id="deleteModalName" class="mt-2 p-2 rounded-2 fw-bold" style="background: rgba(220,53,69,0.05);"></div>
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
function confirmDelete(id, name) {
    document.getElementById('deleteForm').action =
        "{{ route('hadith-categories.destroy', ':id') }}".replace(':id', id);
    document.getElementById('deleteModalName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = searchInput.value.trim();
                window.location.href = query
                    ? '{{ route("hadith-categories.index") }}?q=' + encodeURIComponent(query)
                    : '{{ route("hadith-categories.index") }}';
            }
        });
    }
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, { placement: 'top' });
    });
});
</script>
@endpush
