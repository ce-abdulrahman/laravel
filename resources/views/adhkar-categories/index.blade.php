@extends('layouts.app')

@section('title', 'هاوپۆلەکانی ئەزکار')
@section('page-title', 'هاوپۆلەکانی ئەزکار')
 
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">هاوپۆلەکانی ئەزکار</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">هاوپۆلەکانی ئەزکار</h1>
            <div class="text-muted">هاوپۆلەکانی زیکر (بەیانیان، ئێواران، دوای نوێژ، خەوتن) بەڕێوەببە.</div>
        </div>

        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('adhkar-categories.index') }}" class="d-flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="گەڕان بۆ هاوپۆل..."
                >
                <button class="quran-btn quran-btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <a href="{{ route('adhkar-categories.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                زیادکردنی هاوپۆل
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
                       placeholder="گەڕان بۆ هاوپۆل..."
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
                    <th class="number-column" style="width: 80px;">ڕیزبەندی</th>
                    <th>ناوی کوردی</th>
                    <th>ناوی عەرەبی</th>
                    <th>ناوی ئینگلیزی</th>
                    <th>ئایکۆن</th>
                    <th class="text-center" style="width: 120px;">دۆخ</th>
                    <th class="text-end" style="width: 150px;">کردارەکان</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td class="number-column">
                            <span class="surah-number">{{ $cat->order }}</span>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $cat->name_ku }}</div>
                        </td>
                        <td>
                            <div class="surah-name-arabic">{{ $cat->name_ar }}</div>
                        </td>
                        <td>
                            @if($cat->name_en)
                                <span>{{ $cat->name_en }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($cat->icon)
                                <code>{{ $cat->icon }}</code>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($cat->is_active)
                                <span class="quran-table-badge success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    چالاک
                                </span>
                            @else
                                <span class="quran-table-badge danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    ناچالاک
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="quran-table-actions justify-content-end">
                                <a href="{{ route('adhkars.index') }}?category_id={{ $cat->id }}"
                                   class="quran-table-action-btn view"
                                   data-bs-toggle="tooltip"
                                   title="بینینی زیکرەکان">
                                    <i class="bi bi-list-task"></i>
                                </a>
                                <a href="{{ route('adhkar-categories.edit', $cat) }}"
                                   class="quran-table-action-btn edit"
                                   data-bs-toggle="tooltip"
                                   title="دەستکاریکردن">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('adhkar-categories.destroy', $cat) }}"
                                      class="d-inline"
                                      onsubmit="return confirmDelete(event, '{{ $cat->name_ku }}')">
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
                                <i class="bi bi-tag" style="font-size: 3rem; color: #ccc;"></i>
                                <h6 class="mt-3">هیچ هاوپۆلێک نییە</h6>
                                <p>هیچ هاوپۆلی ئەزکارێک نەدۆزرایەوە لە داتابەیسدا.</p>
                                <a href="{{ route('adhkar-categories.create') }}" class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    دروستکردنی یەکەم هاوپۆل
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Table Footer with Pagination -->
        @if($categories->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    پیشاندانی
                    <strong>{{ $categories->firstItem() }}</strong>
                    بۆ
                    <strong>{{ $categories->lastItem() }}</strong>
                    لە
                    <strong>{{ $categories->total() }}</strong>
                    هاوپۆل
                </div>
                <div class="quran-pagination">
                    {{ $categories->links() }}
                </div>
            </div>
        @elseif(count($categories) > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    کۆتایی لیست. کۆی گشتی:
                    <strong>{{ count($categories) }}</strong>
                    هاوپۆل
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Confirm Delete Function
    function confirmDelete(event, catName) {
        event.preventDefault();
        const form = event.target;

        if (confirm('دڵنیای لە سڕینەوەی ئەم هاوپۆلە؟ هەموو زیکرەکانی ژێر ئەم هاوپۆلەش دەسڕێنەوە!\n\n' + catName)) {
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
                    if (query) {
                        window.location.href = '{{ route("adhkar-categories.index") }}?q=' + encodeURIComponent(query);
                    } else {
                        window.location.href = '{{ route("adhkar-categories.index") }}';
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
