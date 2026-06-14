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
            <a href="{{ route('tajweed-segments.index') }}" 
               class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('tajweed_segments.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Ayah Card with Visual Highlighting -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-journal-text me-2"></i>
                        {{ __('tajweed_segments.full_ayah') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-verse-arabic-text p-4 bg-light rounded-3 text-center" 
                         style="font-family: var(--font-arabic); font-size: 26px; line-height: 2.2; direction: rtl;">
                        @php
                            $fullText = $tajweedSegment->ayah->text_uthmani;
                            $start = $tajweedSegment->start_index;
                            $end = $tajweedSegment->end_index;
                            $highlighted = '';
                            if ($start !== null && $end !== null && $start >= 0 && $end > $start && $start < mb_strlen($fullText) && $end <= mb_strlen($fullText)) {
                                $before = mb_substr($fullText, 0, $start);
                                $match = mb_substr($fullText, $start, $end - $start);
                                $after = mb_substr($fullText, $end);
                                
                                $highlighted = e($before) 
                                    . '<mark class="px-1 rounded fw-bold text-dark position-relative" style="background-color: ' 
                                    . ($tajweedSegment->tajweedRule->color_code ?? '#FF0000') 
                                    . '40; border-bottom: 3px solid ' 
                                    . ($tajweedSegment->tajweedRule->color_code ?? '#FF0000') 
                                    . '; cursor: help;" title="Rule: ' . e($tajweedSegment->tajweedRule->name) . '">'
                                    . e($match) 
                                    . '</mark>' 
                                    . e($after);
                            } else {
                                $highlighted = e($fullText);
                            }
                        @endphp
                        <span id="fullAyahText">{!! $highlighted !!}</span>
                        <span class="ayah-end-mark">{{ \App\Helpers\QuranHelper::getAyahEndMark($tajweedSegment->ayah->ayah_number) }}</span>
                    </div>
                    @if($start !== null && $end !== null)
                    <div class="mt-2 text-muted small text-end">
                        <i class="bi bi-info-circle me-1"></i>Highlit segment starts at character index <strong>{{ $start }}</strong> and ends at <strong>{{ $end }}</strong>.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Segment Card -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-puzzle me-2"></i>
                        {{ __('tajweed_segments.segment_details') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="text-center mb-4">
                        <label class="quran-detail-label d-block mb-2">{{ __('tajweed_segments.fields.matched_text') }}</label>
                        <div class="arabic-text p-4 rounded-3 d-inline-block" 
                             style="font-size: 30px; background-color: {{ $tajweedSegment->tajweedRule->color_code }}20;
                                    border: 2px solid {{ $tajweedSegment->tajweedRule->color_code }}; min-width: 200px;">
                            {{ $tajweedSegment->matched_text }}
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="quran-detail-label">{{ __('tajweed_segments.fields.start_index') }}</label>
                            <div class="quran-detail-value font-monospace bg-light p-2 rounded text-center border">
                                {{ $tajweedSegment->start_index !== null ? $tajweedSegment->start_index : '—' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="quran-detail-label">{{ __('tajweed_segments.fields.end_index') }}</label>
                            <div class="quran-detail-value font-monospace bg-light p-2 rounded text-center border">
                                {{ $tajweedSegment->end_index !== null ? $tajweedSegment->end_index : '—' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="quran-detail-label">Segment Length</label>
                            <div class="quran-detail-value font-monospace bg-light p-2 rounded text-center border">
                                @if($tajweedSegment->start_index !== null && $tajweedSegment->end_index !== null)
                                    {{ $tajweedSegment->end_index - $tajweedSegment->start_index }} chars
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($tajweedSegment->note)
                    <div class="mt-4 p-3 bg-light rounded-3 border-start border-primary border-3">
                        <label class="quran-detail-label fw-bold">{{ __('tajweed_segments.fields.note') }}</label>
                        <p class="mb-0 text-dark">{{ $tajweedSegment->note }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Detection Engine Metadata Card -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-cpu me-2"></i>
                        Detection Engine Metadata
                    </h5>
                </div>
                <div class="quran-card-body">
                    @if($tajweedSegment->metadata && count($tajweedSegment->metadata) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%;">Parameter Key</th>
                                    <th>Value / Config</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tajweedSegment->metadata as $key => $value)
                                <tr>
                                    <td class="font-monospace fw-bold text-primary">{{ $key }}</td>
                                    <td class="font-monospace text-dark">
                                        @if(is_bool($value))
                                            <span class="badge {{ $value ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $value ? 'true' : 'false' }}
                                            </span>
                                        @elseif(is_array($value) || is_object($value))
                                            <pre class="mb-0 bg-light p-2 rounded"><code>{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4 bg-light rounded-3">
                        <i class="bi bi-slash-circle text-muted fs-3 mb-2 d-block"></i>
                        <span class="text-muted small">No engine metadata attributes saved for this segment.</span>
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
                        <div class="quran-detail-value fw-bold text-primary">{{ $tajweedSegment->tajweedRule->name }}</div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tajweed_rules.fields.category') }}</label>
                        <div class="quran-detail-value">
                            @if($tajweedSegment->tajweedRule->category)
                            <a href="{{ route('tajweed-rule-categories.show', $tajweedSegment->tajweedRule->category) }}" class="text-decoration-none">
                                <span class="badge bg-info bg-opacity-10 text-info font-monospace py-1.5 px-2.5">
                                    {{ $tajweedSegment->tajweedRule->category->name }}
                                </span>
                            </a>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>

                    <div class="quran-detail-item">
                        <label class="quran-detail-label">{{ __('tajweed_rules.fields.description') }}</label>
                        <div class="quran-detail-value small text-muted">{{ Str::limit($tajweedSegment->tajweedRule->description, 200) }}</div>
                    </div>

                    <a href="{{ route('tajweed-rules.show', $tajweedSegment->tajweedRule) }}" 
                       class="btn btn-link btn-sm p-0 mt-2">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        {{ __('tajweed_segments.view_full_rule') }}
                    </a>
                </div>
            </div>

            <!-- Other Segments in same Ayah -->
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
                           class="list-group-item list-group-item-action bg-transparent py-3">
                            <div class="d-flex align-items-center gap-2">
                                @if($other->tajweedRule->color_code)
                                <span style="width: 12px; height: 12px; border-radius: 3px; 
                                             background-color: {{ $other->tajweedRule->color_code }}; flex-shrink: 0;"></span>
                                @endif
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="small text-dark">{{ $other->tajweedRule->name }}</strong>
                                        @if($other->start_index !== null)
                                        <span class="badge bg-light text-dark font-monospace border small">
                                            {{ $other->start_index }}-{{ $other->end_index }}
                                        </span>
                                        @endif
                                    </div>
                                    <small class="d-block text-muted arabic-text mt-1 text-end" style="font-size: 15px;">
                                        {{ $other->matched_text }}
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