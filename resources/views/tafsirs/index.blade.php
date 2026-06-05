{{-- resources/views/tafsirs/index.blade.php --}}
@extends('layouts.app')

@section('title', __('tafsirs.titles.index'))
@section('page-title', __('tafsirs.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('tafsirs.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('tafsirs.titles.index') }}</h1>
            <div class="text-muted">{{ __('tafsirs.hints.manage') }}</div>
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
            <a href="{{ route('tafsir-books.create') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('tafsir_books.actions.create') }}
            </a>
            <a href="{{ route('tafsirs.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('tafsirs.actions.create') }}
            </a>
            @endif
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-primary">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tafsirs.total_tafsirs') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_tafsirs']) }}</div>
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
                        <div class="quran-stat-label">{{ __('tafsirs.total_books') }}</div>
                        <div class="quran-stat-value">{{ $stats['total_books'] }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-bookshelf"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quran-stat-card quran-stat-warning">
                <div class="quran-stat-content">
                    <div class="quran-stat-info">
                        <div class="quran-stat-label">{{ __('tafsirs.ayahs_with_tafsir') }}</div>
                        <div class="quran-stat-value">{{ number_format($stats['total_ayahs_with_tafsir']) }}</div>
                    </div>
                    <div class="quran-stat-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="quran-card mb-4">
        <div class="quran-card-body">
            <form method="GET" action="{{ route('tafsirs.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('tafsirs.filter_by_book') }}</label>
                        <select name="tafsir_book_id" class="quran-form-select">
                            <option value="">{{ __('tafsirs.all_books') }}</option>
                            @foreach($tafsirBooks as $book)
                            <option value="{{ $book->id }}" {{ request('tafsir_book_id') == $book->id ? 'selected' : '' }}>
                                {{ $book->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="quran-form-label">{{ __('tafsirs.filter_by_surah') }}</label>
                        <select name="surah_id" class="quran-form-select">
                            <option value="">{{ __('tafsirs.all_surahs') }}</option>
                            @foreach($surahs as $surah)
                            <option value="{{ $surah->id }}" {{ request('surah_id') == $surah->id ? 'selected' : '' }}>
                                {{ $surah->number }}. {{ $surah->name_ar }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="quran-form-label">{{ __('tafsirs.search') }}</label>
                        <input type="text" name="search" class="quran-form-control" 
                               placeholder="{{ __('tafsirs.search_placeholder') }}" 
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

    <!-- Table -->
    <div class="quran-card">
        <div class="quran-table-container">
            <table class="quran-table quran-tafsir-table">
                <thead>
                    <tr>
                        <th>{{ __('tafsirs.fields.surah_ayah') }}</th>
                        <th>{{ __('tafsirs.fields.tafsir_book') }}</th>
                        <th>{{ __('tafsirs.fields.content') }}</th>
                        <th>{{ __('tafsirs.fields.status') }}</th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tafsirs as $tafsir)
                    <tr>
                        <td>
                            <a href="{{ route('ayahs.show', $tafsir->ayah) }}" class="text-decoration-none">
                                <span class="fw-semibold">{{ $tafsir->ayah->surah->name_ar }}</span>
                                <span class="text-muted ms-1">({{ $tafsir->ayah->ayah_number }})</span>
                            </a>
                        </td>
                        <td>
                            <span class="tafsir-source">{{ $tafsir->tafsirBook->name }}</span>
                        </td>
                        <td>
                            <div class="tafsir-excerpt">
                                {{ $tafsir->short_content ?: Str::limit($tafsir->content, 80) }}
                            </div>
                        </td>
                        <td>
                            <span class="quran-table-badge {{ $tafsir->is_active ? 'success' : 'danger' }}">
                                {{ $tafsir->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td>
                            <div class="quran-table-actions justify-content-end">
                                <a href="{{ route('tafsirs.show', $tafsir) }}" class="quran-table-action-btn view">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('tafsirs.edit', $tafsir) }}" class="quran-table-action-btn edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="quran-table-action-btn delete" 
                                        onclick="confirmDelete({{ $tafsir->id }})">
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
                                <i class="bi bi-journal-x"></i>
                                <h6>{{ __('tafsirs.no_tafsirs_found') }}</h6>
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('tafsirs.create') }}" class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    {{ __('tafsirs.actions.create_first') }}
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tafsirs->hasPages())
        <div class="card-footer">
            {{ $tafsirs->links() }}
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
                <p>{{ __('tafsirs.messages.confirm_delete') }}</p>
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
            <form action="{{ route('tafsirs.import') }}" method="POST" enctype="multipart/form-data">
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
    "tafsir_book_name": "Asadi",
    "content": "بە ناوی خودای بەخشندە و دلۆڤان دەست پێ دەکەم...",
    "short_content": "تەفسیری کورتی سورەتی فاتیحە...",
    "source_reference": "لاپەڕە ١",
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
    form.action = "{{ route('tafsirs.destroy', ':id') }}".replace(':id', id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush