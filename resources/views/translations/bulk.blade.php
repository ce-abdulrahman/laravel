@extends('layouts.app')

@section('title', 'Bulk Translation Editor')
@section('page-title', 'Bulk Translation Editor')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('translations-manager.index') }}">Translation Manager</a></li>
    <li class="breadcrumb-item active" aria-current="page">Bulk Editor</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Bulk Translation Editor</h1>
            <div class="text-muted">Perform mass updates, delete multiple keys, or dispatch bulk AI translations in the background.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('translations-manager.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Manager
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

    {{-- Bulk Operations Card --}}
    <div class="quran-card p-4 mb-4 bg-light border-start border-primary border-4">
        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Bulk Actions for Selected Keys</h6>
        <div class="d-flex flex-wrap gap-3 align-items-center">
            <div class="d-flex align-items-center">
                <input class="form-check-input me-2" type="checkbox" id="selectAllKeys" style="width: 18px; height: 18px;">
                <label class="form-check-label small fw-semibold text-muted text-uppercase" for="selectAllKeys">Select All</label>
            </div>
            
            <div class="vr text-muted d-none d-md-block" style="height: 25px;"></div>
            
            <button type="button" class="quran-btn quran-btn-danger btn-sm" onclick="bulkDelete()">
                <i class="bi bi-trash-fill me-1"></i> Delete Selected
            </button>
            
            <div class="vr text-muted d-none d-md-block" style="height: 25px;"></div>
            
            <div class="d-flex align-items-center gap-2">
                <select id="aiLocale" class="form-select form-select-sm" style="width: 180px;">
                    <option value="">AI Target Language...</option>
                    @foreach($languages as $lang)
                        <option value="{{ $lang->code }}">{{ $lang->name }} ({{ strtoupper($lang->code) }})</option>
                    @endforeach
                </select>
                <button type="button" class="quran-btn quran-btn-primary btn-sm" onclick="bulkGenerateAI()">
                    <i class="bi bi-cpu-fill me-1"></i> Translate with AI
                </button>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="quran-card p-4 mb-4">
        <form method="GET" action="{{ route('translations-manager.bulk') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label for="search" class="form-label fw-semibold text-muted small text-uppercase">Search Keys</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="search" name="search" class="form-control border-start-0" placeholder="Search translation keys..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-12 col-md-4">
                <label for="group" class="form-label fw-semibold text-muted small text-uppercase">Filter by Group</label>
                <select id="group" name="group" class="form-select">
                    <option value="">All Groups</option>
                    @foreach($groups as $g)
                        <option value="{{ $g }}" {{ request('group') === $g ? 'selected' : '' }}>{{ ucfirst($g) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3 d-grid gap-2">
                <button type="submit" class="quran-btn quran-btn-outline-primary">
                    Apply Filters
                </button>
                @if(request()->anyFilled(['search', 'group']))
                    <a href="{{ route('translations-manager.bulk') }}" class="btn btn-light btn-sm text-center">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Grid Matrix Form --}}
    <form action="{{ route('translations-manager.bulk-update') }}" method="POST">
        @csrf
        <div class="quran-card">
            <div class="quran-table-container">
                <table class="quran-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 40px;">Select</th>
                            <th style="min-width: 200px;">Key</th>
                            @foreach($languages as $lang)
                                <th style="min-width: 250px;">
                                    {{ $lang->flag }} {{ $lang->name }} ({{ strtoupper($lang->code) }})
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($keys as $k)
                            <tr>
                                <td>
                                    <input class="form-check-input key-checkbox" type="checkbox" value="{{ $k->id }}" style="width: 18px; height: 18px;">
                                </td>
                                <td>
                                    <div class="fw-bold font-monospace text-dark text-break small">{{ $k->key }}</div>
                                    <span class="badge bg-secondary-subtle text-secondary border font-monospace mt-1" style="font-size: 9px;">
                                        {{ $k->group }}
                                    </span>
                                </td>
                                @foreach($languages as $lang)
                                    @php
                                        $trans = $k->translations->where('language_id', $lang->id)->first();
                                        $value = $trans?->value;
                                    @endphp
                                    <td>
                                        <input type="text" 
                                               name="translations[{{ $k->id }}][{{ $lang->id }}]" 
                                               class="form-control form-control-sm" 
                                               value="{{ $value }}"
                                               placeholder="Translation value...">
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $languages->count() + 2 }}" class="text-center py-5 text-muted">
                                    <i class="bi bi-grid-3x3-gap text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="mt-3">No Keys Found</h6>
                                    <p>Try clearing your filters or select a different group.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="quran-table-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div>
                    @if($keys->hasPages())
                        Showing <strong>{{ $keys->firstItem() }}</strong> to <strong>{{ $keys->lastItem() }}</strong> of <strong>{{ $keys->total() }}</strong> keys
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-save me-1"></i> Save All Changes on Page
                    </button>
                </div>
            </div>
            
            @if($keys->hasPages())
                <div class="p-3 border-top bg-light">
                    {{ $keys->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAllKeys');
        const checkboxes = document.querySelectorAll('.key-checkbox');

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });
    });

    function getSelectedKeys() {
        const checked = document.querySelectorAll('.key-checkbox:checked');
        return Array.from(checked).map(cb => cb.value);
    }

    function bulkDelete() {
        const keys = getSelectedKeys();
        if (keys.length === 0) {
            alert('Please select at least one key to delete.');
            return;
        }

        if (!confirm('Are you sure you want to permanently delete the ' + keys.length + ' selected keys and all their translations? This cannot be undone.')) {
            return;
        }

        axios.post("{{ route('translations-manager.bulk-delete') }}", { keys: keys })
            .then(response => {
                if (window.showToast) {
                    window.showToast(response.data.message, 'success');
                } else {
                    alert(response.data.message);
                }
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(error => {
                alert('Deletion failed: ' + (error.response?.data?.message || error.message));
            });
    }

    function bulkGenerateAI() {
        const keys = getSelectedKeys();
        const locale = document.getElementById('aiLocale').value;

        if (keys.length === 0) {
            alert('Please select at least one key for AI translation.');
            return;
        }

        if (!locale) {
            alert('Please select a target language locale.');
            return;
        }

        axios.post("{{ route('translations-manager.bulk-generate-ai') }}", { keys: keys, locale: locale })
            .then(response => {
                if (window.showToast) {
                    window.showToast(response.data.message, 'success');
                } else {
                    alert(response.data.message);
                }
                // Don't reload immediately since it runs in queue
            })
            .catch(error => {
                alert('AI Batch request failed: ' + (error.response?.data?.message || error.message));
            });
    }
</script>
@endpush
