{{-- resources/views/translations/index.blade.php --}}
@extends('layouts.app')

@section('title', __('translations.titles.index'))
@section('page-title', __('translations.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('translations.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('translations.titles.index') }}</h1>
            <div class="text-muted">{{ __('translations.hints.manage') }}</div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-arrow-up me-1"></i>
                Import JSON
            </button>
            <button type="button" class="quran-btn quran-btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                <i class="bi bi-code-slash me-1"></i>
                Example JSON
            </button>
            <a href="{{ route('translations.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('translations.actions.create') }}
            </a>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('translations.total_translations') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_translations']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-translate"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('translations.total_languages') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_languages'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-globe"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('translations.default_translations') }}</div>
                        <div class="quran-stat-value">{{ $stats['default_translations'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Search Section -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('translations.index') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="quran-form-label">{{ __('translations.filter_by_language') }}</label>
                        <select name="language_code" class="quran-form-select">
                            <option value="">{{ __('translations.all_languages') }}</option>
                            @foreach($languages as $code => $name)
                            <option value="{{ $code }}" {{ request('language_code') == $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('translations.filter_by_surah') }}</label>
                        <select name="surah_id" class="quran-form-select">
                            <option value="">{{ __('translations.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                {{ $surah->number }}. {{ $surah->name_ar }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="quran-form-label">{{ __('translations.filter_by_translator') }}</label>
                        <select name="translator" class="quran-form-select">
                            <option value="">{{ __('translations.all_translators') }}</option>
                            @foreach($translators as $translator)
                            <option value="{{ $translator }}" {{ request('translator') == $translator ? 'selected' : '' }}>
                                {{ $translator }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('translations.search') }}</label>
                        <input type="text" name="search" class="quran-form-control" 
                               placeholder="{{ __('translations.search_placeholder') }}" 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="quran-btn quran-btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>
                            {{ __('common.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Translations Table -->
    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th class="number-column">#</th>
                        <th>{{ __('translations.fields.surah_ayah') }}</th>
                        <th>{{ __('translations.fields.language') }}</th>
                        <th>{{ __('translations.fields.translator') }}</th>
                        <th>{{ __('translations.fields.content') }}</th>
                        <th>{{ __('translations.fields.is_default') }}</th>
                        <th>{{ __('translations.fields.status') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($translations as $index => $translation)
                    <tr>
                        <td class="number-column">{{ $translations->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $translation->ayah->surah->name_ar }}</div>
                            <small class="text-muted">
                                {{ __('translations.ayah') }} {{ $translation->ayah->ayah_number }}
                            </small>
                        </td>
                        <td>
                            <span class="quran-table-badge info">
                                {{ $languages[$translation->language_code] ?? $translation->language_code }}
                            </span>
                        </td>
                        <td>
                            {{ $translation->translator_name ?: '—' }}
                        </td>
                        <td>
                            <div style="max-width: 300px;">
                                {{ Str::limit($translation->content, 80) }}
                            </div>
                        </td>
                        <td>
                            @if($translation->is_default)
                            <span class="quran-table-badge success">
                                <i class="bi bi-star-fill"></i> {{ __('translations.default') }}
                            </span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($translation->is_active)
                            <span class="quran-table-badge success">{{ __('common.active') }}</span>
                            @else
                            <span class="quran-table-badge danger">{{ __('common.inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="quran-table-actions justify-content-end">
                                <a href="{{ route('translations.show', $translation) }}" 
                                   class="quran-table-action-btn view" 
                                   data-bs-toggle="tooltip" 
                                   title="{{ __('common.view') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('translations.edit', $translation) }}" 
                                   class="quran-table-action-btn edit" 
                                   data-bs-toggle="tooltip" 
                                   title="{{ __('common.edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" 
                                        class="quran-table-action-btn delete" 
                                        data-bs-toggle="tooltip" 
                                        title="{{ __('common.delete') }}"
                                        onclick="confirmDelete({{ $translation->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="quran-table-empty">
                                <i class="bi bi-translate"></i>
                                <h6>{{ __('translations.no_translations_found') }}</h6>
                                <p>{{ __('translations.no_translations_message') }}</p>
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('translations.create') }}" class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    {{ __('translations.actions.create_first') }}
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($translations->hasPages())
        <div class="card-footer">
            <div class="quran-pagination">
                {{ $translations->links() }}
            </div>
            <div class="pagination-info">
                {{ __('common.showing') }} 
                <strong>{{ $translations->firstItem() }}</strong> 
                {{ __('common.to') }} 
                <strong>{{ $translations->lastItem() }}</strong> 
                {{ __('common.of') }} 
                <strong>{{ $translations->total() }}</strong> 
                {{ __('translations.translations') }}
            </div>
        </div>
        @endif
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
                <p>{{ __('translations.delete_confirm_message') }}</p>
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
{{-- Import JSON Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="importModalLabel">Import JSON File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('translations.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="json_file" class="form-label">Select .json file to import</label>
                        <input type="file" class="form-control" id="json_file" name="file" accept=".json" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="quran-btn quran-btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Example JSON Modal --}}
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="exampleModalLabel">Example JSON Format</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>The JSON file must be an array of objects structured as shown below:</p>
                <div class="bg-dark text-light p-3 rounded-3" style="max-height: 400px; overflow-y: auto;">
                    <pre><code class="text-info">[
  {
    "surah_number": 1,
    "ayah_number": 1,
    "language_code": "ku",
    "translator_name": "Hazhar",
    "content": "بە ناوی خوای بەخشندەی میهرەبان",
    "is_default": true,
    "is_active": true
  }
]</code></pre>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    const form = document.getElementById('deleteForm');
    form.action = "{{ route('translations.destroy', ':id') }}".replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush