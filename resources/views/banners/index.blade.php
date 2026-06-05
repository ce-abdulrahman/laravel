{{-- resources/views/banners/index.blade.php --}}
@extends('layouts.app')

@section('title', __('banners.titles.index'))
@section('page-title', __('banners.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('banners.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('banners.titles.index') }}</h1>
            <div class="text-muted">{{ __('banners.hints.index') }}</div>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('banners.index') }}" class="d-flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="quran-form-control"
                    placeholder="{{ __('banners.placeholders.search') }}"
                >
                <button class="quran-btn quran-btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>
            <a href="{{ route('banners.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('banners.actions.create') }}
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
                       placeholder="{{ __('banners.placeholders.search') }}"
                       id="tableSearch"
                       value="{{ $search }}">
            </div>
            <div class="quran-table-filters">
                <button class="quran-table-filter-btn" onclick="window.location.href='{{ route('banners.index') }}'">
                    <i class="bi bi-arrow-clockwise"></i>
                    {{ __('banners.actions.refresh') }}
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped quran-surah-table">
                <thead>
                    <tr>
                        <th class="number-column" style="width: 80px;">{{ __('banners.table.order') }}</th>
                        <th>{{ __('banners.table.arabic') }}</th>
                        <th>{{ __('banners.table.verse') }}</th>
                        <th>{{ __('banners.table.source') }}</th>
                        <th>{{ __('banners.table.linked') }}</th>
                        <th class="text-center" style="width: 110px;">{{ __('banners.table.status') }}</th>
                        <th class="text-end" style="width: 140px;">{{ __('banners.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $banner)
                        <tr>
                            <td class="number-column">
                                <span class="surah-number">{{ $banner->order }}</span>
                            </td>
                            <td>
                                @if($banner->title_arabic)
                                    <div class="surah-name-arabic" style="font-size: 1.1rem; line-height: 1.7; font-family: var(--quran-font, 'Amiri Quran', serif);">
                                        {{ $banner->title_arabic }}
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 500; font-size: 0.92rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ Str::limit($banner->verse, 80) }}
                                </div>
                            </td>
                            <td>
                                @if($banner->source)
                                    <span class="badge bg-light text-dark border">{{ $banner->source }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($banner->surah)
                                    <span class="quran-table-badge info">
                                        <i class="bi bi-link-45deg me-1"></i>
                                        {{ __('banners.surah_link', ['name' => $banner->surah->name_ar, 'number' => $banner->ayah_number ?? 1]) }}
                                    </span>
                                @else
                                    <span class="text-muted small">{{ __('banners.fields.not_linked') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($banner->is_active)
                                    <span class="quran-table-badge success">
                                        <i class="bi bi-check-circle me-1"></i>{{ __('banners.status.active') }}
                                    </span>
                                @else
                                    <span class="quran-table-badge danger">
                                        <i class="bi bi-x-circle me-1"></i>{{ __('banners.status.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="quran-table-actions justify-content-end">
                                    <a href="{{ route('banners.show', $banner) }}"
                                       class="quran-table-action-btn view"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('banners.actions.view') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('banners.edit', $banner) }}"
                                       class="quran-table-action-btn edit"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('banners.actions.edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="quran-table-action-btn delete"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('banners.actions.delete') }}"
                                            onclick="confirmDelete({{ $banner->id }}, '{{ Str::limit($banner->verse, 30) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="quran-table-empty">
                                    <i class="bi bi-image-alt" style="font-size: 3rem;"></i>
                                    <h6 class="mt-3">{{ __('banners.empty.title') }}</h6>
                                    <p>{{ __('banners.empty.message') }}</p>
                                    <a href="{{ route('banners.create') }}" class="quran-btn quran-btn-primary mt-3">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        {{ __('banners.actions.create_first') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($banners->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('banners.pagination.showing') }}
                    <strong>{{ $banners->firstItem() }}</strong>
                    {{ __('banners.pagination.to') }}
                    <strong>{{ $banners->lastItem() }}</strong>
                    {{ __('banners.pagination.of') }}
                    <strong>{{ $banners->total() }}</strong>
                    {{ __('banners.pagination.entries') }}
                </div>
                <div class="quran-pagination">
                    {{ $banners->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @elseif($banners->count() > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('banners.pagination.total') }}
                    <strong>{{ $banners->count() }}</strong>
                    {{ __('banners.pagination.entries') }}
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
                <p id="deleteModalBody">{{ __('banners.messages.confirm_delete') }}</p>
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
function confirmDelete(id, title) {
    document.getElementById('deleteForm').action = "{{ route('banners.destroy', ':id') }}".replace(':id', id);
    document.getElementById('deleteModalBody').textContent = "{{ __('banners.messages.confirm_delete') }}: " + title;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = searchInput.value.trim();
                window.location.href = query
                    ? '{{ route("banners.index") }}?q=' + encodeURIComponent(query)
                    : '{{ route("banners.index") }}';
            }
        });
    }

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, { placement: 'top' });
    });
});
</script>
@endpush
