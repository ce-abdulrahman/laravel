{{-- resources/views/translations/manager.blade.php --}}
@extends('layouts.app')

@section('title', __('translations_manager.title'))
@section('page-title', __('translations_manager.title'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('translations_manager.title') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('translations_manager.heading') }}</h1>
            <div class="text-muted">{{ __('translations_manager.subheading') }}</div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form action="{{ route('translations-manager.scan') }}" method="POST" class="d-inline m-0">
                @csrf
                <button type="submit" class="quran-btn quran-btn-outline-secondary" title="{{ __('translations_manager.actions.scan_project') }}">
                    <i class="bi bi-search me-1"></i> {{ __('translations_manager.actions.scan_project') }}
                </button>
            </form>
            <form action="{{ route('translations-manager.sync') }}" method="POST" class="d-inline m-0">
                @csrf
                <button type="submit" class="quran-btn quran-btn-outline-secondary" title="{{ __('translations_manager.actions.sync_keys') }}">
                    <i class="bi bi-arrow-repeat me-1"></i> {{ __('translations_manager.actions.sync_keys') }}
                </button>
            </form>
            <form action="{{ route('translations-manager.clear-cache') }}" method="POST" class="d-inline m-0">
                @csrf
                <button type="submit" class="quran-btn quran-btn-outline-secondary" title="{{ __('translations_manager.actions.rebuild_cache') }}">
                    <i class="bi bi-arrow-clockwise me-1"></i> {{ __('translations_manager.actions.rebuild_cache') }}
                </button>
            </form>
            <a href="{{ route('translations-manager.report') }}" class="quran-btn quran-btn-outline-primary" title="{{ __('translations_manager.actions.coverage_report') }}">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> {{ __('translations_manager.actions.coverage_report') }}
            </a>
            <a href="{{ route('translations-manager.bulk') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-grid-3x3-gap me-1"></i>
                {{ __('translations_manager.actions.bulk_grid') }}
            </a>
            <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importExportModal">
                <i class="bi bi-download me-1"></i>
                {{ __('translations_manager.actions.import_export') }}
            </button>
            <button type="button" class="quran-btn quran-btn-primary" data-bs-toggle="modal" data-bs-target="#createKeyModal">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('translations_manager.actions.add_key') }}
            </button>
        </div>
    </div>

    {{-- Premium Statistics Widget --}}
    <div class="row g-4 mb-4">
        <!-- Total Keys Card -->
        <div class="col-6 col-md-3">
            <div class="quran-stat-card quran-stat-primary" style="padding: 1rem;">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label text-uppercase fw-semibold small text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Keys</div>
                        <div class="quran-stat-value fw-bold my-1" style="font-size: 1.5rem;">{{ number_format($reportStats['total_keys'] ?? 0) }}</div>
                        <span class="quran-stat-sub small text-muted">Registered keys</span>
                    </div>
                    <div class="quran-stat-icon" style="font-size: 1.5rem;">
                        <i class="bi bi-key-fill text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Missing Keys Card -->
        <div class="col-6 col-md-3">
            <div class="quran-stat-card @if(($reportStats['missing_translations'] ?? 0) > 0) quran-stat-warning @else quran-stat-success @endif" style="padding: 1rem;">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label text-uppercase fw-semibold small text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px;">Missing Units</div>
                        <div class="quran-stat-value fw-bold my-1" style="font-size: 1.5rem;">{{ number_format($reportStats['missing_translations'] ?? 0) }}</div>
                        <span class="quran-stat-sub small text-muted">Requires translation</span>
                    </div>
                    <div class="quran-stat-icon" style="font-size: 1.5rem;">
                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Coverage Card -->
        <div class="col-6 col-md-3">
            <div class="quran-stat-card quran-stat-success" style="padding: 1rem;">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label text-uppercase fw-semibold small text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px;">Coverage</div>
                        <div class="quran-stat-value fw-bold my-1" style="font-size: 1.5rem;">{{ number_format($reportStats['coverage_percentage'] ?? 100.0, 2) }}%</div>
                        <span class="quran-stat-sub small text-muted">UI coverage rate</span>
                    </div>
                    <div class="quran-stat-icon" style="font-size: 1.5rem;">
                        <i class="bi bi-patch-check-fill text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Languages Count Card -->
        <div class="col-6 col-md-3">
            <div class="quran-stat-card quran-stat-info" style="padding: 1rem;">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label text-uppercase fw-semibold small text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px;">Last Scan</div>
                        <div class="quran-stat-value fw-bold my-1 text-truncate" style="font-size: 0.95rem;" title="{{ $lastScan }}">{{ $lastScan }}</div>
                        <span class="quran-stat-sub small text-muted">{{ $reportStats['active_languages_count'] ?? 0 }} active languages</span>
                    </div>
                    <div class="quran-stat-icon" style="font-size: 1.5rem;">
                        <i class="bi bi-globe text-info"></i>
                    </div>
                </div>
            </div>
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

    {{-- Filter Card --}}
    <div class="quran-card p-4 mb-4">
        <form method="GET" action="{{ route('translations-manager.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="search" class="form-label fw-semibold text-muted small text-uppercase">{{ __('translations_manager.filter.search_label') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           class="form-control border-start-0" 
                           placeholder="{{ __('translations_manager.filter.search_placeholder') }}" 
                           value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-12 col-md-3">
                <label for="group" class="form-label fw-semibold text-muted small text-uppercase">{{ __('translations_manager.filter.group_label') }}</label>
                <select id="group" name="group" class="form-select">
                    <option value="">{{ __('translations_manager.filter.all_groups') }}</option>
                    @foreach($groups as $g)
                        <option value="{{ $g }}" {{ request('group') === $g ? 'selected' : '' }}>{{ ucfirst($g) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="missing" 
                           name="missing" 
                           value="1" 
                           {{ request('missing') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold text-muted small text-uppercase" for="missing">
                        {{ __('translations_manager.filter.show_missing') }}
                    </label>
                </div>
            </div>

            <div class="col-12 col-md-2 d-grid gap-2">
                <button type="submit" class="quran-btn quran-btn-outline-primary">
                    {{ __('common.apply_filters') }}
                </button>
                @if(request()->anyFilled(['search', 'group', 'missing']))
                    <a href="{{ route('translations-manager.index') }}" class="btn btn-light btn-sm text-center">
                        {{ __('common.clear_filters') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table Card --}}
    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table align-middle">
                <thead>
                    <tr>
                        <th style="min-width: 250px;">Key &amp; Description</th>
                        @foreach($languages as $lang)
                            <th style="min-width: 200px;">
                                <div class="d-flex align-items-center gap-2">
                                    <span>{{ $lang->flag }}</span>
                                    <span>{{ $lang->name }}</span>
                                    <span class="badge bg-light text-dark border font-monospace small" style="font-size: 10px;">
                                        {{ strtoupper($lang->code) }}
                                    </span>
                                </div>
                            </th>
                        @endforeach
                        <th class="text-end" style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($keys as $k)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark font-monospace text-break" style="font-size: 0.9rem;">
                                    {{ $k->key }}
                                </div>
                                <div class="small text-muted mb-1">
                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2" style="font-size: 10px;">
                                        {{ $k->group }}
                                    </span>
                                </div>
                                @if($k->description)
                                    <div class="small text-muted text-truncate" style="max-width: 240px;" title="{{ $k->description }}">
                                        {{ $k->description }}
                                    </div>
                                @endif
                            </td>
                            @foreach($languages as $lang)
                                @php
                                    $trans = $k->translations->where('language_id', $lang->id)->first();
                                    $value = $trans?->value;
                                    $isAuto = $trans?->is_auto_generated;
                                @endphp
                                <td>
                                    <div class="position-relative">
                                        <textarea 
                                            class="form-control form-control-sm translation-input @if($isAuto) auto-gen-highlight @endif" 
                                            rows="1"
                                            style="resize: vertical; min-height: 38px; font-size: 0.85rem;"
                                            data-key-id="{{ $k->id }}"
                                            data-lang-id="{{ $lang->id }}"
                                            data-original="{{ $value }}"
                                            onblur="saveTranslation(this)"
                                            placeholder="Empty translation..."
                                        >{{ $value }}</textarea>
                                        
                                        @if($isAuto)
                                            <span class="position-absolute top-0 end-0 translate-middle-y badge rounded-pill bg-warning text-dark font-monospace" 
                                                  style="font-size: 8px; z-index: 5;"
                                                  title="Auto-generated placeholder, editing will mark it as human-verified.">
                                                AUTO
                                            </span>
                                        @endif

                                        @if($trans)
                                            <button type="button" 
                                                    class="position-absolute bottom-0 end-0 btn btn-link p-0 text-muted me-1 mb-1 opacity-50 hover-opacity-100"
                                                    style="font-size: 10px; z-index: 5; text-decoration: none;"
                                                    onclick="showHistory({{ $trans->id }})"
                                                    title="View version history">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                            <td>
                                <div class="quran-table-actions justify-content-end">
                                    <button type="button" 
                                            class="quran-table-action-btn delete"
                                            onclick="confirmDelete({{ $k->id }}, '{{ addslashes($k->key) }}')"
                                            title="Delete translation key">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $languages->count() + 2 }}">
                                <div class="quran-table-empty py-5">
                                    <i class="bi bi-translate text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="mt-3">{{ __('translations_manager.empty.title') }}</h6>
                                    <p>{{ __('translations_manager.empty.description') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($keys->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    Showing <strong>{{ $keys->firstItem() }}</strong> to <strong>{{ $keys->lastItem() }}</strong> of <strong>{{ $keys->total() }}</strong> keys
                </div>
                <div class="quran-pagination">
                    {{ $keys->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createKeyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('translations-manager.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle-fill text-primary me-2"></i>
                        {{ __('translations_manager.modals.add_key_title') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="new_key" class="form-label fw-semibold">Translation Key</label>
                            <input type="text" 
                                   id="new_key" 
                                   name="new_key_display" 
                                   class="form-control font-monospace" 
                                   placeholder="e.g. home.welcome_message" 
                                   required
                                   oninput="updateKeyVal(this.value)">
                            <input type="hidden" id="real_key" name="key">
                            <div class="form-text small">Use a dot-notated prefix like <code>group.key_name</code>.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="new_group" class="form-label fw-semibold">Group</label>
                            <input type="text" 
                                   id="new_group" 
                                   name="group" 
                                   class="form-control" 
                                   placeholder="e.g. home, auth, menu" 
                                   readonly 
                                   required>
                            <div class="form-text small">Determined automatically from your key's prefix.</div>
                        </div>

                        <div class="col-12">
                            <label for="new_description" class="form-label fw-semibold">Description / Context (Optional)</label>
                            <textarea id="new_description" 
                                      name="description" 
                                      class="form-control" 
                                      rows="2" 
                                      placeholder="Provide context for translators about where this key is used."></textarea>
                        </div>

                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="fw-bold mb-3 text-muted">Initial Translations (Optional)</h6>
                            <div class="row g-3">
                                @foreach($languages as $lang)
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold d-flex align-items-center gap-2">
                                            <span>{{ $lang->flag }}</span>
                                            <span>{{ $lang->name }} ({{ strtoupper($lang->code) }})</span>
                                        </label>
                                        <input type="text" 
                                               name="translations[{{ $lang->id }}]" 
                                               class="form-control" 
                                               placeholder="Type translation value...">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="quran-btn quran-btn-primary">{{ __('translations_manager.modals.create_key_btn') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Key Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ __('translations_manager.modals.confirm_delete_title') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('translations_manager.modals.confirm_delete_body') }}</p>
                <div class="alert alert-danger font-monospace py-2" id="deleteKeyLabel"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="quran-btn quran-btn-danger">
                        <i class="bi bi-trash me-1"></i> {{ __('translations_manager.modals.delete_permanent_btn') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Import / Export Modal --}}
<div class="modal fade" id="importExportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-download text-primary me-2"></i>
                    {{ __('translations_manager.modals.import_export_title') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Export section -->
                <form action="{{ route('translations-manager.export') }}" method="POST" class="mb-4">
                    @csrf
                    <h6 class="fw-bold text-dark mb-2">Export Translations</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <select name="locale" class="form-select form-select-sm" required>
                                @foreach($languages as $lang)
                                    <option value="{{ $lang->code }}">{{ $lang->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <select name="format" class="form-select form-select-sm" required>
                                <option value="json">JSON Format</option>
                                <option value="csv">CSV Format</option>
                            </select>
                        </div>
                        <div class="col-12 d-grid mt-2">
                            <button type="submit" class="quran-btn quran-btn-primary btn-sm">
                                <i class="bi bi-cloud-arrow-down me-1"></i> Export Translations
                            </button>
                        </div>
                    </div>
                </form>

                <hr>

                <!-- Import section -->
                <form action="{{ route('translations-manager.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <h6 class="fw-bold text-dark mb-2">Import Translations</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Select Target Language</label>
                        <select name="locale" class="form-select form-select-sm" required>
                            @foreach($languages as $lang)
                                <option value="{{ $lang->code }}">{{ $lang->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Upload JSON/CSV File</label>
                        <input type="file" name="file" class="form-control form-control-sm" accept=".json,.csv" required>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="create_keys" value="1" id="create_keys_check">
                        <label class="form-check-label small text-muted text-wrap" for="create_keys_check">
                            Create translation keys if they don't exist in database
                        </label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="quran-btn quran-btn-outline-primary btn-sm">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Start Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Version History Modal --}}
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history text-primary me-2"></i>
                    {{ __('translations_manager.modals.history_title') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 p-3 bg-light rounded">
                    <div><span class="fw-semibold text-muted small text-uppercase">{{ __('translations_manager.modals.translation_key_label') }}:</span> <code id="history-key-name" class="fs-6 text-dark font-monospace text-break"></code></div>
                    <div class="mt-1"><span class="fw-semibold text-muted small text-uppercase">{{ __('translations_manager.modals.language_locale_label') }}:</span> <span id="history-lang-name" class="fw-bold"></span></div>
                </div>
                <div id="history-loading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 small text-muted">{{ __('translations_manager.modals.loading_history') }}</span>
                </div>
                <div class="timeline" id="history-timeline" style="max-height: 400px; overflow-y: auto; display: none;">
                    {{-- Timeline items will be loaded here dynamically --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">{{ __('common.close') }}</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Timeline styles */
    .timeline {
        position: relative;
        padding-left: 20px;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    /* Hover opacity classes for actions */
    .hover-opacity-100 {
        opacity: 0.5;
        transition: opacity 0.2s ease;
    }
    .hover-opacity-100:hover {
        opacity: 1 !important;
    }

    /* Styling for premium inline saving animations */
    .translation-input {
        transition: all 0.3s ease;
        border-color: rgba(0,0,0,0.1);
    }
    
    .translation-input:focus {
        border-color: var(--quran-primary);
        box-shadow: 0 0 0 3px rgba(27,115,64,0.15);
    }

    .auto-gen-highlight {
        border-left: 3px solid #f59e0b;
        background-color: rgba(245, 158, 11, 0.02);
    }

    /* Visual save states */
    .is-saving {
        border-color: #f59e0b !important;
        background-color: rgba(245, 158, 11, 0.05) !important;
        background-image: linear-gradient(45deg, rgba(0, 0, 0, 0.05) 25%, transparent 25%, transparent 50%, rgba(0, 0, 0, 0.05) 50%, rgba(0, 0, 0, 0.05) 75%, transparent 75%, transparent);
        background-size: 1rem 1rem;
        animation: progress-bar-stripes 1s linear infinite;
    }

    .is-saved {
        border-color: #10b981 !important;
        background-color: rgba(16, 185, 129, 0.08) !important;
    }

    .is-invalid {
        border-color: #ef4444 !important;
        background-color: rgba(239, 68, 68, 0.08) !important;
    }

    @keyframes progress-bar-stripes {
        0% { background-position-x: 1rem; }
    }
</style>
@endsection

@push('scripts')
<script>
    function updateKeyVal(val) {
        document.getElementById('real_key').value = val;
        
        // Extract group name automatically from key prefix
        const parts = val.split('.');
        const groupInput = document.getElementById('new_group');
        if (parts.length > 1) {
            groupInput.value = parts[0];
        } else {
            groupInput.value = 'general';
        }
    }

    function saveTranslation(textarea) {
        const keyId = textarea.getAttribute('data-key-id');
        const langId = textarea.getAttribute('data-lang-id');
        const value = textarea.value;
        const originalValue = textarea.getAttribute('data-original');

        if (value === originalValue) {
            return;
        }

        textarea.classList.add('is-saving');

        axios.put("{{ route('translations-manager.update-inline') }}", {
            translation_key_id: keyId,
            language_id: langId,
            value: value
        })
        .then(response => {
            textarea.classList.remove('is-saving');
            textarea.classList.add('is-saved');
            textarea.setAttribute('data-original', value);
            
            // If it was highlighted as AUTO-generated, remove it
            const parent = textarea.parentElement;
            const badge = parent.querySelector('.badge');
            if (badge) {
                badge.remove();
            }
            textarea.classList.remove('auto-gen-highlight');

            setTimeout(() => {
                textarea.classList.remove('is-saved');
            }, 1000);
            
            // Use local config toast notification
            if (window.showToast) {
                window.showToast('Translation updated successfully!', 'success');
            }
        })
        .catch(error => {
            textarea.classList.remove('is-saving');
            textarea.classList.add('is-invalid');
            setTimeout(() => {
                textarea.classList.remove('is-invalid');
            }, 2000);
            
            if (window.showToast) {
                window.showToast('Failed to update translation.', 'error');
            }
            console.error(error);
        });
    }

    function confirmDelete(id, key) {
        document.getElementById('deleteForm').action =
            "{{ route('translations-manager.destroy', ':id') }}".replace(':id', id);
        document.getElementById('deleteKeyLabel').textContent = key;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function showHistory(translationId) {
        const loading = document.getElementById('history-loading');
        const timeline = document.getElementById('history-timeline');
        
        loading.style.display = 'block';
        timeline.style.display = 'none';
        timeline.innerHTML = '';
        
        new bootstrap.Modal(document.getElementById('historyModal')).show();
        
        axios.get("{{ route('translations-manager.history', ':id') }}".replace(':id', translationId))
            .then(response => {
                loading.style.display = 'none';
                timeline.style.display = 'block';
                
                const data = response.data;
                document.getElementById('history-key-name').textContent = data.translation.key.key;
                document.getElementById('history-lang-name').textContent = data.translation.language.name + ' (' + data.translation.language.code.toUpperCase() + ')';
                
                if (data.versions.length === 0) {
                    timeline.innerHTML = '<div class="text-center py-4 text-muted small">No edit version history exists for this translation cell yet.</div>';
                    return;
                }
                
                data.versions.forEach(v => {
                    const rollbackUrl = "{{ route('translations-manager.rollback', ':id') }}".replace(':id', v.id);
                    const userName = v.user ? v.user.name : 'System';
                    const dateStr = new Date(v.created_at).toLocaleString();
                    const oldVal = v.old_value || '[empty]';
                    const newVal = v.new_value || '[empty]';
                    
                    timeline.innerHTML += `
                        <div class="timeline-item border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark small">
                                    <span class="badge bg-secondary-subtle text-secondary me-2 font-monospace">Version #${v.id}</span>
                                    by ${userName} (${v.change_source})
                                </span>
                                <span class="text-muted small font-monospace">${dateStr}</span>
                            </div>
                            <div class="p-2 bg-light rounded text-break mb-2 small font-monospace">
                                <span class="text-danger-emphasis">${oldVal}</span>
                                <i class="bi bi-arrow-right mx-2 text-muted"></i>
                                <span class="text-success-emphasis fw-bold">${newVal}</span>
                            </div>
                            <form action="${rollbackUrl}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2 small" style="font-size: 11px;">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Rollback to this state
                                </button>
                            </form>
                        </div>
                    `;
                });
            })
            .catch(error => {
                loading.style.display = 'none';
                timeline.style.display = 'block';
                timeline.innerHTML = '<div class="text-center py-4 text-danger small">Failed to load version logs. Please try again.</div>';
                console.error(error);
            });
    }
</script>
@endpush
