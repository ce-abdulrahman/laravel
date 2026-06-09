{{-- resources/views/languages/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Language')
@section('page-title', 'Edit Language')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('languages.index') }}">Languages</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Edit Language</li>
@endsection

@section('content')
<div class="quran-dashboard">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 mb-1">Edit Language — {{ $language->name }}</h1>
            <div class="text-muted">Update configurations and settings for this language.</div>
        </div>
        <a href="{{ route('languages.index') }}" class="quran-btn quran-btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>
    </div>

    {{-- Form Container --}}
    <div class="quran-form-container">
        @if (session('error'))
            <div class="alert alert-danger mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('languages.update', $language) }}">
            @csrf
            @method('PUT')

            @include('languages._form')

            {{-- Actions --}}
            <div class="quran-form-actions mt-4">
                <button type="submit" class="quran-btn quran-btn-primary">
                    <i class="bi bi-save me-1"></i>
                    Update
                </button>
                <a href="{{ route('languages.index') }}" class="quran-btn quran-btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
