{{-- resources/views/banners/create.blade.php --}}
@extends('layouts.app')

@section('title', __('banners.titles.create'))
@section('page-title', __('banners.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('banners.index') }}">{{ __('banners.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('banners.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('banners.titles.create') }}</h1>
            <div class="text-muted">{{ __('banners.hints.create') }}</div>
        </div>
        <a href="{{ route('banners.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('banners.actions.back') }}
        </a>
    </div>

    {{-- Form Container --}}
    <div class="quran-form-container">
        <form method="POST" action="{{ route('banners.store') }}">
            @csrf

            @include('banners._form')

            {{-- Actions --}}
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    {{ __('banners.actions.save') }}
                </button>
                <a href="{{ route('banners.index') }}" class="quran-btn quran-btn-outline-secondary">
                    {{ __('banners.actions.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
