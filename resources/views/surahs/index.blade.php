@extends('layouts.app')

@section('title', __('surah.titles.index'))
@section('page-title', __('surah.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('surah.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('surah.titles.index') }}</h1>
            <div class="text-muted">{{ __('surah.hints.manage') }}</div>
        </div>

        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('surahs.index') }}" class="d-flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="{{ __('surah.search_placeholder') }}"
                >
                <button class="quran-btn quran-btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            @if(auth()->user()?->role === 'admin')
            <button type="button" class="quran-btn quran-btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-arrow-up me-1"></i>
                Import JSON
            </button>
            <button type="button" class="quran-btn quran-btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                <i class="bi bi-code-slash me-1"></i>
                Example JSON
            </button>
            @endif

            <a href="{{ route('surahs.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('surah.actions.create') }}
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="quran-table-container">
        <!-- Table Toolbar -->
        <div class="quran-table-toolbar">
            <div class="quran-table-search">
                <i class="bi bi-search"></i>
                <input type="text"
                       placeholder="{{ __('surah.search_placeholder') }}"
                       id="tableSearch"
                       value="{{ $search }}">
            </div>
            <div class="quran-table-filters">
                <button class="quran-table-filter-btn">
                    <i class="bi bi-funnel"></i>
                    {{ __('common.filter') }}
                </button>
                <button class="quran-table-filter-btn" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i>
                    {{ __('common.refresh') }}
                </button>
            </div>
        </div>

        @php
            $totalColumns = 5 + \App\Models\Language::activeList()->count();
        @endphp

        <div class="table-responsive">
            <table class="quran-table quran-table-striped quran-surah-table">
                <thead>
                    <tr>
                        <th class="number-column">{{ __('surah.fields.number') }}</th>
                        @foreach(\App\Models\Language::activeList() as $lang)
                            <th>Name ({{ $lang->name }})</th>
                        @endforeach
                        <th>{{ __('surah.fields.revelation_type') }}</th>
                        <th class="text-center">{{ __('surah.fields.ayah_count') }}</th>
                        <th class="text-center">{{ __('surah.fields.is_active') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($surahs as $surah)
                        <tr>
                            <td class="number-column text-center">
                                <div class="relative d-inline-flex align-items-center justify-content-center w-9 h-9 select-none">
                                    <svg class="absolute w-full h-full text-emerald-50 dark:text-emerald-950/40 fill-current stroke-emerald-600/50 dark:stroke-emerald-400/40 stroke-1" viewBox="0 0 24 24">
                                        <path d="M12 2L15 5H19V9L22 12L19 15V19H15L12 22L9 19H5V15L2 12L5 9V5H9L12 2Z" />
                                    </svg>
                                    <span class="relative z-10 text-xs font-bold text-emerald-800 dark:text-emerald-300">
                                        {{ $surah->number }}
                                    </span>
                                </div>
                            </td>
                            @foreach(\App\Models\Language::activeList() as $lang)
                                <td>
                                    @php
                                        $val = $surah->getTranslation('name', $lang->code);
                                        $attrs = $surah->getTranslationAttributes('name', $lang->code);
                                    @endphp
                                    @if($val !== null && $val !== '')
                                        <div class="{{ $attrs['class'] }}" dir="{{ $attrs['dir'] }}" style="{{ $attrs['style'] }}">
                                            {{ $val }}
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border small">{{ __('common.missing_translation') ?? 'Missing' }}</span>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                @php
                                    $revelationClass = $surah->revelation_type === 'meccan' ? 'primary' : 'success';
                                @endphp
                                <span class="quran-table-badge {{ $revelationClass }}">
                                    {{ __('surah.revelation_types.' . $surah->revelation_type) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="quran-table-badge info">{{ $surah->ayah_count }}</span>
                            </td>
                            <td class="text-center">
                                @if($surah->is_active)
                                    <span class="quran-table-badge success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ __('surah.status.active') }}
                                    </span>
                                @else
                                    <span class="quran-table-badge danger">
                                        <i class="bi bi-x-circle me-1"></i>
                                        {{ __('surah.status.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="quran-table-actions justify-content-end">
                                    <a href="{{ route('surahs.show', $surah) }}"
                                       class="quran-table-action-btn view"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('surah.actions.view') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('surahs.edit', $surah) }}"
                                       class="quran-table-action-btn edit"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('common.edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('surahs.destroy', $surah) }}"
                                          class="d-inline"
                                          onsubmit="return confirmDelete(event, '{{ $surah->name_ar }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="quran-table-action-btn delete"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('common.delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $totalColumns }}">
                                <div class="quran-table-empty">
                                    <i class="bi bi-journal-x"></i>
                                    <h6>{{ __('common.no_data') }}</h6>
                                    <p>{{ __('surah.messages.no_surahs_found') }}</p>
                                    <a href="{{ route('surahs.create') }}" class="quran-btn quran-btn-primary mt-3">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        {{ __('surah.actions.create_first') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer with Pagination -->
        @if($surahs->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.showing') }}
                    <strong>{{ $surahs->firstItem() }}</strong>
                    {{ __('common.to') }}
                    <strong>{{ $surahs->lastItem() }}</strong>
                    {{ __('common.of') }}
                    <strong>{{ $surahs->total() }}</strong>
                    {{ __('common.surahs') }}
                </div>
                <div class="quran-pagination">
                    {{ $surahs->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @elseif(count($surahs) > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.total') }}:
                    <strong>{{ count($surahs) }}</strong>
                    {{ __('common.surahs') }}
                </div>
            </div>
        @endif
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
            <form action="{{ route('surahs.import') }}" method="POST" enctype="multipart/form-data">
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
                <p>The JSON file must be an array of objects with the Surah table fields plus localized name values:</p>
                <div class="bg-dark text-light p-3 rounded-3" style="max-height: 400px; overflow-y: auto;">
                    <pre><code class="text-info">[
  {
    "number": 1,
    "name_ar": "الفاتحة",
    "name_ku": "الفاتحة",
    "name_en": "Al-Fatihah",
    "revelation_type": "meccan",
    "ayah_count": 7,
    "page_start": 1,
    "page_end": 1,
    "juz_start": 1,
    "juz_end": 1,
    "description": "The opening chapter of the Quran.",
    "is_active": true
  }
]</code></pre>
                </div>
                <p class="mt-3 text-muted small">Required fields: <code>number</code>, <code>name_ar</code>, <code>revelation_type</code>, <code>ayah_count</code>. Optional fields: <code>name_ku</code>, <code>name_en</code>, <code>page_start</code>, <code>page_end</code>, <code>juz_start</code>, <code>juz_end</code>, <code>description</code>, <code>is_active</code>.</p>
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
    // Confirm Delete Function
    function confirmDelete(event, surahName) {
        event.preventDefault();
        const form = event.target;

        if (confirm('{{ __("surah.messages.confirm_delete") }}\n\n' + surahName)) {
            form.submit();
        }
        return false;
    }

    // Table Search Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('tableSearch');
        const urlParams = new URLSearchParams(window.location.search);

        if (searchInput) {
            // Set initial value
            searchInput.value = urlParams.get('q') || '';

            // Handle search
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const query = searchInput.value.trim();
                    if (query) {
                        window.location.href = '{{ route("surahs.index") }}?q=' + encodeURIComponent(query);
                    } else {
                        window.location.href = '{{ route("surahs.index") }}';
                    }
                }
            });
        }

        // Initialize Bootstrap Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                placement: 'top'
            });
        });
    });
</script>
@endpush
