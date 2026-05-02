@extends('layouts.app')

@section('title', __('surah.titles.create'))
@section('page-title', __('surah.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('surahs.index') }}">{{ __('surah.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('surah.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('surah.titles.create') }}</h1>
            <div class="text-muted">{{ __('surah.hints.create_new') }}</div>
        </div>
        <a href="{{ route('surahs.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('surah.actions.back') }}
        </a>
    </div>

    <!-- Form Card -->
    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-plus-circle me-2"></i>
                {{ __('surah.titles.form_create') }}
            </h5>
        </div>

        <div class="quran-card-body">
            <form method="POST" action="{{ route('surahs.store') }}" id="surahForm">
                @csrf

                @include('surahs._form', ['surah' => $surah])

                <div class="quran-form-actions mt-4">
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('common.save') }}
                    </button>
                    <a href="{{ route('surahs.index') }}" class="quran-btn quran-btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('common.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Help Card -->
    <div class="quran-card mt-4">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-question-circle me-2"></i>
                {{ __('surah.titles.help') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="quran-help-item">
                        <i class="bi bi-1-circle text-primary"></i>
                        <span>{{ __('surah.help.step1') }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="quran-help-item">
                        <i class="bi bi-2-circle text-primary"></i>
                        <span>{{ __('surah.help.step2') }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="quran-help-item">
                        <i class="bi bi-3-circle text-primary"></i>
                        <span>{{ __('surah.help.step3') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('surahForm');

        // Auto-calculate page_end based on ayah_count (optional)
        const ayahCountInput = document.getElementById('ayah_count');
        const pageStartInput = document.getElementById('page_start');
        const pageEndInput = document.getElementById('page_end');

        if (ayahCountInput && pageStartInput && pageEndInput) {
            const calculatePageEnd = function() {
                const ayahCount = parseInt(ayahCountInput.value) || 0;
                const pageStart = parseInt(pageStartInput.value) || 0;

                if (ayahCount > 0 && pageStart > 0) {
                    // Approximate calculation: ~20 ayahs per page
                    const estimatedPages = Math.ceil(ayahCount / 20);
                    const calculatedEnd = pageStart + estimatedPages - 1;
                    pageEndInput.value = calculatedEnd > 0 ? calculatedEnd : '';
                }
            };

            pageStartInput.addEventListener('change', calculatePageEnd);
            ayahCountInput.addEventListener('change', calculatePageEnd);
        }

        // Confirm before leaving if form is dirty
        let formDirty = false;
        const formInputs = form.querySelectorAll('input, select, textarea');

        formInputs.forEach(input => {
            input.addEventListener('change', () => {
                formDirty = true;
            });
        });

        form.addEventListener('submit', () => {
            formDirty = false;
        });

        window.addEventListener('beforeunload', (e) => {
            if (formDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    });
</script>
@endpush
