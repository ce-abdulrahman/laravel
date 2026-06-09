{{-- resources/views/languages/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Languages')
@section('page-title', 'Languages')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Languages</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Languages</h1>
            <div class="text-muted">Manage the system languages. Note: Deactivating or deleting languages will affect user options.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('languages.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Add Language
            </a>
        </div>
    </div>

    {{-- Success / Error Alerts --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" role="alert"
         style="background: linear-gradient(135deg, rgba(27,115,64,0.12) 0%, rgba(16,185,129,0.08) 100%); border-left: 4px solid #1B7340 !important;">
        <i class="bi bi-check-circle-fill me-2 text-success"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-octagon-fill me-2 text-danger"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Table Container --}}
    <div class="quran-card">
        {{-- Table --}}
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Order</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Native Name</th>
                        <th>Direction</th>
                        <th class="text-center" style="width: 100px;">Flag</th>
                        <th class="text-center" style="width: 120px;">Default</th>
                        <th class="text-center" style="width: 120px;">Status</th>
                        <th class="text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($languages as $lang)
                        <tr>
                            <td>
                                <span class="surah-number">{{ $lang->order }}</span>
                            </td>
                            <td>
                                <code class="fw-semibold text-primary">{{ $lang->code }}</code>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $lang->name }}</div>
                            </td>
                            <td>
                                <div class="text-muted">{{ $lang->native_name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ strtoupper($lang->direction) }}</span>
                            </td>
                            <td class="text-center" style="font-size: 1.25rem;">
                                {{ $lang->flag ?? '—' }}
                            </td>
                            <td class="text-center">
                                @if($lang->is_default)
                                    <span class="badge bg-primary">
                                        <i class="bi bi-star-fill me-1"></i> Default
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($lang->is_active)
                                    <span class="quran-table-badge success">
                                        <i class="bi bi-check-circle me-1"></i> Active
                                    </span>
                                @else
                                    <span class="quran-table-badge danger">
                                        <i class="bi bi-x-circle me-1"></i> Inactive
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="quran-table-actions justify-content-end">
                                    <a href="{{ route('languages.edit', $lang) }}"
                                       class="quran-table-action-btn edit"
                                       data-bs-toggle="tooltip"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    @if(!$lang->is_default)
                                        <button type="button"
                                                class="quran-table-action-btn delete"
                                                data-bs-toggle="tooltip"
                                                title="Delete"
                                                onclick="confirmDelete({{ $lang->id }}, '{{ addslashes($lang->name) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="quran-table-empty">
                                    <i class="bi bi-translate" style="font-size: 3rem;"></i>
                                    <h6 class="mt-3">No Languages Found</h6>
                                    <p>Start by creating the first system language.</p>
                                    <a href="{{ route('languages.create') }}" class="quran-btn quran-btn-primary mt-3">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        Add Language
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        @if($languages->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    Showing <strong>{{ $languages->firstItem() }}</strong> to <strong>{{ $languages->lastItem() }}</strong> of <strong>{{ $languages->total() }}</strong> entries
                </div>
                <div class="quran-pagination">
                    {{ $languages->links('pagination::bootstrap-5') }}
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
                    Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteModalBody">Are you sure you want to delete this language?</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="quran-btn quran-btn-danger">
                        <i class="bi bi-trash me-1"></i> Delete
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
        "{{ route('languages.destroy', ':id') }}".replace(':id', id);
    document.getElementById('deleteModalBody').textContent =
        "Are you sure you want to delete the language " + name + "?\nThis will also delete any translation records matching this locale.";
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, { placement: 'top' });
    });
});
</script>
@endpush
