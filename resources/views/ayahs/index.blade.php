{{-- resources/views/ayahs/index.blade.php --}}
@extends('layouts.app')

@section('title', __('ayahs.title_index'))
@section('page-title', __('ayahs.title_index'))
 
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('ayahs.ayahs') }}</li>
@endsection

@section('content')
<div class="quran-content-container">
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">{{ __('ayahs.title_index') }}</h4>
            
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
                <i class="bi bi-plus-lg"></i>
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
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('ayahs.index') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('ayahs.filter_by_surah') }}</label>
                        <select name="surah_id" class="quran-form-select">
                            <option value="">{{ __('ayahs.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                {{ $surah->id }}. {{ $surah->name_simple }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="quran-form-label">{{ __('ayahs.filter_by_juz') }}</label>
                        <select name="juz_number" class="quran-form-select">
                            <option value="">{{ __('ayahs.all_juz') }}</option>
                            @foreach($juzNumbers as $juz)
                            <option value="{{ $juz }}" {{ request('juz_number') == $juz ? 'selected' : '' }}>
                                {{ __('ayahs.juz') }} {{ $juz }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('ayahs.search') }}</label>
                        <input type="text" name="search" class="quran-form-control"
                               placeholder="{{ __('ayahs.search_placeholder') }}"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="quran-form-label">{{ __('ayahs.per_page') }}</label>
                        <select name="per_page" class="quran-form-select">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="quran-btn quran-btn-primary w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Ayahs Table --}}
    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table quran-table-striped">
                <thead>
                    <tr>
                        <th class="number-column">#</th>
                        <th>{{ __('ayahs.surah') }}</th>
                        <th>{{ __('ayahs.ayah_number') }}</th>
                        <th>{{ __('ayahs.ayah_text') }}</th>
                        <th>{{ __('ayahs.page') }}</th>
                        <th>{{ __('ayahs.juz') }}</th>
                        <th>{{ __('ayahs.sajda') }}</th>
                        <th>{{ __('ayahs.status') }}</th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ayahs as $index => $ayah)
                    <tr>
                        <td class="number-column">{{ $ayahs->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $ayah->surah->name_ar }}</div>
                            <small class="text-muted">{{ $ayah->surah->name_en }}</small>
                        </td>
                        <td>
                            <span class="quran-surah-number" style="width: 36px; height: 36px; font-size: 14px;">
                                {{ $ayah->ayah_number }}
                            </span>
                        </td>
                        <td>
                            <div class="arabic-text" style="font-family: var(--font-arabic); font-size: 18px; line-height: 1.8;">
                                {{ Str::limit($ayah->text_uthmani, 80) }}
                            </div>
                        </td>
                        <td>{{ $ayah->page_number ?? '-' }}</td>
                        <td>{{ $ayah->juz_number ?? '-' }}</td>
                        <td>
                            @if($ayah->sajda_flag)
                            <span class="quran-table-badge warning">
                                <i class="bi bi-star-fill"></i> {{ __('ayahs.sajda') }}
                            </span>
                            @else
                            <span class="text-danger">×</span>
                            @endif
                        </td>
                        <td>
                            @if($ayah->is_active)
                            <span class="quran-table-badge success">{{ __('common.active') }}</span>
                            @else
                            <span class="quran-table-badge danger">{{ __('common.inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="quran-table-actions">
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
                            <div class="quran-table-empty">
                                <i class="bi bi-journal-x"></i>
                                <h6>{{ __('ayahs.no_ayahs_found') }}</h6>
                                <p>{{ __('ayahs.no_ayahs_message') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($ayahs->hasPages())
        <div class="card-footer">
            <div class="quran-pagination">
                {{ $ayahs->links() }}
            </div>
            <div class="pagination-info">
                {{ __('common.showing') }}
                <strong>{{ $ayahs->firstItem() }}</strong>
                {{ __('common.to') }}
                <strong>{{ $ayahs->lastItem() }}</strong>
                {{ __('common.of') }}
                <strong>{{ $ayahs->total() }}</strong>
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
            <div class="modal-header border-0">
                <h5 class="modal-title">{{ __('common.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('ayahs.delete_confirm_message') }}</p>
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
            <form action="{{ route('ayahs.import') }}" method="POST" enctype="multipart/form-data">
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
    form.action = "{{ route('ayahs.destroy', ':id') }}".replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Enable tooltips
document.addEventListener('DOMContentLoaded', function() { 
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
