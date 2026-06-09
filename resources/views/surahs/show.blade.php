@extends('layouts.app')

@section('title', __('surah.titles.show'))
@section('page-title', $surah->name ?? '')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('surahs.index') }}">{{ __('surah.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $surah->name }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="quran-surah-number">{{ $surah->number }}</span>
                <h1 class="h3 mb-0 quran-surah-arabic">{{ $surah->name }}</h1>
            </div>
            <div class="text-muted d-flex gap-4">
                <span>
                    <i class="bi bi-file-text me-1"></i>
                    {{ $surah->ayah_count }} {{ __('surah.fields.ayah_count') }}
                </span>
                <span>
                    <i class="bi bi-geo-alt me-1"></i>
                    {{ __('surah.revelation_types.' . $surah->revelation_type) }}
                </span>
                @if($surah->juz_start)
                    <span>
                        <i class="bi bi-layers me-1"></i>
                        {{ __('surah.fields.juz') }} {{ $surah->juz_start }}
                        @if($surah->juz_end && $surah->juz_end != $surah->juz_start)
                            - {{ $surah->juz_end }}
                        @endif
                    </span>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('surahs.edit', $surah) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            <a href="{{ route('surahs.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('surah.actions.back') }}
            </a>
        </div>
    </div>

    <!-- Details Card -->
    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-info-circle me-2"></i>
                {{ __('surah.titles.details') }}
            </h5>
            <span class="quran-table-badge {{ $surah->is_active ? 'success' : 'secondary' }}">
                <i class="bi bi-{{ $surah->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                {{ $surah->is_active ? __('surah.status.active') : __('surah.status.inactive') }}
            </span>
        </div>

        <div class="quran-card-body">
            <div class="row g-4">
                <!-- Revelation Type -->
                <div class="col-md-6">
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">
                            <i class="bi bi-geo-alt me-1"></i>
                            {{ __('surah.fields.revelation_type') }}
                        </label>
                        <div class="quran-detail-value">
                            @php
                                $revelationClass = $surah->revelation_type === 'meccan' ? 'primary' : 'success';
                            @endphp
                            <span class="quran-table-badge {{ $revelationClass }}">
                                {{ __('surah.revelation_types.' . $surah->revelation_type) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Page Range -->
                <div class="col-md-6">
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">
                            <i class="bi bi-file-earmark me-1"></i>
                            {{ __('surah.fields.page_range') }}
                        </label>
                        <div class="quran-detail-value">
                            @if($surah->page_start)
                                {{ $surah->page_start }}
                                @if($surah->page_end && $surah->page_end != $surah->page_start)
                                    - {{ $surah->page_end }}
                                @endif
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Juz Range -->
                <div class="col-md-6">
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">
                            <i class="bi bi-layers me-1"></i>
                            {{ __('surah.fields.juz_range') }}
                        </label>
                        <div class="quran-detail-value">
                            @if($surah->juz_start)
                                {{ $surah->juz_start }}
                                @if($surah->juz_end && $surah->juz_end != $surah->juz_start)
                                    - {{ $surah->juz_end }}
                                @endif
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Number -->
                <div class="col-md-3">
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">
                            <i class="bi bi-hash me-1"></i>
                            {{ __('surah.fields.number') }}
                        </label>
                        <div class="quran-detail-value">{{ $surah->number }}</div>
                    </div>
                </div>

                <!-- Ayah Count -->
                <div class="col-md-3">
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">
                            <i class="bi bi-list-ol me-1"></i>
                            {{ __('surah.fields.ayah_count') }}
                        </label>
                        <div class="quran-detail-value">{{ $surah->ayah_count }}</div>
                    </div>
                </div>

                <!-- Created At -->
                <div class="col-md-3">
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">
                            <i class="bi bi-calendar-plus me-1"></i>
                            {{ __('surah.fields.created_at') }}
                        </label>
                        <div class="quran-detail-value">
                            {{ $surah->created_at?->format('Y-m-d H:i') ?: '—' }}
                        </div>
                    </div>
                </div>

                <!-- Updated At -->
                <div class="col-md-3">
                    <div class="quran-detail-item">
                        <label class="quran-detail-label">
                            <i class="bi bi-calendar-check me-1"></i>
                            {{ __('surah.fields.updated_at') }}
                        </label>
                        <div class="quran-detail-value">
                            {{ $surah->updated_at?->format('Y-m-d H:i') ?: '—' }}
                        </div>
                    </div>
                </div>

                <!-- Description -->
                @if($surah->description)
                    <div class="col-12">
                        <div class="quran-detail-item">
                            <label class="quran-detail-label">
                                <i class="bi bi-card-text me-1"></i>
                                {{ __('surah.fields.description') }}
                            </label>
                            <div class="quran-detail-value quran-description">
                                {{ $surah->description }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Card Footer -->
        <div class="quran-card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="quran-table-info">
                    <i class="bi bi-eye me-1"></i>
                    {{ __('surah.hints.view_details') }}
                </div>
                <form method="POST"
                      action="{{ route('surahs.destroy', $surah) }}"
                      onsubmit="return confirmDelete(event, '{{ $surah->name_ar }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="quran-btn quran-btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>
                        {{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Translations Card -->
    <x-translations.show-tabs :model="$surah" :active-languages="$activeLanguages" />

    <!-- Quick Actions Card -->
    <div class="quran-card mt-4">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-lightning-charge me-2"></i>
                {{ __('surah.titles.quick_actions') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="quran-quick-actions">
                <a href="{{ route('ayahs.index', ['surah_id' => $surah->id]) }}" class="quran-quick-action-btn">
                    <i class="bi bi-list-ul"></i>
                    <span>{{ __('surah.actions.view_ayahs') }}</span>
                </a>
                <a href="{{ route('tafsirs.index', ['surah_id' => $surah->id]) }}" class="quran-quick-action-btn">
                    <i class="bi bi-book"></i>
                    <span>{{ __('surah.actions.view_tafsir') }}</span>
                </a>
                <a href="{{ route('audio-files.index', ['surah_id' => $surah->id]) }}" class="quran-quick-action-btn">
                    <i class="bi bi-headphones"></i>
                    <span>{{ __('surah.actions.listen') }}</span>
                </a>
                <a href="{{ route('memorization-plans.index', ['surah_id' => $surah->id]) }}" class="quran-quick-action-btn">
                    <i class="bi bi-journal-plus"></i>
                    <span>{{ __('surah.actions.start_memorization') }}</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(event, surahName) {
        event.preventDefault();
        const form = event.target;

        if (confirm('{{ __("surah.messages.confirm_delete") }}\n\n' + surahName)) {
            form.submit();
        }
        return false;
    }
</script>
@endpush
