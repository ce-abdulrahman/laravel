{{-- resources/views/ayahs/index.blade.php --}}
@extends('layouts.app')

@section('title', __('ayahs.title_index'))
@section('page-title', __('ayahs.title_index'))
 
@section('breadcrumb')
    <li class="breadcrumb-item active text-zinc-500" aria-current="page">{{ __('ayahs.ayahs') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1 text-zinc-900 dark:text-white font-bold">{{ __('ayahs.title_index') }}</h1>
            <div class="text-zinc-500 dark:text-zinc-400 text-sm font-semibold">{{ __('ayahs.hints.manage') ?? 'Manage Quran verses details and translations' }}</div>
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
            @endif

            @can('create', App\Models\Ayah::class)
            <a href="{{ route('ayahs.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                <span>{{ __('ayahs.add_ayah') }}</span>
            </a>
            @endcan
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('ayahs.total_ayahs') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_ayahs']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-success">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('ayahs.total_surahs') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_surahs'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('ayahs.sajda_ayahs') }}</div>
                        <div class="quran-stat-value">{{ $stats['sajda_ayahs'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter and Search Section --}}
    <div class="quran-card mb-4 border-0 shadow-sm">
        <div class="quran-card-body p-4">
            <form method="GET" action="{{ route('ayahs.index') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold mb-1.5">{{ __('ayahs.filter_by_surah') }}</label>
                        <select name="surah_id" class="quran-form-select">
                            <option value="">{{ __('ayahs.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                {{ $surah->id }}. {{ $surah->name_simple }} ({{ $surah->name_arabic }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold mb-1.5">{{ __('ayahs.filter_by_juz') }}</label>
                        <select name="juz_number" class="quran-form-select">
                            <option value="">{{ __('ayahs.all_juz') }}</option>
                            @foreach($juzNumbers as $juz)
                            <option value="{{ $juz }}" {{ request('juz_number') == $juz ? 'selected' : '' }}>
                                {{ __('ayahs.juz') }} {{ $juz }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-8">
                        <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold mb-1.5">{{ __('ayahs.search') }}</label>
                        <div class="input-group">
                            <input type="text" name="search" class="quran-form-control"
                                   placeholder="{{ __('ayahs.search_placeholder') }}"
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="quran-form-label text-zinc-700 dark:text-zinc-300 font-bold mb-1.5">{{ __('ayahs.per_page') }}</label>
                        <select name="per_page" class="quran-form-select">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-12">
                        <button type="submit" class="quran-btn quran-btn-primary w-100 justify-content-center">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Ayahs Table --}}
    <div class="quran-table-container">
        <table class="quran-table quran-table-striped">
            <thead>
                <tr>
                    <th class="number-column text-center">#</th>
                    <th>{{ __('ayahs.surah') }}</th>
                    <th class="text-center">{{ __('ayahs.ayah_number') }}</th>
                    <th>{{ __('ayahs.ayah_text') }}</th>
                    <th class="text-center">{{ __('ayahs.page') }}</th>
                    <th class="text-center">{{ __('ayahs.juz') }}</th>
                    <th class="text-center">{{ __('ayahs.sajda') }}</th>
                    <th class="text-center">{{ __('ayahs.status') }}</th>
                    <th class="text-end">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ayahs as $index => $ayah)
                <tr>
                    <td class="number-column text-center">
                        <span class="text-zinc-500 dark:text-zinc-400 font-bold text-xs">
                            {{ $ayahs->firstItem() + $index }}
                        </span>
                    </td>
                    <td>
                        <div class="fw-bold text-zinc-800 dark:text-zinc-200">{{ $ayah->surah->name_simple }}</div>
                        <span class="arabic-text text-zinc-500 dark:text-zinc-400 text-xs">{{ $ayah->surah->name_arabic }}</span>
                    </td>
                    <td class="text-center">
                        <div class="relative d-inline-flex align-items-center justify-content-center w-8 h-8 select-none">
                            <svg class="absolute w-full h-full text-emerald-50 dark:text-emerald-950/40 fill-current stroke-emerald-600/50 dark:stroke-emerald-400/40 stroke-1" viewBox="0 0 24 24">
                                <path d="M12 2L15 5H19V9L22 12L19 15V19H15L12 22L9 19H5V15L2 12L5 9V5H9L12 2Z" />
                            </svg>
                            <span class="relative z-10 text-[10px] font-bold text-emerald-800 dark:text-emerald-300">
                                {{ $ayah->ayah_number }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="arabic-text text-zinc-900 dark:text-white text-lg leading-relaxed select-none" dir="rtl" style="font-family: 'CustomArFont', 'Amiri', serif !important;">
                            {{ Str::limit($ayah->text_uthmani, 80) }}
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="quran-table-badge info">{{ $ayah->page_number ?? '-' }}</span>
                    </td>
                    <td class="text-center">
                        <span class="quran-table-badge info">{{ $ayah->juz_number ?? '-' }}</span>
                    </td>
                    <td class="text-center">
                        @if($ayah->sajda_flag)
                        <span class="quran-table-badge warning">
                            <i class="bi bi-star-fill text-warning me-1"></i>{{ __('ayahs.sajda') }}
                        </span>
                        @else
                        <span class="text-zinc-300 dark:text-zinc-700">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($ayah->is_active)
                        <span class="quran-table-badge success">{{ __('common.active') }}</span>
                        @else
                        <span class="quran-table-badge danger">{{ __('common.inactive') }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="quran-table-actions justify-content-end">
                            <a href="{{ route('ayahs.show', $ayah) }}"
                               class="quran-table-action-btn view"
                               data-bs-toggle="tooltip"
                               title="{{ __('common.view') }}">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('update', $ayah)
                            <a href="{{ route('ayahs.edit', $ayah) }}"
                               class="quran-table-action-btn edit"
                               data-bs-toggle="tooltip"
                               title="{{ __('common.edit') }}">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('delete', $ayah)
                            <button type="button"
                                    class="quran-table-action-btn delete"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('common.delete') }}"
                                    onclick="confirmDelete({{ $ayah->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="quran-table-empty text-center py-5">
                            <i class="bi bi-journal-x fs-1 text-zinc-400 dark:text-zinc-600 mb-2"></i>
                            <h6 class="text-zinc-800 dark:text-zinc-200 font-bold">{{ __('ayahs.no_ayahs_found') }}</h6>
                            <p class="text-zinc-500 dark:text-zinc-400 text-sm mb-0">{{ __('ayahs.no_ayahs_message') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Table Footer with Pagination --}}
        @if($ayahs->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.showing') }}
                    <strong>{{ $ayahs->firstItem() }}</strong>
                    {{ __('common.to') }}
                    <strong>{{ $ayahs->lastItem() }}</strong>
                    {{ __('common.of') }}
                    <strong>{{ $ayahs->total() }}</strong>
                    {{ __('ayahs.ayahs') }}
                </div>
                <div class="quran-pagination">
                    {{ $ayahs->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @elseif(count($ayahs) > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('common.total') }}:
                    <strong>{{ count($ayahs) }}</strong>
                    {{ __('ayahs.ayahs') }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-bold text-zinc-900 dark:text-white">{{ __('common.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-zinc-600 dark:text-zinc-400 py-3 text-sm">
                <p class="mb-0">{{ __('ayahs.delete_confirm_message') }}</p>
            </div>
            <div class="modal-footer border-0 pt-0">
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
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-bold text-zinc-900 dark:text-white" id="importModalLabel">Import JSON File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('ayahs.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label for="json_file" class="form-label text-zinc-700 dark:text-zinc-300 font-bold text-sm">Select .json file to import</label>
                        <input type="file" class="form-control quran-form-control" id="json_file" name="file" accept=".json" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
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
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-bold text-zinc-900 dark:text-white" id="exampleModalLabel">Example JSON Format</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3 text-sm">
                <p class="text-zinc-600 dark:text-zinc-400">The JSON file must be an array of objects structured as shown below:</p>
                <div class="bg-zinc-900 text-zinc-100 p-3 rounded-2xl" style="max-height: 400px; overflow-y: auto;">
                    <pre><code class="text-emerald-400 font-monospace">[
  {
    "surah_number": 1,
    "ayah_number": 1,
    "text_uthmani": "بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ",
    "text_simple": "بسم الله الرحمن الرحيم",
    "page_number": 1,
    "juz_number": 1,
    "hizb_number": 1,
    "rub_number": 1,
    "sajda_flag": false,
    "is_active": true
  }
]</code></pre>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
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
    form.action = "{{ route('ayahs.destroy', ':id') }}".replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Enable tooltips
document.addEventListener('DOMContentLoaded', function() { 
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: 'top'
        });
    });
});
</script>
@endpush
