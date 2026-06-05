{{-- resources/views/hadiths/create.blade.php --}}
@extends('layouts.app')

@section('title', __('hadiths.titles.create'))
@section('page-title', __('hadiths.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hadiths.index') }}">{{ __('hadiths.titles.index') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('hadiths.actions.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('hadiths.titles.create') }}</h1>
            <div class="text-muted">{{ __('hadiths.hints.create') }}</div>
        </div>
        <a href="{{ route('hadiths.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('hadiths.actions.back') }}
        </a>
    </div>

    <!-- Form Container -->
    <div class="quran-form-container">
        <form method="POST" action="{{ route('hadiths.store') }}">
            @csrf
            
            @include('hadiths._form')

            <!-- Form Actions -->
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    {{ __('hadiths.actions.save') }}
                </button>
                <a href="{{ route('hadiths.index') }}" class="quran-btn quran-btn-outline-secondary">
                    {{ __('hadiths.actions.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
