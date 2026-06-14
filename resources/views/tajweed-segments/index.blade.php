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
        <div class="card-footer">
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('tajweed-segments.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 bg-primary bg-opacity-10">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-upload me-2"></i>Import Segments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted small">Upload a <strong>JSON</strong> or <strong>CSV</strong> file containing generated segments. Existing matching segments will be skipped automatically.</p>
                    <div class="mb-3">
                        <label class="quran-form-label">Choose File</label>
                        <input type="file" name="file" class="form-control" accept=".json,.csv,.txt" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="quran-btn quran-btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="quran-btn quran-btn-primary">Upload & Import</button>
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
function confirmDelete(id) {
    const form = document.getElementById('deleteForm');
    form.action = "{{ route('tajweed-segments.destroy', ':id') }}".replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush