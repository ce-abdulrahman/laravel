{{-- resources/views/hadiths/edit.blade.php --}}
@extends('layouts.app')

@section('title', __('hadiths.titles.edit'))
@section('page-title', __('hadiths.titles.edit'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hadiths.index') }}">{{ __('hadiths.titles.index') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('hadiths.titles.edit') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('hadiths.titles.edit') }}</h1>
            <div class="text-muted">{{ __('hadiths.hints.edit') }}</div>
        </div>
        <a href="{{ route('hadiths.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('hadiths.actions.back') }}
        </a>
    </div>

    <!-- Form Container -->
    <div class="quran-form-container">
        <form method="POST" action="{{ route('hadiths.update', $hadith) }}">
            @csrf
            @method('PUT')
            
            @include('hadiths._form')

            <!-- Form Actions -->
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    {{ __('hadiths.actions.update') }}
                </button>
                <a href="{{ route('hadiths.index') }}" class="quran-btn quran-btn-outline-secondary">
                    {{ __('hadiths.actions.cancel') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="card border-danger-subtle mt-4">
        <div class="card-header bg-danger-subtle text-danger-emphasis d-flex align-items-center justify-content-between py-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-danger"></i>
                <h6 class="mb-0 fw-bold">{{ __('hadiths.actions.delete') }}</h6>
            </div>
            <span class="badge bg-danger text-white px-2 py-1 small fw-medium">Danger Zone</span>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                {{ __('hadiths.messages.confirm_delete') }}
            </p>
            <form method="POST" action="{{ route('hadiths.destroy', $hadith) }}" onsubmit="return confirmDelete(event)">
                @csrf
                @method('DELETE')
                <button type="submit" class="quran-btn quran-btn-danger">
                    <i class="bi bi-trash me-1"></i>
                    {{ __('hadiths.actions.delete') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(event) {
        event.preventDefault();
        const form = event.target;

        if (confirm('{{ __('hadiths.messages.confirm_delete') }}')) {
            form.submit();
        }
        return false;
    }
</script>
@endpush
