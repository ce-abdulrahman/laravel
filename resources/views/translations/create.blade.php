{{-- resources/views/translations/create.blade.php --}}
@extends('layouts.app')

@section('title', __('translations.titles.create'))
@section('page-title', __('translations.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('translations.index') }}">{{ __('translations.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('translations.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('translations.titles.create') }}</h1>
            <div class="text-muted">{{ __('translations.hints.create_new') }}</div>
        </div>
        <a href="{{ route('translations.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('translations.actions.back') }}
        </a>
    </div>

    <!-- Form Card -->
    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-plus-circle me-2"></i>
                {{ __('translations.titles.form_create') }}
            </h5>
        </div>

        <div class="quran-card-body">
            <form method="POST" action="{{ route('translations.store') }}" id="translationForm">
                @csrf

                @include('translations._form', [
                    'translation' => new \App\Models\Translation(),
                    'ayahs' => $ayahs,
                    'languages' => $languages,
                    'selectedAyah' => $selectedAyah
                ])

                <div class="quran-form-actions mt-4">
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('common.save') }}
                    </button>
                    <a href="{{ route('translations.index') }}" class="quran-btn quran-btn-outline-secondary">
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
                {{ __('translations.titles.help') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="quran-help-item">
                        <i class="bi bi-1-circle text-primary"></i>
                        <span>{{ __('translations.help.step1') }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="quran-help-item">
                        <i class="bi bi-2-circle text-primary"></i>
                        <span>{{ __('translations.help.step2') }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="quran-help-item">
                        <i class="bi bi-3-circle text-primary"></i>
                        <span>{{ __('translations.help.step3') }}</span>
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
    const form = document.getElementById('translationForm');

    // Confirm before leaving if form is dirty
    let formDirty = false;
    const formInputs = form.querySelectorAll('input, select, textarea');

    formInputs.forEach(input => {
        input.addEventListener('change', () => { formDirty = true; });
        input.addEventListener('input', () => { formDirty = true; });
    });

    form.addEventListener('submit', () => { formDirty = false; });

    window.addEventListener('beforeunload', (e) => {
        if (formDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
</script>
@endpush