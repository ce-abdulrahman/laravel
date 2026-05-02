{{-- resources/views/tajweed-segments/show.blade.php --}}
@extends('layouts.app')

@section('title', $tajweedSegment->tajweedRule->name . ' - ' . $tajweedSegment->ayah->surah->name_ar)
@section('page-title', $tajweedSegment->tajweedRule->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('tajweed-segments.index') }}">{{ __('tajweed_segments.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('tajweed-rules.show', $tajweedSegment->tajweedRule) }}">
            {{ $tajweedSegment->tajweedRule->name }}
        </a>
    </li>
    <li class="breadcrumb-item active">
        {{ $tajweedSegment->ayah->surah->name_ar }} {{ $tajweedSegment->ayah->ayah_number }}
    </li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                @if($tajweedSegment->tajweedRule->color_code)
                <div style="width: 40px; height: 40px; border-radius: 10px; 
                            background-color: {{ $tajweedSegment->tajweedRule->color_code }};"></div>
                @endif
                <div>
                    <h1 class="h4 mb-1">{{ $tajweedSegment->tajweedRule->name }}</h1>
                    <p class="text-muted mb-0">
                        {{ $tajweedSegment->ayah->surah->name_ar }} - 
                        {{ __('tajweed_segments.ayah') }} {{ $tajweedSegment->ayah->ayah_number }}
                    </p>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('tajweed-segments.edit', $tajweedSegment) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            @endif
            <a href="{{ route('tajweed-rules.show', $tajweedSegment->tajweedRule) }}" 
               class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('tajweed_segments.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Ayah Card -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-journal-text me-2"></i>
                        {{ __('tajweed_segments.full_ayah') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-verse-arabic-text p-4 bg-light rounded-3" 
                         style="font-family: var(--font-arabic); font-size: 24px; line-height: 2;">
                        <span id="fullAyahText">{{ $tajweedSegment->ayah->text_uthmani }}</span>
                        <span class="ayah-end-mark">{{ \App\Helpers\QuranHelper::getAyahEndMark($tajweedSegment->ayah->ayah_number) }}</span>
                    </div>
                </div>
            </div>

            <!-- Segment Card -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-puzzle me-2"></i>
                        {{ __('tajweed_segments.segment_details') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="text-center mb-4">
                        <div class="arabic-text p-4 rounded-3" 
                             style="font-size: 28px; background-color: {{ $tajweedSegment->tajweedRule->color_code }}20;
                                    border: 2px solid {{ $tajweedSegment->tajweedRule->color_code }};">
                            {{ $tajweedSegment->text_segment }}
                        </div>
                    </div>

                    @if($tajweedSegment->start_index !== null || $tajweedSegment->end_index !== null)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="quran-detail-label">{{ __('tajweed_segments.fields.start_index') }}</label>
                            <div class="quran-detail-value">{{ $tajweedSegment->start_index ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="quran-detail-label">{{ __('tajweed_segments.fields.end_index') }}</label>
                            <div class="quran-detail-value">{{ $tajweedSegment->end_index ?? '—' }}</div>
                        </div>
                    </div>
                    @endif

                    @if($tajweedSegment->note)
                    <div class="mt-4 p-3 bg-light rounded-3">
                        <label class="quran-detail-label">{{ __('tajweed_segments.fields.note') }}</label>
                        <p class="mb-0">{{ $tajweedSegment->note }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Rule Info -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('tajweed_segments.rule_info') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tajweed_rules.fields.name') }}</label>
                        <div class="quran-detail-value">{{ $tajweedSegment->tajweedRule->name }}</div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tajweed_rules.fields.category') }}</label>
                        <div class="quran-detail-value">{{ $tajweedSegment->tajweedRule->category ?: '—' }}</div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tajweed_rules.fields.description') }}</label>
                        <div class="quran-detail-value small">{{ Str::limit($tajweedSegment->tajweedRule->description, 150) }}</div>
                    </div>

                    <a href="{{ route('tajweed-rules.show', $tajweedSegment->tajweedRule) }}" 
                       class="btn btn-link btn-sm p-0 mt-2">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        {{ __('tajweed_segments.view_full_rule') }}
                    </a>
                </div>
            </div>

            <!-- Other Segments -->
            @if($otherSegments->count() > 1)
            <div class="quran-card">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-puzzle me-2"></i>
                        {{ __('tajweed_segments.other_segments') }}
                    </h6>
                </div>
                <div class="quran-card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($otherSegments as $other)
                        @if($other->id !== $tajweedSegment->id)
                        <a href="{{ route('tajweed-segments.show', $other) }}" 
                           class="list-group-item list-group-item-action bg-transparent">
                            <div class="d-flex align-items-center gap-2">
                                @if($other->tajweedRule->color_code)
                                <span style="width: 12px; height: 12px; border-radius: 3px; 
                                             background-color: {{ $other->tajweedRule->color_code }};"></span>
                                @endif
                                <div>
                                    <strong>{{ $other->tajweedRule->name }}</strong>
                                    <small class="d-block text-muted arabic-text">
                                        {{ $other->text_segment }}
                                    </small>
                                </div>
                            </div>
                        </a>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection