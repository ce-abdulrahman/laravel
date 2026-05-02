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

        <!-- Table -->
        <table class="quran-table quran-table-striped quran-surah-table">
            <thead>
                <tr>
                    <th class="number-column">{{ __('surah.fields.number') }}</th>
                    <th class="sortable">{{ __('surah.fields.name_ar') }}</th>
                    <th>{{ __('surah.fields.name_ku') }}</th>
                    <th>{{ __('surah.fields.name_en') }}</th>
                    <th>{{ __('surah.fields.revelation_type') }}</th>
                    <th class="text-center">{{ __('surah.fields.ayah_count') }}</th>
                    <th class="text-center">{{ __('surah.fields.is_active') }}</th>
                    <th class="text-end">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($surahs as $surah)
                    <tr>
                        <td class="number-column">
                            <span class="surah-number">{{ $surah->number }}</span>
                        </td>
                        <td>
                            <div class="surah-name-arabic">{{ $surah->name_ar }}</div>
                        </td>
                        <td>
                            @if($surah->name_ku)
                                <span class="surah-name-english">{{ $surah->name_ku }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($surah->name_en)
                                <div class="surah-name-english">{{ $surah->name_en }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
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
                        <td colspan="8">
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
                    {{ $surahs->links() }}
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
