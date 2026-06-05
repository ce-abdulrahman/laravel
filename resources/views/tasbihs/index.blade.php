{{-- resources/views/tasbihs/index.blade.php --}}
@extends('layouts.app')

@section('title', __('tasbihs.titles.index'))
@section('page-title', __('tasbihs.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('tasbihs.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('tasbihs.titles.index') }}</h1>
            <div class="text-muted">{{ __('tasbihs.hints.index') }}</div>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('tasbihs.index') }}" class="d-flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="quran-form-control"
                    placeholder="{{ __('tasbihs.placeholders.search') }}"
                >
                <button class="quran-btn quran-btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>
            <a href="{{ route('tasbihs.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('tasbihs.actions.create') }}
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
                       placeholder="{{ __('tasbihs.placeholders.search') }}"
                       id="tableSearch"
                       value="{{ $search }}">
            </div>
            <div class="quran-table-filters">
                <button class="quran-table-filter-btn"
                        onclick="window.location.href='{{ route('tasbihs.index') }}'">
                    <i class="bi bi-arrow-clockwise"></i>
                    {{ __('tasbihs.actions.refresh') }}
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped quran-surah-table">
                <thead>
                    <tr>
                        <th class="number-column" style="width: 80px;">{{ __('tasbihs.table.number') }}</th>
                        <th>{{ __('tasbihs.table.name') }}</th>
                        <th style="width: 160px;" class="text-center">{{ __('tasbihs.table.target') }}</th>
                        <th class="text-center" style="width: 120px;">{{ __('tasbihs.table.status') }}</th>
                        <th class="text-end" style="width: 140px;">{{ __('tasbihs.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasbihs as $tasbih)
                        <tr>
                            <td class="number-column">
                                <span class="surah-number">{{ $loop->iteration }}</span>
                            </td>
                            <td>
                                <div class="surah-name-arabic"
                                     style="font-size: 1.1rem; line-height: 1.7; direction: rtl; text-align: right; font-family: var(--quran-font, 'Amiri Quran', serif);">
                                    {{ $tasbih->name }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge px-3 py-2"
                                      style="background: rgba(212,175,55,0.15); color: #a08000;
                                             border: 1px solid rgba(212,175,55,0.3); font-size: 1rem; font-weight: 700;">
                                    {{ $tasbih->target }}×
                                </span>
                            </td>
                            <td class="text-center">
                                @if($tasbih->is_active)
                                    <span class="quran-table-badge success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ __('tasbihs.status.active') }}
                                    </span>
                                @else
                                    <span class="quran-table-badge danger">
                                        <i class="bi bi-x-circle me-1"></i>
                                        {{ __('tasbihs.status.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="quran-table-actions justify-content-end">
                                    <a href="{{ route('tasbihs.show', $tasbih) }}"
                                       class="quran-table-action-btn view"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('tasbihs.actions.view') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('tasbihs.edit', $tasbih) }}"
                                       class="quran-table-action-btn edit"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('tasbihs.actions.edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="quran-table-action-btn delete"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('tasbihs.actions.delete') }}"
                                            onclick="confirmDelete({{ $tasbih->id }}, '{{ addslashes(Str::limit($tasbih->name, 30)) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="quran-table-empty">
                                    <i class="bi bi-heptagon" style="font-size: 3rem;"></i>
                                    <h6 class="mt-3">{{ __('tasbihs.empty.title') }}</h6>
                                    <p>{{ __('tasbihs.empty.message') }}</p>
                                    <a href="{{ route('tasbihs.create') }}" class="quran-btn quran-btn-primary mt-3">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        {{ __('tasbihs.actions.create_first') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if($tasbihs->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('tasbihs.pagination.showing') }}
                    <strong>{{ $tasbihs->firstItem() }}</strong>
                    {{ __('tasbihs.pagination.to') }}
                    <strong>{{ $tasbihs->lastItem() }}</strong>
                    {{ __('tasbihs.pagination.of') }}
                    <strong>{{ $tasbihs->total() }}</strong>
                    {{ __('tasbihs.pagination.entries') }}
                </div>
                <div class="quran-pagination">
                    {{ $tasbihs->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @elseif($tasbihs->count() > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('tasbihs.pagination.total') }}
                    <strong>{{ $tasbihs->count() }}</strong>
                    {{ __('tasbihs.pagination.entries') }}
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
                <p>{{ __('tasbihs.messages.confirm_delete') }}</p>
                <div id="deleteModalName" class="mt-2 p-2 rounded-2 text-center"
                     style="background: rgba(0,0,0,0.04); font-family: var(--quran-font, 'Amiri Quran', serif); font-size: 1.2rem; direction: rtl;"></div>
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
        "{{ route('tasbihs.destroy', ':id') }}".replace(':id', id);
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
                    ? '{{ route("tasbihs.index") }}?q=' + encodeURIComponent(query)
                    : '{{ route("tasbihs.index") }}';
            }
        });
    }
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, { placement: 'top' });
    });
});
</script>
@endpush
