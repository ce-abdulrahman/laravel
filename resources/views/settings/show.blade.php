{{-- resources/views/translations/show.blade.php --}}
@extends('layouts.app')

@section('title', __('translations.titles.show'))
@section('page-title', $translation->ayah->surah->name_ar . ' - ' . __('translations.ayah') . ' ' . $translation->ayah->ayah_number)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('translations.index') }}">{{ __('translations.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        {{ $translation->ayah->surah->name_ar }} {{ $translation->ayah->ayah_number }}
    </li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="quran-surah-number">{{ $translation->ayah->surah->number }}</span>
                <h1 class="h4 mb-0">{{ $translation->ayah->surah->name_ar }}</h1>
            </div>
            <div class="text-muted">
                {{ __('translations.ayah') }} {{ $translation->ayah->ayah_number }} - 
                {{ $languages[$translation->language_code] ?? $translation->language_code }}
            </div>
        </div>

        <div class="d-flex gap-2">
            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('translations.edit', $translation) }}" class="quran-btn quran-btn-primary">
                <i class="bi bi-pencil me-1"></i>
                {{ __('common.edit') }}
            </a>
            @endif
            <a href="{{ route('translations.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('translations.actions.back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Translation Display -->
        <div class="col-lg-8">
            <!-- Ayah Card -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-journal-text me-2"></i>
                        {{ __('translations.original_ayah') }}
                    </h5>
                </div>
                <div class="quran-card-body">
                    <div class="quran-verse-arabic-text p-4 bg-light rounded-3" 
                         style="font-family: var(--font-arabic); font-size: 24px; line-height: 2;">
                        {{ $translation->ayah->text_uthmani }}
                        <span class="ayah-end-mark">{{ \App\Helpers\QuranHelper::getAyahEndMark($translation->ayah->ayah_number) }}</span>
                    </div>
                    <div class="text-muted mt-3">
                        <a href="{{ route('ayahs.show', $translation->ayah) }}" class="text-decoration-none">
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            {{ __('translations.view_full_ayah') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Translation Card -->
            <div class="quran-card">
                <div class="quran-card-header">
                    <h5 class="quran-card-title">
                        <i class="bi bi-translate me-2"></i>
                        {{ __('translations.translation') }}
                    </h5>
                    <div class="d-flex gap-2">
                        @if($translation->is_default)
                        <span class="quran-table-badge success">
                            <i class="bi bi-star-fill"></i> {{ __('translations.default') }}
                        </span>
                        @endif
                        <span class="quran-table-badge {{ $translation->is_active ? 'success' : 'danger' }}">
                            <i class="bi bi-{{ $translation->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                            {{ $translation->is_active ? __('common.active') : __('common.inactive') }}
                        </span>
                    </div>
                </div>
                <div class="quran-card-body">
                    <div class="quran-translation-text p-4" style="font-size: 18px; line-height: 1.8;">
                        {{ $translation->content }}
                    </div>
                </div>
                <div class="quran-card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ __('translations.fields.translator') }}:</strong> 
                            {{ $translation->translator_name ?: __('translations.unknown') }}
                        </div>
                        <div class="d-flex gap-2">
                            <button class="quran-btn-icon" onclick="copyTranslation()" 
                                    data-bs-toggle="tooltip" title="{{ __('translations.copy_translation') }}">
                                <i class="bi bi-clipboard"></i>
                            </button>
                            @if(auth()->user()?->role === 'admin' && !$translation->is_default)
                            <form method="POST" action="{{ route('translations.set-default', $translation) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="quran-btn-icon" 
                                        data-bs-toggle="tooltip" title="{{ __('translations.set_as_default') }}">
                                    <i class="bi bi-star"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="col-lg-4">
            <!-- Translation Details -->
            <div class="quran-card mb-4">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('translations.details') }}
                    </h6>
                </div>
                <div class="quran-card-body">
                    <div class="quran-detail-item">
                        <div class="quran-detail-label">
                            <i class="bi bi-book me-2"></i>
                            {{ __('translations.fields.surah') }}
                        </div>
                        <div class="quran-detail-value">
                            {{ $translation->ayah->surah->name_ar }} ({{ $translation->ayah->surah->name_ku }})
                        </div>
                    </div>

                    <div class="quran-detail-item">
                        <div class="quran-detail-label">
                            <i class="bi bi-hash me-2"></i>
                            {{ __('translations.fields.ayah_number') }}
                        </div>
                        <div class="quran-detail-value">{{ $translation->ayah->ayah_number }}</div>
                    </div>

                    <div class="quran-detail-item">
                        <div class="quran-detail-label">
                            <i class="bi bi-globe me-2"></i>
                            {{ __('translations.fields.language') }}
                        </div>
                        <div class="quran-detail-value">
                            {{ $languages[$translation->language_code] ?? $translation->language_code }}
                        </div>
                    </div>

                    <div class="quran-detail-item">
                        <div class="quran-detail-label">
                            <i class="bi bi-calendar-plus me-2"></i>
                            {{ __('translations.fields.created_at') }}
                        </div>
                        <div class="quran-detail-value">
                            {{ $translation->created_at->format('Y-m-d H:i') }}
                        </div>
                    </div>

                    <div class="quran-detail-item">
                        <div class="quran-detail-label">
                            <i class="bi bi-calendar-check me-2"></i>
                            {{ __('translations.fields.updated_at') }}
                        </div>
                        <div class="quran-detail-value">
                            {{ $translation->updated_at->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other Translations -->
            @if($otherTranslations->count() > 0)
            <div class="quran-card">
                <div class="quran-card-header">
                    <h6 class="quran-card-title">
                        <i class="bi bi-translate me-2"></i>
                        {{ __('translations.other_translations') }}
                    </h6>
                </div>
                <div class="quran-card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($otherTranslations as $langCode => $translations)
                        <div class="list-group-item bg-transparent">
                            <h6 class="mb-2">{{ $languages[$langCode] ?? $langCode }}</h6>
                            @foreach($translations as $other)
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <a href="{{ route('translations.show', $other) }}" class="text-decoration-none">
                                        {{ $other->translator_name ?: __('translations.unknown') }}
                                    </a>
                                    @if($other->is_default)
                                    <i class="bi bi-star-fill text-warning ms-1" style="font-size: 10px;"></i>
                                    @endif
                                </div>
                                <span class="text-muted small">
                                    {{ Str::limit($other->content, 30) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyTranslation() {
    const text = `{{ str_replace("'", "\'", $translation->content) }}`;
    navigator.clipboard.writeText(text).then(() => {
        alert('{{ __("translations.messages.copied") }}');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush