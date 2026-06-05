{{-- resources/views/adhkar-categories/edit.blade.php --}}
@extends('layouts.app')

@section('title', __('adhkar_categories.titles.edit'))
@section('page-title', __('adhkar_categories.titles.edit'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('adhkar-categories.index') }}">{{ __('adhkar_categories.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('adhkar-categories.show', $category) }}">{{ __('adhkar_categories.titles.show') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('adhkar_categories.titles.edit') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('adhkar_categories.titles.edit') }}</h1>
            <div class="text-muted">{{ __('adhkar_categories.hints.edit') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('adhkar-categories.show', $category) }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-eye me-1"></i>
                {{ __('adhkar_categories.actions.view') }}
            </a>
            <a href="{{ route('adhkar-categories.index') }}" class="quran-btn quran-btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('adhkar_categories.actions.back') }}
            </a>
        </div>
    </div>

    {{-- Form Container --}}
    <div class="quran-form-container">
        <form method="POST" action="{{ route('adhkar-categories.update', $category) }}">
            @csrf
            @method('PUT')

            @include('adhkar-categories._form')

            {{-- Actions --}}
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    {{ __('adhkar_categories.actions.update') }}
                </button>
                <a href="{{ route('adhkar-categories.index') }}" class="quran-btn quran-btn-outline-secondary">
                    {{ __('adhkar_categories.actions.cancel') }}
                </a>
            </div>
        </form>
    </div>

    {{-- Danger Zone --}}
    <div class="quran-card mt-4" style="border: 1px solid rgba(220,53,69,0.3);">
        <div class="quran-card-header" style="background: rgba(220,53,69,0.06);">
            <h5 class="quran-card-title text-danger mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ __('common.danger_zone') }}
            </h5>
        </div>
        <div class="quran-card-body">
            <p class="text-muted mb-3">{{ __('adhkar_categories.messages.confirm_delete') }}</p>
            <form method="POST" action="{{ route('adhkar-categories.destroy', $category) }}"
                  onsubmit="return confirm('{{ __('adhkar_categories.messages.confirm_delete') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="quran-btn quran-btn-danger">
                    <i class="bi bi-trash me-1"></i>
                    {{ __('adhkar_categories.actions.delete') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
