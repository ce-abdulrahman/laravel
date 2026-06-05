@extends('layouts.app')

@section('title', __('ayahs.create_ayah'))

@section('content')
<div class="quran-dashboard">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1 text-zinc-900 dark:text-white font-bold">{{ __('ayahs.create_ayah') }}</h1>
            <div class="text-muted text-sm">{{ __('ayahs.hints.create_new') ?? 'Add a new verse to the Quran database' }}</div>
        </div>
        <a href="{{ route('ayahs.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left"></i>
            <span>{{ __('common.back') }}</span>
        </a>
    </div>

    <!-- Create Form -->
    <form method="POST" action="{{ route('ayahs.store') }}">
        @csrf

        @include('ayahs._form')

        <!-- Form Actions -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('ayahs.index') }}" class="quran-btn quran-btn-outline-primary">
                <i class="bi bi-x-lg me-1"></i>
                <span>{{ __('common.cancel') }}</span>
            </a>
            <button type="submit" class="quran-btn quran-btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                <span>{{ __('common.save') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection
