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

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <!-- Export Dropdown -->
            <div class="dropdown d-inline-block">
                <button class="quran-btn quran-btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item py-2" href="{{ route('memorization-reviews.export', 'json') }}"><i class="bi bi-filetype-json me-2 text-danger"></i>JSON Format</a></li>
                    <li><a class="dropdown-item py-2" href="{{ route('memorization-reviews.export', 'csv') }}"><i class="bi bi-filetype-csv me-2 text-success"></i>CSV Format</a></li>
                </ul>
            </div>

            <!-- Import Button -->
            <button type="button" class="quran-btn quran-btn-outline-primary" id="btn-trigger-import">
                <i class="bi bi-upload me-1"></i> Import
            </button>
            <input type="file" id="import-file-input" accept=".json,.csv" style="display: none;">

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

    <!-- Import Preview Card -->
    <div class="quran-card mb-4 shadow-sm" id="import-preview-card" style="display: none; border: 1px solid rgba(var(--quran-primary-rgb), 0.15);">
        <div class="quran-card-header d-flex align-items-center justify-content-between bg-light py-3">
            <h5 class="quran-card-title text-primary mb-0 fs-6">
                <i class="bi bi-eye-fill me-2"></i>
                Import Preview (<span id="preview-file-name" class="fw-semibold"></span>)
            </h5>
            <button type="button" class="btn-close" id="btn-cancel-import" aria-label="Close"></button>
        </div>
        <div class="quran-card-body p-4">
            <p class="text-muted small mb-3">
                The following reviews will be imported. Please review them before proceeding. Total records: <strong id="preview-record-count">0</strong>
            </p>
            
            <div class="quran-table-container mb-4" style="max-height: 300px; overflow-y: auto;">
                <table class="quran-table quran-table-striped" id="import-preview-table">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Ayah ID</th>
                            <th>Date</th>
                            <th>Level</th>
                            <th>Result</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="quran-btn quran-btn-outline-primary" id="btn-cancel-import-2">Cancel</button>
                <button type="button" class="quran-btn quran-btn-primary" id="btn-confirm-import">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Confirm and Import Reviews
                </button>
            </div>
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
                        <div class="row g-3 justify-content-between align-items-end">
                            <div class="col-md-2">
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
                            <div class="col-md-2 ms-5">
                                <button type="submit" class="quran-btn quran-btn-primary w-75">
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

document.addEventListener('DOMContentLoaded', function () {
    const btnTriggerImport = document.getElementById('btn-trigger-import');
    const importFileInput = document.getElementById('import-file-input');
    const importPreviewCard = document.getElementById('import-preview-card');
    const previewFileName = document.getElementById('preview-file-name');
    const previewRecordCount = document.getElementById('preview-record-count');
    const importPreviewTableBody = document.querySelector('#import-preview-table tbody');
    const btnCancelImport = document.getElementById('btn-cancel-import');
    const btnCancelImport2 = document.getElementById('btn-cancel-import-2');
    const btnConfirmImport = document.getElementById('btn-confirm-import');

    let parsedReviews = [];

    if (btnTriggerImport) {
        btnTriggerImport.addEventListener('click', () => {
            importFileInput.click();
        });
    }

    if (importFileInput) {
        importFileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (evt) {
                try {
                    const text = evt.target.result;
                    if (file.name.endsWith('.json')) {
                        parsedReviews = parseJSON(text);
                    } else if (file.name.endsWith('.csv')) {
                        parsedReviews = parseCSV(text);
                    } else {
                        alert('Unsupported file format. Please upload .json or .csv');
                        return;
                    }

                    if (parsedReviews.length === 0) {
                        alert('No valid review records found in this file.');
                        return;
                    }

                    // Show preview
                    previewFileName.textContent = file.name;
                    previewRecordCount.textContent = parsedReviews.length;
                    importPreviewTableBody.innerHTML = '';

                    parsedReviews.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>Ayah ${escapeHtml(row.ayah_id)}</td>
                            <td>${escapeHtml(row.review_date)}</td>
                            <td><span class="quran-table-badge info">${escapeHtml(row.review_level || '—')}</span></td>
                            <td><span class="quran-table-badge ${escapeHtml(row.result || 'default')}">${escapeHtml(row.result || '—')}</span></td>
                            <td><small class="text-muted">${escapeHtml(row.notes || '—')}</small></td>
                        `;
                        importPreviewTableBody.appendChild(tr);
                    });

                    importPreviewCard.style.display = 'block';
                    importPreviewCard.scrollIntoView({ behavior: 'smooth' });
                } catch (err) {
                    console.error(err);
                    alert('Error parsing file: ' + err.message);
                }
            };
            reader.readAsText(file);
        });
    }

    const closePreview = () => {
        importPreviewCard.style.display = 'none';
        importFileInput.value = '';
        parsedReviews = [];
    };

    if (btnCancelImport) btnCancelImport.addEventListener('click', closePreview);
    if (btnCancelImport2) btnCancelImport2.addEventListener('click', closePreview);

    if (btnConfirmImport) {
        btnConfirmImport.addEventListener('click', function () {
            if (parsedReviews.length === 0) return;

            btnConfirmImport.disabled = true;
            btnConfirmImport.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Importing...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch("{{ route('memorization-reviews.import') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reviews: parsedReviews })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Reviews imported successfully!');
                    window.location.reload();
                } else {
                    alert('Import failed: ' + (data.message || 'Unknown error'));
                    btnConfirmImport.disabled = false;
                    btnConfirmImport.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i> Confirm and Import Reviews';
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred during import.');
                btnConfirmImport.disabled = false;
                btnConfirmImport.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i> Confirm and Import Reviews';
            });
        });
    }

    function parseCSV(text) {
        const lines = text.split(/\r?\n/).filter(line => line.trim() !== '');
        if (lines.length < 2) return [];
        
        const headers = lines[0].split(',').map(h => h.replace(/^["']|["']$/g, '').trim().toLowerCase());
        const results = [];
        
        for (let i = 1; i < lines.length; i++) {
            const line = lines[i];
            const rowValues = [];
            let insideQuote = false;
            let currentValue = '';
            
            for (let j = 0; j < line.length; j++) {
                const char = line[j];
                if (char === '"') {
                    insideQuote = !insideQuote;
                } else if (char === ',' && !insideQuote) {
                    rowValues.push(currentValue.trim());
                    currentValue = '';
                } else {
                    currentValue += char;
                }
            }
            rowValues.push(currentValue.trim());
            
            const row = {};
            headers.forEach((header, index) => {
                let val = rowValues[index] ? rowValues[index].replace(/^["']|["']$/g, '') : null;
                row[header] = val;
            });
            
            if (row.ayah_id) {
                results.push({
                    ayah_id: parseInt(row.ayah_id),
                    review_date: row.review_date || new Date().toISOString().split('T')[0],
                    review_level: row.review_level || null,
                    result: row.result || null,
                    notes: row.notes || null
                });
            }
        }
        return results;
    }

    function parseJSON(text) {
        const raw = JSON.parse(text);
        const results = [];
        if (Array.isArray(raw)) {
            raw.forEach(row => {
                if (row.ayah_id) {
                    results.push({
                        ayah_id: parseInt(row.ayah_id),
                        review_date: row.review_date || new Date().toISOString().split('T')[0],
                        review_level: row.review_level || null,
                        result: row.result || null,
                        notes: row.notes || null
                    });
                }
            });
        }
        return results;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
</script>
@endpush