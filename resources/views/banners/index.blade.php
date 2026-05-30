@extends('layouts.app')

@section('title', 'بەڕێوەبردنی بانەرەکان')
@section('page-title', 'بەڕێوەبردنی بانەرەکان')
 
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">بەڕێوەبردنی بانەرەکان</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">بەڕێوەبردنی بانەرەکانی پەڕەی سەرەکی</h1>
            <div class="text-muted">لێرەوە دەتوانیت ئەو ئایەت و بانەرانە بەڕێوەببەیت کە لە خشتەی پەڕەی سەرەکی مۆبایلەکەتدا پیشان دەدرێن.</div>
        </div>

        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('banners.index') }}" class="d-flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="گەڕان بۆ بانەر..."
                >
                <button class="quran-btn quran-btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <a href="{{ route('banners.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                زیادکردنی بانەر
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
                       placeholder="گەڕان بۆ بانەر..."
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
                    <th>دەقی عەرەبی (ئایەت)</th>
                    <th>دەقی کوردی (مانا)</th>
                    <th>سەرچاوە</th>
                    <th>بەستراوە بە</th>
                    <th class="text-center" style="width: 120px;">دۆخ</th>
                    <th class="text-end" style="width: 120px;">کردارەکان</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                    <tr>
                        <td class="number-column">
                            <span class="surah-number">{{ $banner->order }}</span>
                        </td>
                        <td>
                            @if($banner->title_arabic)
                                <div class="surah-name-arabic" style="font-size: 1.1rem; line-height: 1.6;">{{ $banner->title_arabic }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 500; font-size: 0.95rem;">{{ Str::limit($banner->verse, 100) }}</div>
                        </td>
                        <td>
                            @if($banner->source)
                                <span class="badge bg-light text-dark">{{ $banner->source }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($banner->surah)
                                <span class="quran-table-badge info">
                                    سوورەتی {{ $banner->surah->name_ar }} (ئایەتی {{ $banner->ayah_number ?? '1' }})
                                </span>
                            @else
                                <span class="text-muted">بەستراو نییە</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($banner->is_active)
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
                                <a href="{{ route('banners.edit', $banner) }}"
                                   class="quran-table-action-btn edit"
                                   data-bs-toggle="tooltip"
                                   title="دەستکاریکردن">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('banners.destroy', $banner) }}"
                                      class="d-inline"
                                      onsubmit="return confirmDelete(event, '{{ Str::limit($banner->verse, 20) }}')">
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
                                <i class="bi bi-image-alt" style="font-size: 3rem; color: #ccc;"></i>
                                <h6 class="mt-3">هیچ بانەرێک نییە</h6>
                                <p>هیچ بانەرێک نەدۆزرایەوە لە داتابەیسدا.</p>
                                <a href="{{ route('banners.create') }}" class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    دروستکردنی یەکەم بانەر
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Table Footer with Pagination -->
        @if($banners->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    پیشاندانی
                    <strong>{{ $banners->firstItem() }}</strong>
                    بۆ
                    <strong>{{ $banners->lastItem() }}</strong>
                    لە
                    <strong>{{ $banners->total() }}</strong>
                    بانەر
                </div>
                <div class="quran-pagination">
                    {{ $banners->links() }}
                </div>
            </div>
        @elseif(count($banners) > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    کۆتایی لیست. کۆی گشتی:
                    <strong>{{ count($banners) }}</strong>
                    بانەر
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Confirm Delete Function
    function confirmDelete(event, bannerTitle) {
        event.preventDefault();
        const form = event.target;

        if (confirm('دڵنیای لە سڕینەوەی ئەم بانەرە؟\n\n' + bannerTitle)) {
            form.submit();
        }
        return false;
    }

    // Table Search Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('tableSearch');

        if (searchInput) {
            // Handle search
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const query = searchInput.value.trim();
                    if (query) {
                        window.location.href = '{{ route("banners.index") }}?q=' + encodeURIComponent(query);
                    } else {
                        window.location.href = '{{ route("banners.index") }}';
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
