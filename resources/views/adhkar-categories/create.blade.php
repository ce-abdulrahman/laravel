{{-- resources/views/adhkar-categories/create.blade.php --}}
@extends('layouts.app')

@section('title', __('adhkar_categories.titles.create'))
@section('page-title', __('adhkar_categories.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('adhkar-categories.index') }}">{{ __('adhkar_categories.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('adhkar_categories.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('adhkar_categories.titles.create') }}</h1>
            <div class="text-muted">{{ __('adhkar_categories.hints.create') }}</div>
        </div>
        <a href="{{ route('adhkar-categories.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('adhkar_categories.actions.back') }}
        </a>
    </div>

    {{-- Form Container --}}
    <div class="quran-form-container">
        <form method="POST" action="{{ route('adhkar-categories.store') }}">
            @csrf

            @include('adhkar-categories._form')

            {{-- Actions --}}
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    {{ __('adhkar_categories.actions.save') }}
                </button>
                <a href="{{ route('adhkar-categories.index') }}" class="quran-btn quran-btn-outline-secondary">
                    {{ __('adhkar_categories.actions.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
