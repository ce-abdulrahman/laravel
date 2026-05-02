{{-- resources/views/translations/edit.blade.php --}}
@extends('layouts.app')

@section('title', __('translations.titles.edit'))
@section('page-title', __('translations.titles.edit'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('translations.index') }}">{{ __('translations.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('translations.show', $translation) }}">
            {{ $translation->ayah->surah->name_ar }} {{ $translation->ayah->ayah_number }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('translations.titles.edit') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('translations.titles.edit') }}</h1>
            <div class="text-muted">
                {{ $translation->ayah->surah->name_ar }} - {{ __('translations.ayah') }} {{ $translation->ayah->ayah_number }}
            </div>
        </div>
        <a href="{{ route('translations.show', $translation) }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('translations.actions.back') }}
        </a>
    </div>

    <!-- Form Card -->
    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-pencil-square me-2"></i>
                {{ __('translations.titles.form_edit') }}
            </h5>
            <div class="quran-card-actions">
                <span class="quran-table-badge info">
                    <i class="bi bi-calendar me-1"></i>
                    {{ __('translations.fields.updated_at') }}: {{ $translation->updated_at->format('Y-m-d H:i') }}
                </span>
            </div>
        </div>

        <div class="quran-card-body">
            <form method="POST" action="{{ route('translations.update', $translation) }}" id="translationForm">
                @csrf
                @method('PUT')

                @include('translations._form', [
                    'translation' => $translation,
                    'ayahs' => $ayahs,
                    'languages' => $languages,
                    'selectedAyah' => $translation->ayah
                ])

                <div class="quran-form-actions mt-4">
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('common.update') }}
                    </button>
                    <a href="{{ route('translations.show', $translation) }}" class="quran-btn quran-btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('common.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="quran-card border-danger mt-4">
        <div class="quran-card-header bg-danger bg-opacity-10">
            <h5 class="quran-card-title text-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ __('translations.titles.danger_zone') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1">{{ __('translations.messages.delete_title') }}</h6>
                    <p class="text-muted mb-0">{{ __('translations.messages.delete_warning') }}</p>
                </div>
                <form method="POST"
                      action="{{ route('translations.destroy', $translation) }}"
                      onsubmit="return confirmDelete(event)">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="quran-btn quran-btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        {{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(event) {
    event.preventDefault();
    const form = event.target;

    if (confirm('{{ __("translations.messages.confirm_delete") }}')) {
        form.submit();
    }
    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('translationForm');
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