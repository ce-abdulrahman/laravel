{{-- resources/views/adhkars/create.blade.php --}}
@extends('layouts.app')

@section('title', __('adhkars.titles.create'))
@section('page-title', __('adhkars.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('adhkars.index') }}">{{ __('adhkars.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('adhkars.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('adhkars.titles.create') }}</h1>
            <div class="text-muted">{{ __('adhkars.hints.create') }}</div>
        </div>
        <a href="{{ route('adhkars.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('adhkars.actions.back') }}
        </a>
    </div>

    {{-- Form Container --}}
    <div class="quran-form-container">
        <form method="POST" action="{{ route('adhkars.store') }}">
            @csrf

            @include('adhkars._form')

            {{-- Actions --}}
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    {{ __('adhkars.actions.save') }}
                </button>
                <a href="{{ route('adhkars.index') }}" class="quran-btn quran-btn-outline-secondary">
                    {{ __('adhkars.actions.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
