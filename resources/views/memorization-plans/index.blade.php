{{-- resources/views/memorization-plans/index.blade.php --}}
@extends('layouts.app')

@section('title', __('memorization_plans.titles.index'))
@section('page-title', __('memorization_plans.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active">{{ __('memorization_plans.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('memorization_plans.titles.index') }}</h1>
            <div class="text-muted">
                @if(auth()->user()->role === 'admin')
                    {{ __('memorization_plans.hints.manage_all_plans') }}
                @else
                    {{ __('memorization_plans.hints.available_plans') }}
                @endif
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Export Dropdown -->
            <div class="dropdown d-inline-block">
                <button class="quran-btn quran-btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="exportDropdown">
                    <li><a class="dropdown-item py-2" href="{{ route('memorization-plans.export', 'json') }}"><i class="bi bi-filetype-json me-2 text-danger"></i>JSON Format</a></li>
                    <li><a class="dropdown-item py-2" href="{{ route('memorization-plans.export', 'csv') }}"><i class="bi bi-filetype-csv me-2 text-success"></i>CSV Format</a></li>
                </ul>
            </div>

            <!-- Import Button -->
            <button type="button" class="quran-btn quran-btn-outline-primary" id="btn-trigger-import">
                <i class="bi bi-upload me-1"></i> Import
            </button>
            <input type="file" id="import-file-input" accept=".json,.csv" style="display: none;">

            @if(auth()->user()->role === 'admin')
            <a href="{{ route('memorization-plans.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('memorization_plans.actions.create') }}
            </a>
            @endif
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
                The following plans will be imported. Please review them before proceeding. Total plans: <strong id="preview-record-count">0</strong>
            </p>
            
            <div class="quran-table-container mb-4" style="max-height: 300px; overflow-y: auto;">
                <table class="quran-table quran-table-striped" id="import-preview-table">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Plan Title</th>
                            <th>Type</th>
                            <th>Target Rate</th>
                            <th>Start Date</th>
                            <th>Day Items</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="quran-btn quran-btn-outline-primary" id="btn-cancel-import-2">Cancel</button>
                <button type="button" class="quran-btn quran-btn-primary" id="btn-confirm-import">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Confirm and Import Plans
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
                        <div class="quran-stat-label">{{ __('memorization_plans.total_plans') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_plans'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-calendar-range"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.active_plans') }}</div>
                        <div class="quran-stat-value">{{ $stats['active_plans'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-play-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-info">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.completed_plans') }}</div>
                        <div class="quran-stat-value">{{ $stats['completed_plans'] ?? 0 }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        @if(auth()->user()->role === 'admin')
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.total_users') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_users'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="col-md-3">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('memorization_plans.my_progress') }}</div>
                        <div class="quran-stat-value">
                            {{ auth()->user()->memorizationReviews()->whereDate('created_at', today())->count() }}
                        </div>
                        <div class="quran-stat-sub">{{ __('memorization_plans.today') }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Filter - تەنها بۆ ئەدمین -->
    @if(auth()->user()->role === 'admin')
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('memorization-plans.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('memorization_plans.filter_by_status') }}</label>
                        <select name="status" class="quran-form-select">
                            <option value="">{{ __('memorization_plans.all_statuses') }}</option>
                            @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('memorization_plans.filter_by_type') }}</label>
                        <select name="plan_type" class="quran-form-select">
                            <option value="">{{ __('memorization_plans.all_types') }}</option>
                            @foreach($planTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('plan_type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="quran-btn quran-btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Plans Grid -->
    <div class="row g-4">
        @forelse($plans as $plan)
        <div class="col-md-6 col-lg-4">
            <div class="quran-plan-card">
                <div class="quran-plan-header">
                    <div class="quran-plan-icon">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div class="quran-plan-info">
                        <h6>{{ $plan->title }}</h6>
                        <span class="quran-plan-badge {{ $plan->status }}">
                            {{ $statuses[$plan->status] ?? $plan->status }}
                        </span>
                        @if(auth()->user()->role === 'admin')
                        <small class="text-muted d-block">{{ $plan->user->name ?? 'Unknown' }}</small>
                        @endif
                    </div>
                </div>

                <div class="quran-plan-progress">
                    @php
                        $totalItems = $plan->items_count;
                        $completedItems = $plan->items->count();
                        $progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
                    @endphp
                    <div class="quran-plan-stats">
                        <span>{{ $completedItems }}/{{ $totalItems }} {{ __('memorization_plans.days') }}</span>
                        <span>{{ $progress }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                <div class="quran-plan-footer">
                    <div class="quran-plan-next">
                        <small>{{ $plan->start_date->format('Y-m-d') }}</small>
                    </div>
                    <div class="quran-table-actions">
                        <a href="{{ route('memorization-plans.show', $plan) }}" 
                           class="quran-table-action-btn view">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if(auth()->user()->role === 'admin')
                        <a href="{{ route('memorization-plans.edit', $plan) }}" 
                           class="quran-table-action-btn edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="quran-table-empty">
                <i class="bi bi-calendar-range"></i>
                <h6>{{ __('memorization_plans.no_plans') }}</h6>
                <p>
                    @if(auth()->user()->role === 'admin')
                        {{ __('memorization_plans.no_plans_message_admin') }}
                    @else
                        {{ __('memorization_plans.no_plans_message_user') }}
                    @endif
                </p>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('memorization-plans.create') }}" class="quran-btn quran-btn-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('memorization_plans.actions.create_first') }}
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    @if($plans->hasPages())
    <div class="mt-4">
        {{ $plans->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
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

    let parsedPlans = [];

    btnTriggerImport.addEventListener('click', () => {
        importFileInput.click();
    });

    importFileInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (evt) {
            try {
                const text = evt.target.result;
                if (file.name.endsWith('.json')) {
                    parsedPlans = parseJSON(text);
                } else if (file.name.endsWith('.csv')) {
                    parsedPlans = parseCSV(text);
                } else {
                    alert('Unsupported file format. Please upload .json or .csv');
                    return;
                }

                if (parsedPlans.length === 0) {
                    alert('No valid plan records found in this file.');
                    return;
                }

                // Show preview
                previewFileName.textContent = file.name;
                previewRecordCount.textContent = parsedPlans.length;
                importPreviewTableBody.innerHTML = '';

                parsedPlans.forEach(plan => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong>${escapeHtml(plan.title)}</strong></td>
                        <td><span class="quran-table-badge info">${escapeHtml(plan.plan_type)}</span></td>
                        <td>${escapeHtml(plan.daily_target_value)} ${escapeHtml(plan.daily_target_type)}</td>
                        <td>${escapeHtml(plan.start_date)}</td>
                        <td><span class="badge bg-secondary">${plan.items.length} days</span></td>
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

    const closePreview = () => {
        importPreviewCard.style.display = 'none';
        importFileInput.value = '';
        parsedPlans = [];
    };

    btnCancelImport.addEventListener('click', closePreview);
    btnCancelImport2.addEventListener('click', closePreview);

    btnConfirmImport.addEventListener('click', function () {
        if (parsedPlans.length === 0) return;

        btnConfirmImport.disabled = true;
        btnConfirmImport.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Importing...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('memorization-plans.import') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ plans: parsedPlans })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Plans imported successfully!');
                window.location.reload();
            } else {
                alert('Import failed: ' + (data.message || 'Unknown error'));
                btnConfirmImport.disabled = false;
                btnConfirmImport.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i> Confirm and Import Plans';
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred during import.');
            btnConfirmImport.disabled = false;
            btnConfirmImport.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-1"></i> Confirm and Import Plans';
        });
    });

    function parseCSV(text) {
        const lines = text.split(/\r?\n/).filter(line => line.trim() !== '');
        if (lines.length < 2) return [];
        
        const headers = lines[0].split(',').map(h => h.replace(/^["']|["']$/g, '').trim().toLowerCase());
        const plansMap = {};
        
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
            
            if (!row.title) continue;

            const titleKey = row.title.trim();

            if (!plansMap[titleKey]) {
                plansMap[titleKey] = {
                    title: row.title,
                    plan_type: row.plan_type || 'custom',
                    start_date: row.start_date || new Date().toISOString().split('T')[0],
                    target_end_date: row.target_end_date || null,
                    daily_target_type: row.daily_target_type || 'ayahs',
                    daily_target_value: parseInt(row.daily_target_value) || 1,
                    status: row.plan_status || 'active',
                    notes: row.notes || null,
                    items: []
                };
            }

            // Add item if present
            if (row.surah_id && row.from_ayah_id) {
                plansMap[titleKey].items.push({
                    surah_id: parseInt(row.surah_id),
                    from_ayah_id: parseInt(row.from_ayah_id),
                    to_ayah_id: parseInt(row.to_ayah_id || row.from_ayah_id),
                    day_number: parseInt(row.day_number) || 1,
                    target_date: row.item_target_date || row.start_date,
                    status: row.item_status || 'pending'
                });
            }
        }
        return Object.values(plansMap);
    }

    function parseJSON(text) {
        const raw = JSON.parse(text);
        const results = [];
        if (Array.isArray(raw)) {
            raw.forEach(row => {
                if (row.title) {
                    const plan = {
                        title: row.title,
                        plan_type: row.plan_type || 'custom',
                        start_date: row.start_date || new Date().toISOString().split('T')[0],
                        target_end_date: row.target_end_date || null,
                        daily_target_type: row.daily_target_type || 'ayahs',
                        daily_target_value: parseInt(row.daily_target_value) || 1,
                        status: row.status || 'active',
                        notes: row.notes || null,
                        items: []
                    };
                    if (Array.isArray(row.items)) {
                        row.items.forEach(item => {
                            if (item.surah_id && item.from_ayah_id) {
                                plan.items.push({
                                    surah_id: parseInt(item.surah_id),
                                    from_ayah_id: parseInt(item.from_ayah_id),
                                    to_ayah_id: parseInt(item.to_ayah_id || item.from_ayah_id),
                                    day_number: parseInt(item.day_number) || 1,
                                    target_date: item.target_date || plan.start_date,
                                    status: item.status || 'pending'
                                });
                            }
                        });
                    }
                    results.push(plan);
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