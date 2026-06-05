{{-- resources/views/hadith-categories/create.blade.php --}}
@extends('layouts.app')

@section('title', __('hadith_categories.titles.create'))
@section('page-title', __('hadith_categories.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('hadith-categories.index') }}">{{ __('hadith_categories.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('hadith_categories.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('hadith_categories.titles.create') }}</h1>
            <div class="text-muted">{{ __('hadith_categories.hints.create') }}</div>
        </div>
        <a href="{{ route('hadith-categories.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('hadith_categories.actions.back') }}
        </a>
    </div>

    <div class="quran-form-container">
        <form method="POST" action="{{ route('hadith-categories.store') }}">
            @csrf

            @include('hadith-categories._form')

            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    {{ __('hadith_categories.actions.save') }}
                </button>
                <a href="{{ route('hadith-categories.index') }}" class="quran-btn quran-btn-outline-secondary">
                    {{ __('hadith_categories.actions.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
