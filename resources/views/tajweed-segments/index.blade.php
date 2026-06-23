{{-- resources/views/tajweed-segments/index.blade.php --}}
@extends('layouts.app')

@section('title', __('tajweed_segments.titles.index'))
@section('page-title', __('tajweed_segments.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('tajweed_segments.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('tajweed_segments.titles.index') }}</h1>
            <div class="text-muted">{{ __('tajweed_segments.hints.manage') }}</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if(auth()->user()?->role === 'admin')
            <!-- Action Buttons for Admin -->
            <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload me-1"></i>
                Import
            </button>

            <div class="btn-group">
                <button type="button" class="quran-btn quran-btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i>
                    Export
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('tajweed-segments.export', array_merge(request()->query(), ['format' => 'json'])) }}">
                            <i class="bi bi-filetype-json me-2 text-primary"></i>Export as JSON
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('tajweed-segments.export', array_merge(request()->query(), ['format' => 'csv'])) }}">
                            <i class="bi bi-filetype-csv me-2 text-success"></i>Export as CSV
                        </a>
                    </li>
                </ul>
            </div>

            <button type="button" class="quran-btn quran-btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rebuildModal">
                <i class="bi bi-arrow-repeat me-1"></i>
                Rebuild
            </button>

            <a href="{{ route('tajweed-segments.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('tajweed_segments.actions.create') }}
            </a>
            @endif
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <h6 class="alert-heading fw-bold"><i class="bi bi-x-circle-fill me-2"></i>Import Validation Failures</h6>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tajweed_segments.total_segments') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_segments']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-puzzle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tajweed_segments.total_rules_used') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_rules_used'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-palette"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tajweed_segments.ayahs_with_tajweed') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_ayahs_with_tajweed']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filter Section -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('tajweed-segments.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('tajweed_segments.filter_by_category') }}</label>
                        <select name="category_id" class="quran-form-select">
                            <option value="">{{ __('tajweed_segments.all_categories') }}</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('tajweed_segments.filter_by_rule') }}</label>
                        <select name="tajweed_rule_id" class="quran-form-select">
                            <option value="">{{ __('tajweed_segments.all_rules') }}</option>
                            @foreach($tajweedRules as $rule)
                            <option value="{{ $rule->id }}" {{ request('tajweed_rule_id') == $rule->id ? 'selected' : '' }}>
                                {{ $rule->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="quran-form-label">{{ __('tajweed_segments.filter_by_surah') }}</label>
                        <select name="surah_id" class="quran-form-select">
                            <option value="">{{ __('tajweed_segments.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                {{ $surah->number }}. {{ $surah->name_ar }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="quran-form-label">{{ __('tajweed_segments.filter_by_ayah') }}</label>
                        <input type="number" name="ayah_number" class="quran-form-control" 
                               placeholder="Ayah #..." 
                               value="{{ request('ayah_number') }}" min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="quran-form-label">{{ __('tajweed_segments.search') }}</label>
                        <input type="text" name="search" class="quran-form-control" 
                               placeholder="{{ __('tajweed_segments.search_placeholder') }}" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-12 text-end">
                        <a href="{{ route('tajweed-segments.index') }}" class="quran-btn quran-btn-outline-secondary me-2">
                            <i class="bi bi-x-lg me-1"></i>Clear
                        </a>
                        <button type="submit" class="quran-btn quran-btn-primary">
                            <i class="bi bi-funnel me-1"></i>
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th>{{ __('tajweed_segments.fields.surah_ayah') }}</th>
                        <th>{{ __('tajweed_segments.fields.rule') }}</th>
                        <th>{{ __('tajweed_segments.fields.matched_text') }}</th>
                        <th>Character Range</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($segments as $segment)
                    <tr>
                        <td>
                            <a href="{{ route('ayahs.show', $segment->ayah) }}" class="text-decoration-none fw-bold">
                                {{ $segment->ayah->surah->name_ar }} 
                                ({{ $segment->ayah->ayah_number }})
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($segment->tajweedRule->color_code)
                                <span style="width: 12px; height: 12px; border-radius: 3px; 
                                             background-color: {{ $segment->tajweedRule->color_code }};"></span>
                                @endif
                                <a href="{{ route('tajweed-rules.show', $segment->tajweedRule) }}" 
                                   class="text-decoration-none">
                                    {{ $segment->tajweedRule->name }}
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="arabic-text" style="font-size: 18px;">
                                <span style="background-color: {{ $segment->tajweedRule->color_code }}20; 
                                             padding: 2px 8px; border-radius: 6px;">
                                    {{ $segment->matched_text }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @if($segment->start_index !== null && $segment->end_index !== null)
                            <span class="badge bg-light text-dark font-monospace border">
                                {{ $segment->start_index }} - {{ $segment->end_index }}
                            </span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="quran-table-actions justify-content-end">
                                <a href="{{ route('tajweed-segments.show', $segment) }}" 
                                   class="quran-table-action-btn view">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('tajweed-segments.edit', $segment) }}" 
                                   class="quran-table-action-btn edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="quran-table-action-btn delete" 
                                        onclick="confirmDelete({{ $segment->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="quran-table-empty">
                                <i class="bi bi-puzzle"></i>
                                <h6>{{ __('tajweed_segments.no_segments_found') }}</h6>
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('tajweed-segments.create') }}" 
                                   class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    {{ __('tajweed_segments.actions.create_first') }}
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($segments->hasPages())
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
            {{ $segments->links() }}
        </div>
        @endif
    </div>
</div>

@if(auth()->user()?->role === 'admin')
<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-danger">{{ __('common.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <p class="mb-0">{{ __('tajweed_segments.messages.confirm_delete') }}</p>
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('tajweed-segments.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-header border-0 bg-primary bg-opacity-10">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-upload me-2"></i>Import Segments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted small mb-3">
                        چەندین فایلی <strong>JSON</strong> هەڵبژێرە بۆ ئیمپۆرتکردن. فایلەکان بەدواداچوون پرۆسەس دەکرێن. بەشەکانی هاوشێوە بەخۆماندا تێپەردەکرێن.
                        <br><span class="text-muted">You can select multiple <strong>.json</strong> files at once. Duplicates are skipped automatically.</span>
                    </p>

                    {{-- Drop Zone --}}
                    <div id="importDropZone"
                         class="border border-2 border-dashed rounded-3 p-4 text-center mb-3 position-relative"
                         style="border-color: var(--bs-primary) !important; background: rgba(var(--bs-primary-rgb), .04); cursor: pointer; transition: background .2s;">
                        <i class="bi bi-file-earmark-arrow-up fs-2 text-primary mb-2 d-block"></i>
                        <div class="fw-semibold">Click to choose files <span class="text-muted fw-normal">or drag &amp; drop here</span></div>
                        <div class="text-muted small mt-1">Accepted: <code>.json</code> &nbsp;|&nbsp; Multiple files allowed</div>
                        <input type="file" name="files[]" id="importFileInput"
                               class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                               accept=".json" multiple style="cursor:pointer;">
                    </div>

                    {{-- File List Preview --}}
                    <div id="importFileList" class="d-none">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <small class="fw-semibold text-secondary">Selected Files:</small>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearAllFiles">Clear all</button>
                        </div>
                        <ul class="list-group list-group-flush" id="importFileItems"></ul>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="quran-btn quran-btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="quran-btn quran-btn-primary" id="importSubmitBtn" disabled>
                        <i class="bi bi-cloud-upload me-1"></i>
                        <span id="importSubmitLabel">Upload &amp; Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Rebuild Modal -->
<div class="modal fade" id="rebuildModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('tajweed-segments.rebuild') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 bg-danger bg-opacity-10">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ __('tajweed_segments.messages.rebuild_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="alert alert-danger mb-3">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>{{ __('tajweed_segments.messages.rebuild_warning') }}
                    </div>
                    <p class="text-muted small">This action will delete all existing segments and replace them entirely with the contents of the file uploaded below.</p>
                    <div class="mb-3">
                        <label class="quran-form-label">Upload New Dataset (JSON/CSV)</label>
                        <input type="file" name="file" class="form-control" accept=".json,.csv,.txt" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="quran-btn quran-btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="quran-btn quran-btn-danger">Confirm & Rebuild</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// ─── Delete confirm ───────────────────────────────────────────────────────────
function confirmDelete(id) {
    const form = document.getElementById('deleteForm');
    form.action = "{{ route('tajweed-segments.destroy', ':id') }}".replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ─── Multi-file Import ────────────────────────────────────────────────────────
(function () {
    const input      = document.getElementById('importFileInput');
    const dropZone   = document.getElementById('importDropZone');
    const fileList   = document.getElementById('importFileList');
    const fileItems  = document.getElementById('importFileItems');
    const submitBtn  = document.getElementById('importSubmitBtn');
    const submitLbl  = document.getElementById('importSubmitLabel');
    const clearBtn   = document.getElementById('clearAllFiles');

    if (!input) return;

    // All selected files live here — NOT in the native input.files
    let dt = new DataTransfer();

    // ── helpers ────────────────────────────────────────────────────────────────
    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function renderList() {
        fileItems.innerHTML = '';
        const files = dt.files;

        if (files.length === 0) {
            fileList.classList.add('d-none');
            submitBtn.disabled = true;
            submitLbl.textContent = 'Upload & Import';
            return;
        }

        fileList.classList.remove('d-none');
        submitBtn.disabled = false;
        submitLbl.textContent = `Upload & Import (${files.length} file${files.length > 1 ? 's' : ''})`;

        for (let i = 0; i < files.length; i++) {
            const f = files[i];
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex align-items-center justify-content-between px-0 py-2';
            li.innerHTML = `
                <div class="d-flex align-items-center gap-2 text-truncate">
                    <i class="bi bi-filetype-json text-primary fs-5 flex-shrink-0"></i>
                    <span class="text-truncate small fw-medium">${f.name}</span>
                    <span class="badge bg-light text-secondary border ms-1 flex-shrink-0">${formatBytes(f.size)}</span>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 flex-shrink-0 ms-2 remove-file-btn"
                        data-index="${i}" title="Remove">
                    <i class="bi bi-x-circle"></i>
                </button>`;
            fileItems.appendChild(li);
        }
        // ⚠ Do NOT set input.files = dt.files — unreliable cross-browser.
        //   Files are sent via fetch + FormData in the submit handler below.
    }

    function addFiles(newFiles) {
        for (const f of newFiles) {
            if (!f.name.toLowerCase().endsWith('.json')) continue;
            let duplicate = false;
            for (const existing of dt.files) {
                if (existing.name === f.name) { duplicate = true; break; }
            }
            if (!duplicate) dt.items.add(f);
        }
        renderList();
    }

    function removeFile(index) {
        const newDt = new DataTransfer();
        for (let i = 0; i < dt.files.length; i++) {
            if (i !== index) newDt.items.add(dt.files[i]);
        }
        dt = newDt;
        renderList();
    }

    // ── native input change ────────────────────────────────────────────────────
    input.addEventListener('change', function () {
        addFiles(this.files);
        this.value = ''; // reset so same file can be re-selected after removal
    });

    fileItems.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-file-btn');
        if (btn) removeFile(parseInt(btn.dataset.index, 10));
    });

    clearBtn.addEventListener('click', function () {
        dt = new DataTransfer();
        renderList();
    });

    // ── drag-and-drop ──────────────────────────────────────────────────────────
    dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.style.background = 'rgba(var(--bs-primary-rgb),.12)'; });
    dropZone.addEventListener('dragleave', ()  => { dropZone.style.background = 'rgba(var(--bs-primary-rgb),.04)'; });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.style.background = 'rgba(var(--bs-primary-rgb), .04)';
        addFiles(e.dataTransfer.files);
    });

    // ── Submit via fetch — bypasses unreliable input.files = dt.files ──────────
    document.getElementById('importForm').addEventListener('submit', function (e) {
        e.preventDefault();

        if (dt.files.length === 0) {
            alert('Please select at least one .json file.');
            return;
        }

        // Build FormData manually from our DataTransfer store
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        for (let i = 0; i < dt.files.length; i++) {
            fd.append('files[]', dt.files[i], dt.files[i].name);
        }

        // Loading state
        submitBtn.disabled = true;
        submitLbl.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Importing…';

        fetch('{{ route('tajweed-segments.import') }}', {
            method: 'POST',
            body: fd
            // Do NOT set Content-Type header — browser handles multipart boundary
        })
        .then(function (res) {
            // fetch follows redirects; res.url is the final destination
            window.location.href = res.url;
        })
        .catch(function () {
            // Restore button on network error
            submitBtn.disabled = false;
            renderList();
            alert('Network error — please try again.');
        });
    });

    // ── Reset when modal closes ────────────────────────────────────────────────
    document.getElementById('importModal').addEventListener('hidden.bs.modal', function () {
        dt = new DataTransfer();
        renderList();
    });
})();
</script>
@endpush