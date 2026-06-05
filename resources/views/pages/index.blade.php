{{-- resources/views/pages/index.blade.php --}}
@extends('layouts.app')

@section('title', __('sidebar.page'))
@section('page-title', __('sidebar.page'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('sidebar.page') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('sidebar.page') }}</h1>
            <div class="text-muted">
                @if(app()->getLocale() == 'ku')
                    خوێندنەوەی قورئانی پیرۆز بەپێی لاپەڕەکان (٦٠٤ لاپەڕە)
                @elseif(app()->getLocale() == 'ar')
                    قراءة القرآن الكريم حسب الصفحات (٦٠٤ صفحة)
                @else
                    Read the Holy Quran by pages (604 pages)
                @endif
            </div>
        </div>
        
        <!-- Quick Search Form -->
        <div>
            <form id="pageSearchForm" class="d-flex gap-2" onsubmit="handlePageSearch(event)">
                <input
                    type="number"
                    id="pageNumberInput"
                    min="1"
                    max="604"
                    class="form-control"
                    placeholder="@if(app()->getLocale() == 'ku')لاپەڕە (١-٦٠٤)...@elseif(app()->getLocale() == 'ar')صفحة (١-٦٠٤)...@elsePage (1-604)...@endif"
                    style="min-width: 200px; border-radius: 10px;"
                    required
                >
                <button type="submit" class="quran-btn quran-btn-primary" style="border-radius: 10px;">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Grid Layout -->
    <div class="row g-4">
        @foreach($pages as $page)
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="quran-card h-100 d-flex flex-column justify-content-between" style="border: 1px solid rgba(27,115,64,0.12); transition: transform 0.2s, box-shadow 0.2s; border-radius: 16px; overflow: hidden;">
                    <div class="quran-card-body p-4">
                        <!-- Top header inside card -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="surah-number" style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: var(--quran-primary); color: white; font-weight: bold;">
                                {{ $page['number'] }}
                            </span>
                            <span class="text-muted small fw-medium">
                                @if(app()->getLocale() == 'ku')
                                    لاپەڕەی {{ $page['number'] }}
                                @elseif(app()->getLocale() == 'ar')
                                    الصفحة {{ $page['number'] }}
                                @else
                                    Page {{ $page['number'] }}
                                @endif
                            </span>
                        </div>
                        
                        <!-- Title -->
                        <h5 class="fw-bold mb-2">Page {{ $page['number'] }}</h5>
                        
                        <!-- Range info -->
                        @if($page['range'])
                            <div class="mt-3 pt-3 border-top" style="border-top-style: dashed !important; border-top-color: rgba(27,115,64,0.1) !important;">
                                <label class="text-muted small d-block mb-1 fw-medium">
                                    @if(app()->getLocale() == 'ku')
                                        ناوەرۆک
                                    @elseif(app()->getLocale() == 'ar')
                                        المحتوى
                                    @else
                                        Content
                                    @endif
                                </label>
                                <span class="small text-dark fw-semibold" style="line-height: 1.4; display: block;">
                                    {{ $page['range'] }}
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="quran-card-footer bg-transparent border-top-0 pt-0 pb-4 px-4">
                        <a href="{{ route('read.page', $page['number']) }}" target="_blank" class="quran-btn quran-btn-primary w-100 py-2.5 text-center d-block text-decoration-none" style="font-size: 0.85rem; font-weight: 600; border-radius: 10px;">
                            <i class="bi bi-book-half me-1"></i>
                            @if(app()->getLocale() == 'ku')
                                خوێندنەوە
                            @elseif(app()->getLocale() == 'ar')
                                قراءة
                            @else
                                Read Page
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination Footer -->
    @if($pages->hasPages())
        <div class="quran-table-footer mt-5">
            <div class="quran-table-info text-muted">
                {{ __('hadiths.pagination.showing') }}
                <strong>{{ $pages->firstItem() }}</strong>
                {{ __('hadiths.pagination.to') }}
                <strong>{{ $pages->lastItem() }}</strong>
                {{ __('hadiths.pagination.of') }}
                <strong>{{ $pages->total() }}</strong>
                {{ __('hadiths.pagination.entries') }}
            </div>
            <div class="quran-pagination">
                {{ $pages->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<style>
    .quran-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(27,115,64,0.08) !important;
    }
</style>
@endsection

@push('scripts')
<script>
    function handlePageSearch(event) {
        event.preventDefault();
        const pageNum = parseInt(document.getElementById('pageNumberInput').value.trim(), 10);
        if (pageNum >= 1 && pageNum <= 604) {
            const url = '{{ route("read.page", ":page") }}'.replace(':page', pageNum);
            window.open(url, '_blank');
        } else {
            alert('Please enter a valid page number between 1 and 604');
        }
    }
</script>
@endpush
