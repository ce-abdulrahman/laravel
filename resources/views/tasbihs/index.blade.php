@extends('layouts.app')

@section('title', 'بەڕێوەبردنی تەسبیحەکان')
@section('page-title', 'بەڕێوەبردنی تەسبیحەکان')
 
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">بەڕێوەبردنی تەسبیحەکان</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">بەڕێوەبردنی تەسبیحەکانی ناو ئەپ</h1>
            <div class="text-muted">لێرەوە دەتوانیت ئەو زیکر و تەسبیحانە بەڕێوەببەیت کە لە لاپەڕەی تەسبیحی مۆبایلەکەتدا پیشان دەدرێن.</div>
        </div>

        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('tasbihs.index') }}" class="d-flex gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="گەڕان بۆ زیکر..."
                >
                <button class="quran-btn quran-btn-outline-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <a href="{{ route('tasbihs.create') }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                زیادکردنی تەسبیح
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
                       placeholder="گەڕان بۆ تەسبیح..."
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
                    <th class="number-column" style="width: 80px;">ڕێژە</th>
                    <th>ناوی زیکر</th>
                    <th>ئامانج</th>
                    <th class="text-center" style="width: 120px;">دۆخ</th>
                    <th class="text-end" style="width: 120px;">کردارەکان</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasbihs as $tasbih)
                    <tr>
                        <td class="number-column">
                            <span class="surah-number">{{ $loop->iteration }}</span>
                        </td>
                        <td>
                            <div class="surah-name-arabic" style="font-size: 1.1rem; line-height: 1.6;">{{ $tasbih->name }}</div>
                        </td>
                        <td>
                            <div style="font-weight: bold; font-size: 1rem;">{{ $tasbih->target }} جار</div>
                        </td>
                        <td class="text-center">
                            @if($tasbih->is_active)
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
                                <a href="{{ route('tasbihs.edit', $tasbih) }}"
                                   class="quran-table-action-btn edit"
                                   data-bs-toggle="tooltip"
                                   title="دەستکاریکردن">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('tasbihs.destroy', $tasbih) }}"
                                      class="d-inline"
                                      onsubmit="return confirmDelete(event, '{{ Str::limit($tasbih->name, 20) }}')">
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
                        <td colspan="5">
                            <div class="quran-table-empty">
                                <i class="bi bi-heptagon" style="font-size: 3rem; color: #ccc;"></i>
                                <h6 class="mt-3">هیچ تەسبیحێک نییە</h6>
                                <p>هیچ تەسبیح یان زیکرێک نەدۆزرایەوە لە داتابەیسدا.</p>
                                <a href="{{ route('tasbihs.create') }}" class="quran-btn quran-btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    دروستکردنی یەکەم تەسبیح
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Table Footer with Pagination -->
        @if($tasbihs->hasPages())
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    پیشاندانی
                    <strong>{{ $tasbihs->firstItem() }}</strong>
                    بۆ
                    <strong>{{ $tasbihs->lastItem() }}</strong>
                    لە
                    <strong>{{ $tasbihs->total() }}</strong>
                    تەسبیح
                </div>
                <div class="quran-pagination">
                    {{ $tasbihs->links() }}
                </div>
            </div>
        @elseif(count($tasbihs) > 0)
            <div class="quran-table-footer">
                <div class="quran-table-info">
                    کۆتایی لیست. کۆی گشتی:
                    <strong>{{ count($tasbihs) }}</strong>
                    تەسبیح
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(event, tasbihName) {
        event.preventDefault();
        const form = event.target;

        if (confirm('دڵنیای لە سڕینەوەی ئەم تەسبیحە؟\n\n' + tasbihName)) {
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
                    if (query) {
                        window.location.href = '{{ route("tasbihs.index") }}?q=' + encodeURIComponent(query);
                    } else {
                        window.location.href = '{{ route("tasbihs.index") }}';
                    }
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
