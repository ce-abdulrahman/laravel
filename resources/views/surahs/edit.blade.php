@extends('layouts.app')

@section('title', __('surah.titles.edit'))
@section('page-title', __('surah.titles.edit') . ': ' . $surah->name_ar)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('surahs.index') }}">{{ __('surah.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('surahs.show', $surah) }}">{{ $surah->name_ar }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('surah.titles.edit') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <span class="quran-surah-number">{{ $surah->number }}</span>
                <h1 class="h4 mb-0">{{ __('surah.titles.edit') }}: {{ $surah->name_ar }}</h1>
            </div>
            <div class="text-muted">{{ __('surah.hints.edit_existing') }}</div>
        </div>
        <a href="{{ route('surahs.show', $surah) }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('surah.actions.back') }}
        </a>
    </div>

    <!-- Form Card -->
    <div class="quran-card">
        <div class="quran-card-header">
            <h5 class="quran-card-title">
                <i class="bi bi-pencil-square me-2"></i>
                {{ __('surah.titles.form_edit') }}
            </h5>
            <div class="quran-card-actions">
                <span class="quran-table-badge info">
                    <i class="bi bi-calendar me-1"></i>
                    {{ __('surah.fields.updated_at') }}: {{ $surah->updated_at?->format('Y-m-d H:i') }}
                </span>
            </div>
        </div>

        <div class="quran-card-body">
            <form method="POST" action="{{ route('surahs.update', $surah) }}" id="surahForm">
                @csrf
                @method('PUT')

                @include('surahs._form', ['surah' => $surah])

                <div class="quran-form-actions mt-4">
                    <button type="submit" class="quran-btn quran-btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('common.save') }}
                    </button>
                    <a href="{{ route('surahs.show', $surah) }}" class="quran-btn quran-btn-outline-secondary">
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
                {{ __('surah.titles.danger_zone') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1">{{ __('surah.messages.delete_title') }}</h6>
                    <p class="text-muted mb-0">{{ __('surah.messages.delete_warning') }}</p>
                </div>
                <form method="POST"
                      action="{{ route('surahs.destroy', $surah) }}"
                      onsubmit="return confirmDelete(event, '{{ $surah->name_ar }}')">
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
    function confirmDelete(event, surahName) {
        event.preventDefault();
        const form = event.target;

        if (confirm('{{ __("surah.messages.confirm_delete") }}\n\n' + surahName + '\n\n{{ __("surah.messages.cannot_undo") }}')) {
            form.submit();
        }
        return false;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('surahForm');

        // Confirm before leaving if form is dirty
        let formDirty = false;
        const formInputs = form.querySelectorAll('input, select, textarea');

        formInputs.forEach(input => {
            input.addEventListener('change', () => {
                formDirty = true;
            });
            input.addEventListener('input', () => {
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
