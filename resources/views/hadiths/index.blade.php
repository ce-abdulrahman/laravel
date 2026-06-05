@extends('layouts.app')

@section('title', 'لیستی فەرموودەکان')
@section('page-title', 'لیستی فەرموودەکان')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">فەرموودەکان</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">لیستی فەرموودەکان</h1>
            <div class="text-muted">فەرموودەکان بەپێی هاوپۆلەکانیان دروست بکە، دەستکاری بکە یان بسڕەوە.</div>
        </div>

        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('hadiths.index') }}" class="d-flex gap-2" id="searchForm">
                <select name="category_id" class="form-select" onchange="document.getElementById('searchForm').submit()">
                    <option value="">-- هەموو هاوپۆلەکان --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected($selectedCategoryId == $cat->id)>
                            {{ $cat->name_ku }}
                        </option>
                    @endforeach
                </select>
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="گەڕان لە دەق، وەرگێڕان یان ڕاوی..."
                    style="min-width: 250px;"
                >
                <button class="quran-btn quran-btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <a href="{{ route('hadiths.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                زیادکردنی فەرموودە
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
                       placeholder="خێرا گەڕان..."
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
                    <th>هاوپۆل</th>
                    <th>ڕاوی</th>
                    <th>دەقی عەرەبی</th>
                    <th>وەرگێڕانی کوردی</th>
                    <th>سەرچاوە</th>
                    <th class="text-center" style="width: 100px;">دۆخ</th>
                    <th class="text-end" style="width: 120px;">کردارەکان</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hadiths as $had)
                    <tr>
                        <td class="number-column">
                            <span class="surah-number">{{ $had->order }}</span>
                        </td>
                        <td>
                            <span class="quran-table-badge info">{{ $had->category->name_ku }}</span>
                        </td>
                        <td>
                            @if($had->narrator)
                                <small class="text-muted">{{ Str::limit($had->narrator, 25) }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="surah-name-arabic text-truncate" style="max-width: 250px;">{{ $had->arabic_text }}</div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 250px;">{{ $had->translation_ku }}</div>
                        </td>
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
                                <a href="{{ route('hadiths.edit', $had) }}"
                                   class="quran-table-action-btn edit"
                                   data-bs-toggle="tooltip"
                                   title="دەستکاریکردن">
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
                                            title="سڕینەوە">
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
                                <i class="bi bi-chat-square-text" style="font-size: 3rem; color: #ccc;"></i>
                                <h6 class="mt-3">هیچ فەرموودەیەک نییە</h6>
                                <p>هیچ فەرموودەیەک نەدۆزرایەوە لەم بەشەدا.</p>
                                <a href="{{ route('hadiths.create') }}" class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    دروستکردنی یەکەم فەرموودە
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Table Footer with Pagination -->
        @if($hadiths->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    پیشاندانی
                    <strong>{{ $hadiths->firstItem() }}</strong>
                    بۆ
                    <strong>{{ $hadiths->lastItem() }}</strong>
                    لە
                    <strong>{{ $hadiths->total() }}</strong>
                    فەرموودە
                </div>
                <div class="quran-pagination">
                    {{ $hadiths->links() }}
                </div>
            </div>
        @elseif(count($hadiths) > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    کۆتایی لیست. کۆی گشتی:
                    <strong>{{ count($hadiths) }}</strong>
                    فەرموودە
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

        if (confirm('دڵنیای لە سڕینەوەی ئەم فەرموودەیە؟')) {
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
