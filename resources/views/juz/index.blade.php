{{-- resources/views/juz/index.blade.php --}}
@extends('layouts.app')

@section('title', __('sidebar.juz'))
@section('page-title', __('sidebar.juz'))

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('sidebar.juz') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="mb-4">
        <h1 class="h4 mb-1">{{ __('sidebar.juz') }}</h1>
        <div class="text-muted">
            @if(app()->getLocale() == 'ku')
                خوێندنەوەی قورئانی پیرۆز بەپێی جوزءەکان
            @elseif(app()->getLocale() == 'ar')
                قراءة القرآن الكريم حسب الأجزاء
            @else
                Read the Holy Quran by Juz divisions
            @endif
        </div>
    </div>

    <!-- Grid Layout -->
    <div class="row g-4">
        @foreach($juzs as $juz)
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="quran-card h-100 d-flex flex-column justify-content-between hover-card" style="border: 1px solid rgba(27,115,64,0.08); border-radius: 20px; overflow: hidden; background: var(--quran-bg-card, #ffffff); box-shadow: 0 4px 12px rgba(0,0,0,0.02); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                    <div class="quran-card-body p-4">
                        <!-- Top header inside card -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <!-- Gold Islamic geometric octagram star badge -->
                            <div class="position-relative d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <svg class="position-absolute w-100 h-100" style="left:0; top:0; color: rgba(212,175,55,0.12); stroke: rgba(212,175,55,0.7); fill: currentColor; stroke-width: 1.2;" viewBox="0 0 24 24">
                                    <path d="M12 2L15 5H19V9L22 12L19 15V19H15L12 22L9 19H5V15L2 12L5 9V5H9L12 2Z" />
                                </svg>
                                <span class="position-relative fw-extrabold" style="z-index: 2; font-size: 1.05rem; color: #a49e2b !important; line-height: 1;">
                                    {{ $juz['number'] }}
                                </span>
                            </div>
                            <span class="text-muted small fw-medium">
                                @if(app()->getLocale() == 'ku')
                                    جوزئی {{ $juz['number'] }}
                                @elseif(app()->getLocale() == 'ar')
                                    الجزء {{ $juz['number'] }}
                                @else
                                    Juz {{ $juz['number'] }}
                                @endif
                            </span>
                        </div>
                        
                        <!-- Title -->
                        <h5 class="fw-bold mb-2">Juz {{ $juz['number'] }}</h5>
                        
                        <!-- Range info -->
                        @if($juz['start_surah_name'])
                            <div class="mt-3 pt-3 border-top" style="border-top-style: dashed !important; border-top-color: rgba(27,115,64,0.1) !important;">
                                <label class="text-muted small d-block mb-2 fw-medium">
                                    @if(app()->getLocale() == 'ku')
                                        ناوەرۆک
                                    @elseif(app()->getLocale() == 'ar')
                                        المحتوى
                                    @else
                                        Content
                                    @endif
                                </label>
                                <div class="d-flex flex-column gap-1">
                                    <div class="small fw-semibold text-dark d-flex align-items-center justify-content-between">
                                        <span>{{ $juz['start_surah_name'] }}</span>
                                        <span class="badge bg-light text-secondary border small">Ayah {{ $juz['start_ayah'] }}</span>
                                    </div>
                                    <div class="text-center text-muted my-1" style="font-size: 0.8rem;">&darr;</div>
                                    <div class="small fw-semibold text-dark d-flex align-items-center justify-content-between">
                                        <span>{{ $juz['end_surah_name'] }}</span>
                                        <span class="badge bg-light text-secondary border small">Ayah {{ $juz['end_ayah'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="quran-card-footer bg-transparent border-top-0 pt-0 pb-4 px-4">
                        <a href="{{ route('read.juz', $juz['number']) }}" target="_blank" class="quran-btn quran-btn-primary w-100 py-2.5 text-center d-block text-decoration-none" style="font-size: 0.85rem; font-weight: 600; border-radius: 12px; transition: all 0.2s;">
                            <i class="bi bi-book-half me-1"></i>
                            @if(app()->getLocale() == 'ku')
                                خوێندنەوە
                            @elseif(app()->getLocale() == 'ar')
                                قراءة
                            @else
                                Read Juz
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(27,115,64,0.08) !important;
        border-color: rgba(27,115,64,0.2) !important;
    }
    .hover-card:hover svg {
        transform: rotate(45deg);
        transition: transform 0.4s ease;
    }
    .hover-card svg {
        transition: transform 0.4s ease;
    }
</style>
@endsection
