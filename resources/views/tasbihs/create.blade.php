{{-- resources/views/tasbihs/create.blade.php --}}
@extends('layouts.app')

@section('title', __('tasbihs.titles.create'))
@section('page-title', __('tasbihs.titles.create'))

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('tasbihs.index') }}">{{ __('tasbihs.titles.index') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('tasbihs.titles.create') }}</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('tasbihs.titles.create') }}</h1>
            <div class="text-muted">{{ __('tasbihs.hints.create') }}</div>
        </div>
        <a href="{{ route('tasbihs.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('tasbihs.actions.back') }}
        </a>
    </div>

    {{-- Form Container --}}
    <div class="quran-form-container">
        <form method="POST" action="{{ route('tasbihs.store') }}">
            @csrf

            @include('tasbihs._form')

            {{-- Actions --}}
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    {{ __('tasbihs.actions.save') }}
                </button>
                <a href="{{ route('tasbihs.index') }}" class="quran-btn quran-btn-outline-secondary">
                    {{ __('tasbihs.actions.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
