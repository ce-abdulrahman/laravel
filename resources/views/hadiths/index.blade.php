{{-- resources/views/hadiths/index.blade.php --}}
@extends('layouts.app')

@section('title', __('hadiths.titles.index'))
@section('page-title', __('hadiths.titles.index'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('hadiths.titles.index') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('hadiths.titles.index') }}</h1>
            <div class="text-muted">{{ __('hadiths.hints.index') }}</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <form method="GET" action="{{ route('hadiths.index') }}" class="d-flex gap-2" id="searchForm">
                <select name="category_id" class="form-select" onchange="document.getElementById('searchForm').submit()">
                    <option value="">-- {{ __('hadith_categories.placeholders.search') }} --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($selectedCategoryId == $cat->id)>
                            {{ $cat->{'name_' . app()->getLocale()} ?? $cat->name_ku }}
                        </option>
                    @endforeach
                </select>
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="{{ __('hadiths.placeholders.search') }}"
                    style="min-width: 250px;"
                >
                <button class="quran-btn quran-btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <a href="{{ route('hadiths.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                {{ __('hadiths.actions.create') }}
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
                       placeholder="{{ __('hadiths.placeholders.search') }}"
                       id="tableSearch"
                       value="{{ $search }}">
            </div>
            <div class="quran-table-filters">
                <button class="quran-table-filter-btn" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i>
                    {{ __('hadiths.actions.refresh') }}
                </button>
            </div>
        </div>

        @php
            $totalColumns = 7 + \App\Models\Language::activeList()->count();
        @endphp

        <!-- Table -->
        <div class="table-responsive">
            <table class="quran-table quran-table-striped quran-surah-table">
                <thead>
                    <tr>
                        <th class="number-column" style="width: 80px;">{{ __('hadiths.table.order') }}</th>
                        <th>{{ __('hadiths.table.category') }}</th>
                        <th>{{ __('hadiths.table.narrator') }}</th>
                        <th>{{ __('hadiths.table.arabic_text') }}</th>
                        @foreach(\App\Models\Language::activeList() as $lang)
                            <th>Translation ({{ $lang->name }})</th>
                        @endforeach
                        <th>{{ __('hadiths.table.source') }}</th>
                        <th class="text-center" style="width: 100px;">{{ __('hadiths.table.status') }}</th>
                        <th class="text-end" style="width: 150px;">{{ __('hadiths.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hadiths as $had)
                        <tr>
                            <td class="number-column">
                                <span class="surah-number">{{ $had->order }}</span>
                            </td>
                            <td>
                                <span class="quran-table-badge info">
                                    {{ $had->category->name }}
                                </span>
                            </td>
                            <td>
                                @if($had->narrator)
                                    <small class="text-muted">{{ Str::limit($had->narrator, 25) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="surah-name-arabic text-truncate" style="max-width: 220px;" dir="rtl">{{ $had->arabic_text }}</div>
                            </td>
                            @foreach(\App\Models\Language::activeList() as $lang)
                                <td>
                                    @php
                                        $val = $had->getTranslation('translation', $lang->code);
                                        $attrs = $had->getTranslationAttributes('translation', $lang->code);
                                    @endphp
                                    @if($val !== null && $val !== '')
                                        <div class="{{ $attrs['class'] }} text-truncate" style="max-width: 220px; {{ $attrs['style'] }}" dir="{{ $attrs['dir'] }}" title="{{ $val }}">
                                            {{ $val }}
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted border small">{{ __('common.missing_translation') ?? 'Missing' }}</span>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                @if($had->source)
                                    <span class="badge bg-light text-dark">{{ $had->source }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($had->is_active)
                                    <span class="quran-table-badge success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ __('hadiths.status.active') }}
                                    </span>
                                @else
                                    <span class="quran-table-badge danger">
                                        <i class="bi bi-x-circle me-1"></i>
                                        {{ __('hadiths.status.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="quran-table-actions justify-content-end">
                                    <a href="{{ route('hadiths.show', $had) }}"
                                       class="quran-table-action-btn view"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('hadiths.actions.view') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('hadiths.edit', $had) }}"
                                       class="quran-table-action-btn edit"
                                       data-bs-toggle="tooltip"
                                       title="{{ __('hadiths.actions.edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('hadiths.destroy', $had) }}"
                                          class="d-inline"
                                          onsubmit="return confirmDelete(event)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="quran-table-action-btn delete"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('hadiths.actions.delete') }}">
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
                                    <i class="bi bi-chat-square-text" style="font-size: 3rem; color: #ccc;"></i>
                                    <h6 class="mt-3">{{ __('hadiths.empty.title') }}</h6>
                                    <p>{{ __('hadiths.empty.message') }}</p>
                                    <a href="{{ route('hadiths.create') }}" class="quran-btn quran-btn-primary mt-3">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        {{ __('hadiths.actions.create_first') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer with Pagination -->
        @if($hadiths->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('hadiths.pagination.showing') }}
                    <strong>{{ $hadiths->firstItem() }}</strong>
                    {{ __('hadiths.pagination.to') }}
                    <strong>{{ $hadiths->lastItem() }}</strong>
                    {{ __('hadiths.pagination.of') }}
                    <strong>{{ $hadiths->total() }}</strong>
                    {{ __('hadiths.pagination.entries') }}
                </div>
                <div class="quran-pagination">
                    {{ $hadiths->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @elseif(count($hadiths) > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    {{ __('hadiths.pagination.total') }}
                    <strong>{{ count($hadiths) }}</strong>
                    {{ __('hadiths.pagination.entries') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(event) {
        event.preventDefault();
        const form = event.target;

        if (confirm('{{ __('hadiths.messages.confirm_delete') }}')) {
            form.submit();
        }
        return false;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('tableSearch');

        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const query = searchInput.value.trim();
                    const categoryId = '{{ $selectedCategoryId }}';
                    let url = '{{ route("hadiths.index") }}?q=' + encodeURIComponent(query);
                    if (categoryId) {
                        url += '&category_id=' + categoryId;
                    }
                    window.location.href = url;
                }
            });
        }

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                placement: 'top'
            });
        });
    });
</script>
@endpush
