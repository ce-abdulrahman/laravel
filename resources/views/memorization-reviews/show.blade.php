{{-- resources/views/memorization-reviews/show.blade.php --}}
@extends('layouts.app')

@section('title', __('memorization_reviews.titles.show'))
@section('page-title', $memorizationReview->ayah->surah->name_ar)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('memorization-reviews.index') }}">{{ __('memorization_reviews.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">
        {{ $memorizationReview->ayah->surah->name_ar }} {{ $memorizationReview->ayah->ayah_number }}
    </li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="quran-surah-number">{{ $memorizationReview->ayah->surah->number }}</span>
                <h1 class="h4 mb-0">{{ $memorizationReview->ayah->surah->name_ar }}</h1>
            </div>
            <div class="text-muted">
                {{ __('memorization_reviews.ayah') }} {{ $memorizationReview->ayah->ayah_number }}
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('memorization-reviews.edit', $memorizationReview) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            <a href="{{ route('memorization-reviews.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('memorization_reviews.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Ayah Card -->
        <div class="col-lg-8">
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-journal-text me-2"></i>
                        {{ __('memorization_reviews.ayah_text') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-verse-arabic-text p-4 bg-light rounded-3" 
                         style="font-family: var(--font-arabic); font-size: 24px; line-height: 2;">
                        {{ $memorizationReview->ayah->text_uthmani }}
                        <span class="ayah-end-mark">{{ \App\Helpers\QuranHelper::getAyahEndMark($memorizationReview->ayah->ayah_number) }}</span>
                    </div>
                </div>
            </div>

            <!-- Review Details -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('memorization_reviews.review_details') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="quran-detail-item">
                                <label class="quran-detail-label">{{ __('memorization_reviews.fields.review_date') }}</label>
                                <div class="quran-detail-value">{{ $memorizationReview->review_date->format('Y-m-d') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="quran-detail-item">
                                <label class="quran-detail-label">{{ __('memorization_reviews.fields.level') }}</label>
                                <div class="quran-detail-value">
                                    @if($memorizationReview->review_level)
                                    <span class="quran-table-badge info">
                                        {{ $reviewLevels[$memorizationReview->review_level] ?? $memorizationReview->review_level }}
                                    </span>
                                    @else
                                    —
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="quran-detail-item">
                                <label class="quran-detail-label">{{ __('memorization_reviews.fields.result') }}</label>
                                <div class="quran-detail-value">
                                    @if($memorizationReview->result)
                                    <span class="quran-table-badge {{ $memorizationReview->result }}">
                                        {{ $results[$memorizationReview->result] ?? $memorizationReview->result }}
                                    </span>
                                    @else
                                    —
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($memorizationReview->notes)
                    <div class="mt-4 p-3 bg-light rounded-3">
                        <label class="quran-detail-label">{{ __('memorization_reviews.fields.notes') }}</label>
                        <p class="mb-0">{{ $memorizationReview->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Previous Reviews -->
            @if($previousReviews->count() > 0)
            <div class="quran-card">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-clock-history me-2"></i>
                        {{ __('memorization_reviews.previous_reviews') }}
                    </h6>
                </div>
                <div class="quran-card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($previousReviews as $prev)
                        <a href="{{ route('memorization-reviews.show', $prev) }}" 
                           class="list-group-item list-group-item-action bg-transparent">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="quran-table-badge {{ $prev->result }} me-2">
                                        {{ $results[$prev->result] ?? $prev->result }}
                                    </span>
                                    <small class="text-muted">{{ $prev->review_date->format('Y-m-d') }}</small>
                                </div>
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection