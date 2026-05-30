@extends('layouts.app')

@section('title', 'بەڕێوەبردنی زیکرەکان')
@section('page-title', 'بەڕێوەبردنی زیکرەکان')
 
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">بەڕێوەبردنی زیکرەکان</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">لیستی زیکر و ئەزکارەکان</h1>
            <div class="text-muted">هەموو زیکر و دوعاکان لەم پەڕەیەوە بەڕێوەببە و هاوپۆلیان بکە.</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('adhkar-categories.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-tag me-1"></i>
                هاوپۆلەکانی ئەزکار
            </a>

            <a href="{{ route('adhkars.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                زیادکردنی زیکر
            </a>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('adhkars.index') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="category_id" class="quran-form-select" onchange="this.form.submit()">
                        <option value="">-- هەموو هاوپۆلەکان --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($selectedCategoryId == $cat->id)>
                                {{ $cat->name_ku }} ({{ $cat->name_ar }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="گەڕان بۆ زیکر بە عەرەبی یان کوردی..."
                    >
                </div>
                <div class="col-md-2">
                    <button class="quran-btn quran-btn-primary w-100" type="submit">
                        <i class="bi bi-search me-1"></i>
                        بگەڕێ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="quran-table-container">
        <!-- Table Toolbar -->
        <div class="quran-table-toolbar">
            <div class="quran-table-search">
                <i class="bi bi-search"></i>
                <input type="text"
                       placeholder="بگەڕێ..."
                       id="tableSearch"
                       value="{{ $search }}">
            </div>
            <div class="quran-table-filters">
                <button class="quran-table-filter-btn" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i>
                    تازەکردنەوە
                </button>
            </div>
        </div>

        <!-- Table -->
        <table class="quran-table quran-table-striped quran-surah-table">
            <thead>
                <tr>
                    <th style="width: 150px;">هاوپۆل</th>
                    <th style="width: 80px;" class="text-center">ڕیزبەندی</th>
                    <th>دەقی عەرەبی</th>
                    <th>دەقی کوردی (مانا)</th>
                    <th class="text-center" style="width: 80px;">جار</th>
                    <th>سەرچاوە</th>
                    <th class="text-end" style="width: 120px;">کردارەکان</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adhkars as $item)
                    <tr>
                        <td>
                            @if($item->category)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    {{ $item->category->name_ku }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="surah-number">{{ $item->order }}</span>
                        </td>
                        <td>
                            <div class="surah-name-arabic" style="font-size: 1rem; text-align: right; direction: rtl; line-height: 1.6;">
                                {{ Str::limit($item->arabic_text, 120) }}
                            </div>
                        </td>
                        <td>
                            <div class="text-muted small" style="line-height: 1.5;">
                                {{ Str::limit($item->translation_ku, 120) }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark" style="font-size: 0.9rem; font-weight: 700;">
                                {{ $item->count }}x
                            </span>
                        </td>
                        <td>
                            @if($item->source)
                                <span class="badge bg-light text-dark">{{ $item->source }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="quran-table-actions justify-content-end">
                                <a href="{{ route('adhkars.edit', $item) }}"
                                   class="quran-table-action-btn edit"
                                   data-bs-toggle="tooltip"
                                   title="دەستکاریکردن">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('adhkars.destroy', $item) }}"
                                      class="d-inline"
                                      onsubmit="return confirmDelete(event, '{{ Str::limit($item->arabic_text, 20) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="quran-table-action-btn delete"
                                            data-bs-toggle="tooltip"
                                            title="سڕینەوە">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="quran-table-empty">
                                <i class="bi bi-chat-square-quote" style="font-size: 3rem; color: #ccc;"></i>
                                <h6 class="mt-3">هیچ زیکرێک نییە</h6>
                                <p>هیچ زیکرێک نەدۆزرایەوە لەم بەشەدا.</p>
                                <a href="{{ route('adhkars.create') }}" class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    دروستکردنی یەکەم زیکر
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Table Footer with Pagination -->
        @if($adhkars->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    پیشاندانی
                    <strong>{{ $adhkars->firstItem() }}</strong>
                    بۆ
                    <strong>{{ $adhkars->lastItem() }}</strong>
                    لە
                    <strong>{{ $adhkars->total() }}</strong>
                    زیکر
                </div>
                <div class="quran-pagination">
                    {{ $adhkars->links() }}
                </div>
            </div>
        @elseif(count($adhkars) > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    کۆتایی لیست. کۆی گشتی:
                    <strong>{{ count($adhkars) }}</strong>
                    زیکر
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Confirm Delete Function
    function confirmDelete(event, dhikrText) {
        event.preventDefault();
        const form = event.target;

        if (confirm('دڵنیای لە سڕینەوەی ئەم زیکرە؟\n\n' + dhikrText)) {
            form.submit();
        }
        return false;
    }

    // Table Search Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('tableSearch');

        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const query = searchInput.value.trim();
                    let url = '{{ route("adhkars.index") }}?';
                    const categoryId = '{{ $selectedCategoryId }}';
                    if (categoryId) {
                        url += 'category_id=' + categoryId + '&';
                    }
                    if (query) {
                        url += 'q=' + encodeURIComponent(query);
                    }
                    window.location.href = url;
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
