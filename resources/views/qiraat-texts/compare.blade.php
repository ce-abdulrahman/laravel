{{-- resources/views/qiraat-texts/compare.blade.php --}}
@extends('layouts.app')

@section('title', __('qiraat_texts.titles.compare'))
@section('page-title', __('qiraat_texts.titles.compare'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('qiraat-texts.index') }}">{{ __('qiraat_texts.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('qiraat_texts.titles.compare') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('qiraat_texts.titles.compare') }}</h1>
            <div class="text-muted">
                {{ $ayah->surah->name_ar }} - {{ __('qiraat_texts.ayah') }} {{ $ayah->ayah_number }}
            </div>
        </div>
        <a href="{{ route('ayahs.show', $ayah) }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('qiraat_texts.actions.back_to_ayah') }}
        </a>
    </div>

    <!-- Original Ayah -->
    <div class="quran-card mb-4">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-journal-text me-2"></i>
                {{ __('qiraat_texts.original_ayah') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="quran-verse-arabic-text p-4 bg-light rounded-3 text-center" 
                 style="font-family: var(--font-arabic); font-size: 28px; line-height: 2.2;">
                {{ $ayah->text_uthmani }}
                <span class="ayah-end-mark">{{ \App\Helpers\QuranHelper::getAyahEndMark($ayah->ayah_number) }}</span>
            </div>
        </div>
    </div>

    <!-- Qiraat Variants -->
    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-collection me-2"></i>
                {{ __('qiraat_texts.qiraat_variants') }}
            </h5>
            <span class="quran-table-badge info">{{ $variants->count() }} {{ __('qiraat_texts.variants') }}</span>
        </div>
        <div class="quran-card-body">
            @if($variants->count() > 0)
            <div class="compare-table">
                @foreach($variants as $variant)
                <div class="compare-row mb-4 pb-4 border-bottom">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="quran-plan-icon" style="width: 40px; height: 40px;">
                            <i class="bi bi-book-half"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ $variant->qiraat->name }}</h6>
                            @if($variant->qiraat->riwayah)
                            <span class="quran-table-badge info">{{ $variant->qiraat->riwayah }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="arabic-text p-3 rounded-3" 
                         style="font-size: 22px; background-color: var(--quran-bg-pattern);">
                        {{ $variant->text_variant }}
                    </div>
                    
                    @if($variant->note)
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            {{ $variant->note }}
                        </small>
                    </div>
                    @endif
                    
                    <div class="mt-3">
                        <a href="{{ route('qiraat-texts.show', $variant) }}" class="btn btn-link btn-sm p-0">
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            {{ __('common.view_details') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="quran-table-empty">
                <i class="bi bi-journal-x"></i>
                <h6>{{ __('qiraat_texts.no_variants_found') }}</h6>
                @if(auth()->user()?->role === 'admin')
                <a href="{{ route('qiraat-texts.create', ['ayah_id' => $ayah->id]) }}" 
                   class="quran-btn quran-btn-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('qiraat_texts.actions.add_variant') }}
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.compare-row:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}
</style>
@endpush