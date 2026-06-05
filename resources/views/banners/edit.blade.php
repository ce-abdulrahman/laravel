{{-- resources/views/banners/edit.blade.php --}}
@extends('layouts.app')

@section('title', __('banners.titles.edit'))
@section('page-title', __('banners.titles.edit'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('banners.index') }}">{{ __('banners.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('banners.show', $banner) }}">{{ __('banners.titles.show') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('banners.titles.edit') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('banners.titles.edit') }}</h1>
            <div class="text-muted">{{ __('banners.hints.edit') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('banners.show', $banner) }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-eye me-1"></i>
                {{ __('banners.actions.view') }}
            </a>
            <a href="{{ route('banners.index') }}" class="quran-btn quran-btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('banners.actions.back') }}
            </a>
        </div>
    </div>

    {{-- Form Container --}}
    <div class="quran-form-container">
        <form method="POST" action="{{ route('banners.update', $banner) }}">
            @csrf
            @method('PUT')

            @include('banners._form')

            {{-- Actions --}}
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    {{ __('banners.actions.update') }}
                </button>
                <a href="{{ route('banners.index') }}" class="quran-btn quran-btn-outline-secondary">
                    {{ __('banners.actions.cancel') }}
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
            <p class="text-muted mb-3">{{ __('banners.messages.confirm_delete') }}</p>
            <form method="POST" action="{{ route('banners.destroy', $banner) }}"
                  onsubmit="return confirm('{{ __('banners.messages.confirm_delete') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="quran-btn quran-btn-danger">
                    <i class="bi bi-trash me-1"></i>
                    {{ __('banners.actions.delete') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
