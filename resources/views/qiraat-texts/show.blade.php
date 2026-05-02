{{-- resources/views/qiraat-texts/show.blade.php --}}
@extends('layouts.app')

@section('title', $qiraatText->qiraat->name . ' - ' . $qiraatText->ayah->surah->name_ar)
@section('page-title', $qiraatText->qiraat->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('qiraat-texts.index') }}">{{ __('qiraat_texts.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('qiraats.show', $qiraatText->qiraat) }}">{{ $qiraatText->qiraat->name }}</a>
    </li>
    <li class="breadcrumb-item active">
        {{ $qiraatText->ayah->surah->name_ar }} {{ $qiraatText->ayah->ayah_number }}
    </li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="quran-surah-number">{{ $qiraatText->ayah->surah->number }}</span>
                <h1 class="h4 mb-0">{{ $qiraatText->ayah->surah->name_ar }}</h1>
            </div>
            <div class="text-muted">
                {{ __('qiraat_texts.ayah') }} {{ $qiraatText->ayah->ayah_number }} - 
                <a href="{{ route('qiraats.show', $qiraatText->qiraat) }}" class="text-decoration-none">
                    {{ $qiraatText->qiraat->name }}
                </a>
            </div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('qiraat-texts.edit', $qiraatText) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            @endif
            <a href="{{ route('qiraats.show', $qiraatText->qiraat) }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('qiraat_texts.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Original Ayah -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-journal-text me-2"></i>
                        {{ __('qiraat_texts.original_ayah') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-verse-arabic-text p-4 bg-light rounded-3" 
                         style="font-family: var(--font-arabic); font-size: 24px; line-height: 2;">
                        {{ $originalAyah->text_uthmani }}
                        <span class="ayah-end-mark">{{ \App\Helpers\QuranHelper::getAyahEndMark($originalAyah->ayah_number) }}</span>
                    </div>
                </div>
            </div>

            <!-- Qiraat Variant -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-book-half me-2"></i>
                        {{ __('qiraat_texts.qiraat_variant') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="text-center mb-4">
                        <div class="arabic-text p-4 rounded-3" 
                             style="font-size: 28px; background: linear-gradient(135deg, var(--quran-primary)10, transparent);
                                    border: 2px solid var(--quran-primary);">
                            {{ $qiraatText->text_variant }}
                        </div>
                    </div>

                    @if($qiraatText->note)
                    <div class="mt-4 p-3 bg-light rounded-3">
                        <label class="quran-detail-label">{{ __('qiraat_texts.fields.note') }}</label>
                        <p class="mb-0">{{ $qiraatText->note }}</p>
                    </div>
                    @endif

                    <!-- Highlight differences -->
                    <div class="mt-4">
                        <button type="button" class="quran-btn quran-btn-outline-primary" 
                                onclick="highlightDifferences()">
                            <i class="bi bi-code-square me-1"></i>
                            {{ __('qiraat_texts.highlight_differences') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Qiraat Info -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('qiraat_texts.qiraat_info') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="quran-plan-icon" style="width: 48px; height: 48px;">
                            <i class="bi bi-book-half"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ $qiraatText->qiraat->name }}</h6>
                            @if($qiraatText->qiraat->riwayah)
                            <span class="quran-table-badge info">{{ $qiraatText->qiraat->riwayah }}</span>
                            @endif
                        </div>
                    </div>

                    @if($qiraatText->qiraat->description)
                    <p class="small text-muted">{{ Str::limit($qiraatText->qiraat->description, 150) }}</p>
                    @endif

                    <a href="{{ route('qiraats.show', $qiraatText->qiraat) }}" class="btn btn-link btn-sm p-0">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        {{ __('qiraat_texts.view_all_variants') }}
                    </a>
                </div>
            </div>

            <!-- Other Variants -->
            @if($otherVariants->count() > 0)
            <div class="quran-card">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-collection me-2"></i>
                        {{ __('qiraat_texts.other_variants') }}
                    </h6>
                </div>
                <div class="quran-card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($otherVariants as $variant)
                        <a href="{{ route('qiraat-texts.show', $variant) }}" 
                           class="list-group-item list-group-item-action bg-transparent">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <strong>{{ $variant->qiraat->name }}</strong>
                                    <small class="d-block text-muted arabic-text">
                                        {{ Str::limit($variant->text_variant, 40) }}
                                    </small>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                
                <div class="quran-card-footer">
                    <a href="{{ route('qiraat-texts.compare', $originalAyah->id) }}" 
                       class="text-decoration-none small">
                        <i class="bi bi-arrow-left-right me-1"></i>
                        {{ __('qiraat_texts.compare_all') }}
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function highlightDifferences() {
    // Simple difference highlighting
    const originalText = `{{ $originalAyah->text_uthmani }}`;
    const variantText = `{{ $qiraatText->text_variant }}`;
    
    // You can implement a more sophisticated diff algorithm here
    alert('{{ __("qiraat_texts.diff_feature_coming") }}');
}
</script>
@endpush