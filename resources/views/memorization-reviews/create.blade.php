{{-- resources/views/memorization-reviews/create.blade.php --}}
@extends('layouts.app')

@section('title', __('memorization_reviews.titles.create'))
@section('page-title', __('memorization_reviews.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('memorization-reviews.index') }}">{{ __('memorization_reviews.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ __('memorization_reviews.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('memorization_reviews.titles.create') }}</h1>
            <div class="text-muted">{{ __('memorization_reviews.hints.create_new') }}</div>
        </div>
        <a href="{{ route('memorization-reviews.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('memorization_reviews.actions.back') }}
        </a>
    </div>

    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-plus-circle me-2"></i>
                {{ __('memorization_reviews.titles.form_create') }}
            </h5>
        </div>

        <div class="quran-card-body">
            <form method="POST" action="{{ route('memorization-reviews.store') }}">
                @csrf
                @include('memorization-reviews._form', ['review' => new \App\Models\MemorizationReview()])

                <div class="quran-form-actions mt-4">
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('common.save') }}
                    </button>
                    <a href="{{ route('memorization-reviews.index') }}" class="quran-btn quran-btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('common.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.select2').select2({
        placeholder: '{{ __("memorization_reviews.search_ayah") }}',
        allowClear: true
    });

    // Filter ayahs by surah
    const filterSurah = document.getElementById('filter_surah');
    const ayahSelect = document.getElementById('ayah_id');

    if (filterSurah) {
        filterSurah.addEventListener('change', function() {
            const surahId = this.value;
            
            // Clear and disable ayah select
            ayahSelect.innerHTML = '<option value="">{{ __("memorization_reviews.select_ayah") }}</option>';
            
            if (surahId) {
                ayahSelect.disabled = true;
                
                fetch(`/api/surah/${surahId}/ayahs-for-plan`)
                    .then(res => res.json())
                    .then(data => {
                        data.forEach(ayah => {
                            const option = document.createElement('option');
                            option.value = ayah.id;
                            option.textContent = `${ayah.ayah_number} - ${ayah.text_uthmani.substring(0, 30)}...`;
                            ayahSelect.appendChild(option);
                        });
                        ayahSelect.disabled = false;
                        $(ayahSelect).trigger('change.select2');
                    });
            }
        });
    }
});
</script>
@endpush